<?php

use Livewire\Livewire;
use Spatie\LaravelPasskeys\Livewire\PasskeysComponent;
use Spatie\LaravelPasskeys\Models\Passkey;
use Spatie\LaravelPasskeys\Tests\TestSupport\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();

    auth()->login($this->user);
});

it('can mount the PasskeysComponent', function () {
    Livewire::test(PasskeysComponent::class)
        ->assertStatus(200);
});

it('allows a user to delete a passkey', function () {
    $passkey = Passkey::factory()->for($this->user, 'authenticatable')->create();

    Livewire::test(PasskeysComponent::class)
        ->call('deletePasskey', $passkey->id);

    expect(Passkey::query()->find($passkey->id))->toBeNull();
});
