<?php

namespace Spatie\LaravelPasskeys\Actions\Resolvers\Context;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Spatie\LaravelPasskeys\Actions\Resolvers\Context\Concerns\HasWildcardMatching;
use Spatie\LaravelPasskeys\Contracts\ContextResolver;
use Spatie\LaravelPasskeys\Support\Config;

class RouteContextResolver implements ContextResolver
{
    use HasWildcardMatching;

    protected const string NAME_PREFIX = 'name:';
    protected const string PATH_PREFIX = 'path:';


    public function resolve(Request $request): ?string
    {
        $route = $request->route();

        if (! $route instanceof Route) {
            return null;
        }

        $rules = Config::getContextDetectionRoutes();

        if (! is_array($rules) || $rules === []) {
            return null;
        }

        // Routen-Attribute extrahieren
        $name = $this->normalizeRouteName($route->getName());
        $path = $this->normalizePath('/' . ltrim($this->extractUri($route, $request), '/'));

        // In der Reihenfolge der definierten Regeln matchen
        foreach ($rules as $rawPattern => $context) {
            if ($rawPattern === '' || empty($context)) {
                continue;
            }

            $pattern = strtolower((string) $rawPattern);

            if ($this->isNamePattern($pattern)) {
                if ($name !== null && $this->matchesWildcardPattern($name, $this->stripNamePrefix($pattern))) {
                    return (string) $context;
                }
                continue;
            }

            if ($this->isPathPattern($pattern)) {
                if ($this->matchesWildcardPattern($path, $this->normalizePath($this->stripPathPrefix($pattern)))) {
                    return (string) $context;
                }
                continue;
            }

            // Fallback: behandle Muster ohne Präfix als name:-Pattern
            if ($name !== null && $this->matchesWildcardPattern($name, $pattern)) {
                return (string) $context;
            }
        }

        return null;
    }

    protected function extractUri(Route $route, Request $request): string
    {
        return method_exists($route, 'uri') ? $route->uri() : $request->path();
    }

    protected function normalizeRouteName(?string $name): ?string
    {
        if (empty($name)) {
            return null;
        }

        return strtolower($name);
    }

    protected function normalizePath(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        $path = preg_replace('#/+#', '/', $path) ?? $path;

        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        return strtolower($path);
    }

    protected function isNamePattern(string $pattern): bool
    {
        return str_starts_with($pattern, self::NAME_PREFIX);
    }

    protected function isPathPattern(string $pattern): bool
    {
        return str_starts_with($pattern, self::PATH_PREFIX);
    }

    protected function stripNamePrefix(string $pattern): string
    {
        return substr($pattern, strlen(self::NAME_PREFIX));
    }

    protected function stripPathPrefix(string $pattern): string
    {
        return substr($pattern, strlen(self::PATH_PREFIX));
    }
}
