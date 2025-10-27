<?php

namespace Spatie\LaravelPasskeys\Actions\Resolvers;

use Illuminate\Http\Request;
use InvalidArgumentException;
use Spatie\LaravelPasskeys\Support\Config;

class ResolveContextAction
{
    public function execute(Request $request): string
    {
        if (count(Config::getAllContexts()) === 1) {
            return Config::getDefaultContextName();
        }

        foreach (Config::getContextDetectionResolvers() as $resolverClass) {
            $resolver = app($resolverClass);
            $context = $resolver->resolve($request);

            if ($context !== null) {
                return $context;
            }
        }

        return Config::getDefaultContextName();
    }
}
