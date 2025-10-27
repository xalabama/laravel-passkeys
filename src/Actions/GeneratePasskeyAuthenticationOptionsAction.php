<?php

namespace Spatie\LaravelPasskeys\Actions;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Spatie\LaravelPasskeys\Support\Config;
use Spatie\LaravelPasskeys\Support\Serializer;
use Webauthn\PublicKeyCredentialRequestOptions;

class GeneratePasskeyAuthenticationOptionsAction
{
    public function execute(string $context): string
    {
        $options = new PublicKeyCredentialRequestOptions(
            challenge: Str::random(),
            rpId: Config::getRelyingParty($context)->id,
            allowCredentials: [],
        );

        $options = Serializer::make()->toJson($options);

        Session::flash('passkey-authentication-options', $options);

        return $options;
    }
}
