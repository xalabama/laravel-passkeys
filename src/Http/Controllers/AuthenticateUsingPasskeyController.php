<?php

namespace Spatie\LaravelPasskeys\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Spatie\LaravelPasskeys\Actions\FindPasskeyToAuthenticateAction;
use Spatie\LaravelPasskeys\Concerns\CanResolveContext;
use Spatie\LaravelPasskeys\Events\PasskeyUsedToAuthenticateEvent;
use Spatie\LaravelPasskeys\Http\Requests\AuthenticateUsingPasskeysRequest;
use Spatie\LaravelPasskeys\Models\Passkey;
use Spatie\LaravelPasskeys\Support\Config;

class AuthenticateUsingPasskeyController
{
    use CanResolveContext;

    public function __invoke(AuthenticateUsingPasskeysRequest $request)
    {
        $context = $this->resolveContext($request);
        $passkey = $this->findPasskey($request, $context);

        if (! $passkey) {
            return $this->invalidPasskeyResponse();
        }

        /** @var Authenticatable $authenticatable */
        $authenticatable = $passkey->authenticatable;

        if (! $authenticatable) {
            return $this->invalidPasskeyResponse();
        }

        $this->logInAuthenticatable($authenticatable, $context, $request->boolean('remember'));

        $this->firePasskeyEvent($passkey, $request);

        return $this->validPasskeyResponse($request, $context);
    }

    protected function findPasskey(Request $request, string $context): ?Passkey
    {
        $action = Config::getAction('find_passkey',FindPasskeyToAuthenticateAction::class);

        return $action->execute(
            $request->get('start_authentication_response'),
            Session::get('passkey-authentication-options'),
            $context
        );
    }

    protected function logInAuthenticatable(Authenticatable $authenticatable, string $context, bool $remember = false): self
    {
        $guard = Config::getGuard($context);

        auth($guard)->login($authenticatable, $remember);

        Session::regenerate();

        return $this;
    }

    protected function validPasskeyResponse(Request $request, string $context): RedirectResponse
    {
        $url = Session::has('passkeys.redirect')
            ? Session::pull('passkeys.redirect')
            : Config::getRedirectAfterLogin($context);

        return redirect($url);
    }

    protected function invalidPasskeyResponse(): RedirectResponse
    {
        session()->flash('authenticatePasskey::message', __('passkeys::passkeys.invalid'));

        return back();
    }

    protected function firePasskeyEvent(Passkey $passkey, AuthenticateUsingPasskeysRequest $request): self
    {
        event(new PasskeyUsedToAuthenticateEvent($passkey, $request));

        return $this;
    }
}
