<?php

namespace Spatie\LaravelPasskeys\Actions\Resolvers\Context;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Spatie\LaravelPasskeys\Actions\Resolvers\Context\Concerns\HasWildcardMatching;
use Spatie\LaravelPasskeys\Contracts\ContextResolver;
use Spatie\LaravelPasskeys\Support\Config;

class MiddlewareContextResolver implements ContextResolver
{
    use HasWildcardMatching;

    public function resolve(Request $request): ?string
    {
        $route = $request->route();
        if (! $route instanceof Route) {
            return null;
        }

        // Liste aktiver Middleware für diese Route
        $applied = method_exists($route, 'gatherMiddleware')
            ? (array) $route->gatherMiddleware()
            : (array) $route->middleware();

        if ($applied === []) {
            return null;
        }

        // Kandidaten-Keys generieren (alias / alias:param / alias.param / class-basename Varianten)
        $candidates = $this->buildCandidates($applied);

        // Regeln aus der Config lesen: ['pattern' => 'context']
        $rules = Config::getContextDetectionMiddleware();

        if (! is_array($rules) || $rules === []) {
            return null;
        }

        // In der Reihenfolge der definierten Regeln matchen
        foreach ($rules as $pattern => $context) {
            if ($pattern === '' || $context === null || $context === '') {
                continue;
            }

            $pattern = strtolower((string) $pattern);

            foreach ($candidates as $candidate) {
                if ($this->matchesWildcardPattern($candidate, $pattern)) {
                    return (string) $context;
                }
            }
        }

        return null;
    }

    /**
     * Erzeugt mögliche Schlüssel, die gegen die Konfiguration gematcht werden:
     * - alias
     * - alias:param1 (voller Parameter-String)
     * - alias.param1 (erste Param-Variante als Dot-Notation)
     * - classbasename (falls Klassenname verwendet wurde)
     * - classbasename:param1 / classbasename.param1
     */
    private function buildCandidates(array $applied): array
    {
        $set = [];

        foreach ($applied as $raw) {
            $raw = strtolower((string) $raw);

            // Zerlege "name:param1,param2"
            $name = $raw;
            $params = '';
            if (str_contains($raw, ':')) {
                [$name, $params] = explode(':', $raw, 2);
            }

            $firstParam = $params !== '' ? explode(',', $params)[0] : null;

            // Alias-Varianten
            $set[$name] = true;
            if ($params !== '') {
                $set["{$name}:{$params}"] = true;
            }
            if ($firstParam !== null && $firstParam !== '') {
                $set["{$name}.{$firstParam}"] = true;
            }

            // Klassenbasename-Varianten (falls Klassenname verwendet wurde)
            $basename = strtolower(Str::afterLast($name, '\\'));
            if ($basename !== $name) {
                $set[$basename] = true;
                if ($params !== '') {
                    $set["{$basename}:{$params}"] = true;
                }
                if ($firstParam !== null && $firstParam !== '') {
                    $set["{$basename}.{$firstParam}"] = true;
                }
            }
        }

        return array_keys($set);
    }
}
