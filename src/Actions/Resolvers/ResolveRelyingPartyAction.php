<?php

namespace Spatie\LaravelPasskeys\Actions\Resolvers;

use Illuminate\Support\Arr;
use Spatie\LaravelPasskeys\Support\Config;
use Spatie\LaravelPasskeys\ValueObjects\RelyingParty;

class ResolveRelyingPartyAction
{
    public function execute(string $context): RelyingParty
    {
        $relyingPartyConfig = config("passkeys.contexts.$context.relying_party");

        if (empty($relyingPartyConfig)) {
            return RelyingParty::fromArray(Config::getDefaultRelyingParty());
        }

        return new RelyingParty(
            $this->resolveRelyingPartyName($relyingPartyConfig),
            $this->resolveRelyingPartyId($relyingPartyConfig),
            $this->resolveRelyingPartyIcon($relyingPartyConfig),
        );
    }

    protected function resolveRelyingPartyName(array $relyingPartyConfig): string
    {
        return Arr::get($relyingPartyConfig, 'name') ?? Config::getDefaultRelyingPartyName();
    }

    protected function resolveRelyingPartyId(array $relyingPartyConfig): string
    {
        $relyingPartyId = Arr::get($relyingPartyConfig, 'id') ?? Config::getDefaultRelyingPartyId();

        return $relyingPartyId;
    }

    protected function resolveRelyingPartyIcon(array $relyingPartyConfig): ?string
    {
        return Arr::get($relyingPartyConfig, 'icon', Config::getDefaultRelyingPartyIcon());
    }
}
