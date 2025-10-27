<?php

namespace Spatie\LaravelPasskeys\Exceptions;

use Exception;

class InvalidContext extends Exception
{
    public static function make(string $context): self
    {
        return new self("Context `$context` is not existing.");
    }
}
