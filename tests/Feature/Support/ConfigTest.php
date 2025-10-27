<?php

use Spatie\LaravelPasskeys\Actions\ConfigureCeremonyStepManagerFactoryAction;
use Spatie\LaravelPasskeys\Actions\FindPasskeyToAuthenticateAction;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyAuthenticationOptionsAction;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyRegisterOptionsAction;
use Spatie\LaravelPasskeys\Actions\Resolvers\ResolveConnectionAction;
use Spatie\LaravelPasskeys\Actions\Resolvers\ResolveContextAction;
use Spatie\LaravelPasskeys\Actions\Resolvers\ResolveRedirectAfterLoginAction;
use Spatie\LaravelPasskeys\Actions\Resolvers\ResolveRelyingPartyAction;
use Spatie\LaravelPasskeys\Actions\StorePasskeyAction;
use Spatie\LaravelPasskeys\Support\Config;

it('can get the model classes', function () {
    expect(Config::getPassKeyModel())->not()->toBeNull();
});

it ('can get authenticatable model classes by context', function (?string $context, string $modelClass) {
    expect(Config::getAuthenticatableModel($context))->toBe($modelClass);
})->with([
    [null, \Spatie\LaravelPasskeys\Tests\TestSupport\Models\User::class],
    ['app', \Spatie\LaravelPasskeys\Tests\TestSupport\Models\User::class],
    ['admin', \Spatie\LaravelPasskeys\Tests\TestSupport\Models\Admin::class],
]);

describe('it can get the relying party configuration', function () {
    test('by context', function (?string $context, string $name, string $id, ?string $icon) {
       config()->set('passkeys.contexts.app.relying_party', [
           'name' => 'test app',
           'id' => 'test.example',
           'icon' => null,
       ]);

       config()->set('passkeys.contexts.admin.relying_party', [
           'name' => 'test admin app',
           'id' => 'admin.test.example',
           'icon' => 'test-icon',
       ]);

       expect(Config::getRelyingParty($context))
           ->name->toBe($name)
           ->id->toBe($id)
           ->icon->toBe($icon);
    })->with([
        [null, 'test app', 'test.example', null],
        ['app', 'test app', 'test.example', null],
        ['admin', 'test admin app', 'admin.test.example', 'test-icon'],
    ]);

    test('with fallbacks', function () {
        config()->set('passkeys.contexts.app.relying_party', []);
        config()->set('passkeys.defaults.relying_party', [
            'name' => 'default app',
            'id' => 'default.id',
            'icon' => 'default-icon',
        ]);

        expect(Config::getRelyingParty('app'))
            ->name->toBe('default app')
            ->id->toBe('default.id')
            ->icon->toBe('default-icon');
    });
});

it('can get the default action classes', function (string $actionName, string $actionBaseClass) {
    expect(Config::getActionClass($actionName, $actionBaseClass))->not->toBeNull()->toBe($actionBaseClass);
})->with([
    ['generate_passkey_register_options', GeneratePasskeyRegisterOptionsAction::class],
    ['store_passkey', StorePasskeyAction::class],
    ['generate_passkey_authentication_options', GeneratePasskeyAuthenticationOptionsAction::class],
    ['find_passkey', FindPasskeyToAuthenticateAction::class],
    ['configure_ceremony_step_manager_factory', ConfigureCeremonyStepManagerFactoryAction::class],
    ['resolve_connection', ResolveConnectionAction::class],
    ['resolve_relying_party', ResolveRelyingPartyAction::class],
    ['resolve_redirect_after_login', ResolveRedirectAfterLoginAction::class],
    ['resolve_context', ResolveContextAction::class],
]);

it ('can get contexts', function () {
    expect(Config::getAllContexts())->not()->toBeNull()->toBeArray();
});

it ('can get context by name', function () {
    expect(Config::getContext('app'))->not()->toBeNull()->toBeArray();
});

it ('can get default context', function () {
    expect(Config::getContext())->not()->toBeNull();
});

describe('it can get redirect to after login', function () {
    test('by context', function (?string $context, string $path) {
        expect(Config::getRedirectAfterLogin($context))->toBe(config('app.url') . $path);
    })->with([
        [null, '/dashboard'],
        ['app', '/dashboard'],
        ['admin', '/admin/dashboard']
    ]);

    test('default fallback', function () {
        config()->set('passkeys.contexts.app.redirect_to_after_login');
        config()->set('passkeys.defaults.redirect_to_after_login', '/test');

        expect(Config::getRedirectAfterLogin('app'))->toBe(redirect()->intended('/test')->getTargetUrl());
    });
});

it ('can get guard by context', function (?string $context, string $guardName) {
     expect(Config::getGuard($context))->toBe($guardName);
})->with([
    [null, 'web'],
    ['app', 'web'],
    ['admin', 'admin'],
]);

describe('it can get connection', function () {
    test ('by context', function (?string $context, string $connection) {
        config()->set('passkeys.contexts.app.connection', 'app_connection');
        config()->set('passkeys.contexts.admin.connection', 'admin_connection');

        expect(Config::getConnection($context))->toBe($connection);
    })->with([
        [null, 'app_connection'],
        ['app', 'app_connection'],
        ['admin', 'admin_connection'],
    ]);

    test ('with default fallback', function () {
        config()->set('passkeys.contexts.app.connection');
        config()->set('passkeys.defaults.connection', 'default_connection');

        expect(Config::getConnection('app'))->toBe('default_connection');
    });

    test ('with application fallback connection', function () {
        config()->set('passkeys.contexts.app.connection');
        config()->set('passkeys.defaults.connection');
        config()->set('database.default', 'app_fallback_connection');

        expect(Config::getConnection('app'))->toBe('app_fallback_connection');
    });
});

it('can get context detection resolvers', function () {
    expect(Config::getContextDetectionResolvers())->not()->toBeNull()->toBeArray();
});

it('can get context detection domains', function () {
    expect(Config::getContextDetectionDomains())->not()->toBeNull()->toBeArray();
});

it('can get context detection middleware', function () {
    expect(Config::getContextDetectionMiddleware())->not()->toBeNull()->toBeArray();
});

it('can get context detection routes', function () {
    expect(Config::getContextDetectionRoutes())->not()->toBeNull()->toBeArray();
});
