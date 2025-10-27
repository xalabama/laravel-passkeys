<?php

namespace Spatie\LaravelPasskeys\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyAuthenticationOptionsAction;
use Spatie\LaravelPasskeys\Actions\Resolvers\ResolveContextAction;
use Spatie\LaravelPasskeys\Concerns\CanResolveContext;
use Spatie\LaravelPasskeys\Support\Config;

class GeneratePasskeyAuthenticationOptionsController
{
    use CanResolveContext;

    public function __invoke(Request $request)
    {
        $action = Config::getAction(
            'generate_passkey_authentication_options',
            GeneratePasskeyAuthenticationOptionsAction::class
        );

        $options = $action->execute($this->resolveContext($request));

        Session::flash('passkey-registration-options', $options);

        return $options;
    }
}
