<?php

namespace Spatie\LaravelPasskeys\Concerns;

use Illuminate\Http\Request;
use Spatie\LaravelPasskeys\Actions\Resolvers\ResolveContextAction;
use Spatie\LaravelPasskeys\Support\Config;

trait CanResolveContext
{
    protected function resolveContext(Request $request): string
    {
        $action = Config::getAction('resolve_context', ResolveContextAction::class);

        return $action->execute($request);
    }
}
