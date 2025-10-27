<?php

namespace Spatie\LaravelPasskeys\Http\Components;

use Illuminate\Support\Facades\Session;
use Illuminate\View\Component;
use Illuminate\View\View;

class AuthenticatePasskeyComponent extends Component
{
    public function __construct(
        public ?string $redirect = null,
        public string $route="passkeys.login",
        public string $optionsRoute = 'passkeys.authentication_options'
    ) {}

    public function render(): View
    {
        if ($this->redirect) {
            Session::put('passkeys.redirect', $this->redirect);
        }

        return view('passkeys::components.authenticate');
    }
}
