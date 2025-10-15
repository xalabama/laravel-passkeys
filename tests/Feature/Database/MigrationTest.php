<?php

use Spatie\LaravelPasskeys\Models\Passkey;

function runPackageMigration(string $name): void
{
    $migration = include __DIR__."/../../../database/migrations/$name.php.stub";
    $migration->up();
}

beforeEach(function () {
    Schema::dropIfExists('passkeys');
    expect(Schema::hasTable('passkeys'))->toBeFalse();
});

it ('can run the initial create_passkeys_table migration', function () {
    runPackageMigration('create_passkeys_table');

    expect(Schema::hasTable('passkeys'))->toBeTrue()
        ->and(Schema::hasColumns('passkeys', [
            'id',
            'authenticatable_id',
            'name',
            'credential_id',
            'data',
            'last_used_at',
            'created_at',
            'updated_at'
        ]))
        ->and(Schema::hasIndex('passkeys', 'passkeys_authenticatable_fk'));
});

describe('it can run the make_passkeys_authenticatable_polymorphic migration', function () {

    test ('without existing data', function () {
        runPackageMigration('create_passkeys_table');
        runPackageMigration('make_passkeys_authenticatable_polymorphic');

        expect(Schema::hasTable('passkeys'))->toBeTrue()
            ->and(Schema::hasColumns('passkeys', [
                'id',
                'authenticatable_type',
                'authenticatable_id',
                'name',
                'credential_id',
                'data',
                'last_used_at',
                'created_at',
                'updated_at',
            ]))
            ->and(Schema::hasIndex('passkeys', 'passkeys_authenticatable_fk'));
    });

    test ('with existing data', function () {
        runPackageMigration('create_passkeys_table');

        $passkey = Passkey::factory()->make();

        unset($passkey->authenticatable_type);

        $passkey->save();

        $this->assertDatabaseCount('passkeys', 1);

        runPackageMigration('make_passkeys_authenticatable_polymorphic');

        $this->assertDatabaseCount('passkeys', 1);

        $this->assertDatabaseHas('passkeys', [
            'id' => $passkey->id,
            'authenticatable_type' => \Spatie\LaravelPasskeys\Tests\TestSupport\Models\User::class,
            'authenticatable_id' => $passkey->authenticatable_id,
        ]);
    });
});
