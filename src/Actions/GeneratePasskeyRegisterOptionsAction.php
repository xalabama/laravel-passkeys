<?php

namespace Spatie\LaravelPasskeys\Actions;

use Illuminate\Support\Str;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\LaravelPasskeys\Support\Config;
use Spatie\LaravelPasskeys\Support\Serializer;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

class GeneratePasskeyRegisterOptionsAction
{
    public function execute(
        HasPasskeys $authenticatable,
        string $context,
        bool $asJson = true
    ): string|PublicKeyCredentialCreationOptions {
        $options = new PublicKeyCredentialCreationOptions(
            rp: $this->relatedPartyEntity($context),
            user: $this->generateUserEntity($authenticatable),
            challenge: $this->challenge(),
            authenticatorSelection: $this->authenticatorSelection(),
            attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
        );

        if ($asJson) {
            $options = Serializer::make()->toJson($options);
        }

        return $options;
    }

    protected function relatedPartyEntity(string $context): PublicKeyCredentialRpEntity
    {
        $relyingParty = Config::getRelyingParty($context);
        return new PublicKeyCredentialRpEntity(
            name: Config::getRelyingPartyName(),
            id: Config::getRelyingPartyId(),
            icon: Config::getRelyingPartyIcon(),
        );
    }

    public function generateUserEntity(HasPasskeys $authenticatable): PublicKeyCredentialUserEntity
    {
        return new PublicKeyCredentialUserEntity(
            name: $authenticatable->getPassKeyName(),
            id: $authenticatable->getPassKeyId(),
            displayName: $authenticatable->getPassKeyDisplayName(),
        );
    }

    protected function challenge(): string
    {
        return Str::random();
    }

    public function authenticatorSelection(): AuthenticatorSelectionCriteria
    {
        return new AuthenticatorSelectionCriteria(
            null,
            AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_REQUIRED,
        );
    }
}
