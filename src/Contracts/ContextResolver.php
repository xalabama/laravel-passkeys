<?php

namespace Spatie\LaravelPasskeys\Contracts;

use Illuminate\Http\Request;

interface ContextResolver
{
    public function resolve(Request $request): ?string;
}
