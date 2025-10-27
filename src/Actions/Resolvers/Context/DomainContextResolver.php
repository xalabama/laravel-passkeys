<?php

namespace Spatie\LaravelPasskeys\Actions\Resolvers\Context;

use Illuminate\Http\Request;
use Spatie\LaravelPasskeys\Contracts\ContextResolver;
use Spatie\LaravelPasskeys\Support\Config;

class DomainContextResolver implements ContextResolver
{
    public function resolve(Request $request): ?string
    {
        $host = $this->normalizeHost($request->host());

        if ($host === '') {
            return null;
        }

        $rules = Config::getContextDetectionDomains();

        if (!is_array($rules) || $rules === []) {
            return null;
        }

        $exactMatch = $this->matchFirstExactRule($host, $rules);

        if ($exactMatch !== null) {
            return $exactMatch;
        }

        return $this->matchFirstWildcardRule($host, $rules);
    }

    protected function normalizeHost(?string $host): ?string
    {
        $host = strtolower((string) $host);

        return rtrim($host, '.');
    }

    protected function isWildcardPattern(string $pattern): bool
    {
        return str_contains($pattern, '*');
    }

    protected function matchFirstExactRule(string $host, array $rules): ?string
    {
        foreach ($rules as $pattern => $context) {
            if ($pattern === '' || empty($context)) {
                continue;
            }

            if (! $this->isWildcardPattern($pattern) && $this->hostMatchesPattern($host, $pattern)) {
                return (string) $context;
            }
        }

        return null;
    }

    protected function matchFirstWildcardRule(string $host, array $rules): ?string
    {
        foreach ($rules as $pattern => $context) {
            if ($pattern === '' || empty($context)) {
                continue;
            }

            $pattern = $this->normalizeHost((string) $pattern);

            if ($this->isWildcardPattern($pattern) && $this->hostMatchesPattern($host, $pattern)) {
                return (string) $context;
            }
        }

        return null;
    }

    private function hostMatchesPattern(string $host, string $pattern): bool
    {
        $host = rtrim(strtolower($host), '.');
        $pattern = rtrim(strtolower($pattern), '.');

        if (strpos($pattern, '*') === false) {
            return strcasecmp($host, $pattern) === 0;
        }

        $quoted = preg_quote($pattern, '/');
        $regex = '/^' . str_replace('\*', '.*', $quoted) . '$/i';

        return preg_match($regex, $host) === 1;
    }
}
