<?php

use Spatie\LaravelPasskeys\Actions\Resolvers\ResolveContextAction;

beforeEach(function () {
    $this->action = \Spatie\LaravelPasskeys\Support\Config::getAction('resolve_context', ResolveContextAction::class);
});

it('resolves context when only one context is configured', function () {
    config()->set('passkeys.contexts', [
        'app' => [
            'authenticatable' => \Spatie\LaravelPasskeys\Tests\TestSupport\Models\User::class,
            'guard' => 'web',
        ],
    ]);

    $context = $this->action->execute(Request::create('/'));

    expect($context)->toBe('app');
});

it('falls back to default when no resolver matches', function () {
    config()->set('passkeys.defaults.context', 'app');
    config()->set('passkeys.context_detection.resolvers', []);

    $context = $this->action->execute(Request::create('/'));

    expect($context)->toBe('app');
});

describe('it resolves context by first matching resolver', function () {
    test('with multiple domains', function (string $domain, string $context) {
        config()->set('passkeys.context_detection.domains', [
            '*.example.com' => 'app',
            'test.example.com' => 'admin',
        ]);

        $resolvedContext = $this->action->execute(Request::create("https://$domain/"));

        expect($context)->toBe($resolvedContext);
    })->with([
        ['test.example.com', 'admin'],
        ['something.example.com', 'app'],
    ]);

    test('with route names', function (string $uri, string $name, string $context) {
        config()->set('passkeys.context_detection.routes', [
            'name:admin.*' => 'admin',
            'name:app.*' => 'app',
        ]);

        \Illuminate\Support\Facades\Route::get($uri, fn() => 'ok')->name($name);

        $request = Request::create($uri);

        $request->setRouteResolver(function () use ($request) {
            return app('router')->getRoutes()->match($request);
        });

        $resolvedContext = $this->action->execute($request);

        expect($context)->toBe($resolvedContext);
    })->with([
        ['/page', 'app.page', 'app'],
        ['/admin/page', 'admin.page', 'admin'],
    ]);

    test('with non prefixed route names', function (string $uri, string $name, string $context) {
        config()->set('passkeys.context_detection.routes', [
            'admin.*' => 'admin',
            'app.*' => 'app',
        ]);

        \Illuminate\Support\Facades\Route::get($uri, fn() => 'ok')->name($name);

        $request = Request::create($uri);

        $request->setRouteResolver(function () use ($request) {
            return app('router')->getRoutes()->match($request);
        });

        $resolvedContext = $this->action->execute($request);

        expect($context)->toBe($resolvedContext);
    })->with([
        ['/page', 'app.page', 'app'],
        ['/admin/page', 'admin.page', 'admin'],
    ]);

    test('with route paths', function (string $uri, string $context) {
        config()->set('passkeys.context_detection.routes', [
            'path:/admin/*' => 'admin',
            'path:/app/*' => 'app',
        ]);

        \Illuminate\Support\Facades\Route::get($uri, fn() => 'ok');

        $request = Request::create($uri);

        $request->setRouteResolver(function () use ($request) {
            return app('router')->getRoutes()->match($request);
        });

        $resolvedContext = $this->action->execute($request);

        expect($context)->toBe($resolvedContext);
    })->with([
        ['/app/page', 'app'],
        ['/admin/page', 'admin'],
    ]);

    test('with middleware', function () {
        // Arrange: Middleware-Konfiguration mit Alias und Klassen-Basename
        config()->set('passkeys.context_detection.middleware', [
            'throttle' => 'app',
            'authenticate' => 'admin',
        ]);

        $uriAdmin = '/admin/area';
        \Illuminate\Support\Facades\Route::get($uriAdmin, fn () => 'ok')
            ->middleware([\Illuminate\Auth\Middleware\Authenticate::class]);

        $requestAdmin = Request::create($uriAdmin);
        $requestAdmin->setRouteResolver(function () use ($requestAdmin) {
            return app('router')->getRoutes()->match($requestAdmin);
        });

        $resolvedAdmin = $this->action->execute($requestAdmin);
        expect($resolvedAdmin)->toBe('admin');


        $uriApp = '/app/area';
        \Illuminate\Support\Facades\Route::get($uriApp, fn () => 'ok')
            ->middleware(['throttle:60,1']);

        $requestApp = Request::create($uriApp);
        $requestApp->setRouteResolver(function () use ($requestApp) {
            return app('router')->getRoutes()->match($requestApp);
        });

        $resolvedApp = $this->action->execute($requestApp);
        expect($resolvedApp)->toBe('app');
    });
});
