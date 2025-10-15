<?php

namespace Spatie\LaravelPasskeys\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\LaravelPasskeys\Support\Config;

trait InteractsWithPasskeys
{
    public function passkeys(): MorphMany
    {
        $passkeyModel = Config::getPassKeyModel();

        return $this->morphMany($passkeyModel, 'authenticatable');
    }

    public function getPasskeyName(): string
    {
        return $this->email;
    }

    public function getPasskeyId(): string
    {
        return $this->id;
    }

    public function getPasskeyDisplayName(): string
    {
        return $this->name;
    }
}
