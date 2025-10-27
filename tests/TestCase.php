<?php

namespace Spatie\LaravelPasskeys\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Str;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelPasskeys\LaravelPasskeysServiceProvider;
use Spatie\LaravelPasskeys\Tests\TestSupport\Models\Admin;
use Spatie\LaravelPasskeys\Tests\TestSupport\Models\User;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Str::createRandomStringsUsing(function () {
            return 'fake-random-string';
        });

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Spatie\\LaravelPasskeys\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            LaravelPasskeysServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('auth.providers.users.model', User::class);
        config()->set('passkeys.models.authenticatable', User::class);
        config()->set('app.key', Encrypter::generateKey(config('app.cipher')));

        config()->set('passkeys.contexts.app.authenticatable', User::class);

        config()->set('passkeys.contexts.admin.authenticatable', Admin::class);
        config()->set('passkeys.contexts.admin.redirect_to_after_login', '/admin/dashboard');
        config()->set('passkeys.contexts.admin.guard', 'admin');

        $this->setUpMigrations();
    }

    protected function setUpMigrations(): void
    {
        $migrations = [
            '/../vendor/orchestra/testbench-core/laravel/migrations/0001_01_01_000000_testbench_create_users_table.php',
            '/TestSupport/migrations/testbench_create_admins_table.php',
            '/../database/migrations/create_passkeys_table.php.stub',
            '/../database/migrations/make_passkeys_authenticatable_polymorphic.php.stub',
        ];

        array_any($migrations, function ($migrationPath) {
            $migration = include __DIR__ . $migrationPath;
            $migration->up();
        });
    }
}
