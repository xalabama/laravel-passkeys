<?php

namespace Spatie\LaravelPasskeys\Actions\Resolvers;

use Spatie\LaravelPasskeys\Support\Config;

class ResolveConnectionAction
{
    public function execute(string $context)
    {
        $connection = config("passkeys.contexts.{$context}.connection");

        if ($connection === null) {
            return Config::getDefaultConnection();
        }

        return $connection;
    }
}
