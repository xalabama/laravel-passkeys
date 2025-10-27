<?php

namespace Spatie\LaravelPasskeys\Actions\Resolvers;

use Spatie\LaravelPasskeys\Support\Config;

class ResolveRedirectAfterLoginAction
{
    public function execute(string $context): string
    {
        $path = config("passkeys.contexts.$context.redirect_to_after_login");

        $path ??= Config::getDefaultRedirectAfterLogin();

        return redirect()
            ->intended($path)
            ->getTargetUrl();
    }
}
