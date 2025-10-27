<?php

namespace Spatie\LaravelPasskeys\Actions\Resolvers\Context\Concerns;

trait HasWildcardMatching
{
    protected function matchesWildcardPattern(string $candidate, string $pattern): bool
    {
        if ($pattern === '*') {
            return true;
        }

        if (! str_contains($pattern, '*')) {
            return strcmp($candidate, $pattern) === 0;
        }

        $quoted = preg_quote($pattern, '/');
        $regex = '/^' . str_replace('\*', '.*', $quoted) . '$/i';

        return preg_match($regex, $candidate) === 1;
    }
}
