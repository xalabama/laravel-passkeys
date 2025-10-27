<?php

namespace Spatie\LaravelPasskeys\Support;

use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Spatie\LaravelPasskeys\Actions\Resolvers\ResolveConnectionAction;
use Spatie\LaravelPasskeys\Actions\Resolvers\ResolveRedirectAfterLoginAction;
use Spatie\LaravelPasskeys\Actions\Resolvers\ResolveRelyingPartyAction;
use Spatie\LaravelPasskeys\Exceptions\InvalidActionClass;
use Spatie\LaravelPasskeys\Exceptions\InvalidAuthenticatableModel;
use Spatie\LaravelPasskeys\Exceptions\InvalidContext;
use Spatie\LaravelPasskeys\Exceptions\InvalidPasskeyModel;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\LaravelPasskeys\Models\Passkey;
use Spatie\LaravelPasskeys\ValueObjects\RelyingParty;

class Config
{
    /**
     * @return class-string<Passkey>
     */
    public static function getPassKeyModel(): string
    {
        $passkeyModel = config('passkeys.passkey_model');

        if (! is_a($passkeyModel, Passkey::class, true)) {
            throw InvalidPasskeyModel::make($passkeyModel);
        }

        return config('passkeys.passkey_model');
    }

    /** @return class-string<Authenticatable> */
    public static function getAuthenticatableModel(?string $context = null): string
    {
        $context = self::resolveContextName($context);

        /** @var class-string<Authenticatable> $authenticatableModel */
        $authenticatableModel = config("passkeys.contexts.{$context}.authenticatable");

        foreach ([Authenticatable::class, HasPasskeys::class] as $interface) {
            if (! is_a($authenticatableModel, $interface, true)) {
                throw InvalidAuthenticatableModel::missingInterface($authenticatableModel, $interface);
            }
        }

        return $authenticatableModel;
    }

    public static function getRelyingParty(?string $context = null): RelyingParty
    {
        $context = self::resolveContextName($context);

        $action = self::getAction('resolve_relying_party', ResolveRelyingPartyAction::class);

        return $action->execute($context);
    }

    public static function getDefaultRelyingParty(): array
    {
        return config('passkeys.defaults.relying_party');
    }

    /**
     * @deprecated Use Config::getRelyingParty() instead
     */
    public static function getRelyingPartyName(?string $context = null): string
    {
        return self::getRelyingParty($context)->name;
    }

    public static function getDefaultRelyingPartyName(): string
    {
        return config('passkeys.defaults.relying_party.name');
    }

    /**
     * @deprecated Use Config::getRelyingParty() instead
     */
    public static function getRelyingPartyId(?string $context = null): string
    {
        return self::getRelyingParty($context)->id;
    }

    public static function getDefaultRelyingPartyId(): string
    {
        return config('passkeys.defaults.relying_party.id');
    }

    /**
     * @deprecated Use Config::getRelyingParty() instead
     */
    public static function getRelyingPartyIcon(?string $context = null): ?string
    {
        return self::getRelyingParty($context)->icon;
    }

    public static function getDefaultRelyingPartyIcon(): ?string
    {
        return config('passkeys.defaults.relying_party.icon');
    }

    /**
     * @template T
     *
     * @param  class-string<T>  $actionBaseClass
     * @return class-string<T>
     */
    public static function getActionClass(string $actionName, string $actionBaseClass): string
    {
        $actionClass = config("passkeys.actions.{$actionName}") ?? $actionBaseClass;

        self::ensureValidActionClass($actionName, $actionBaseClass, $actionClass);

        return $actionClass;
    }

    /**
     * @template T
     *
     * @param  class-string<T>  $actionBaseClass
     * @return T
     */
    public static function getAction(string $actionName, string $actionBaseClass)
    {
        $actionClass = self::getActionClass($actionName, $actionBaseClass);

        return app($actionClass);
    }

    protected static function ensureValidActionClass(string $actionName, string $actionBaseClass, string $actionClass): void
    {
        if (! is_a($actionClass, $actionBaseClass, true)) {
            throw InvalidActionClass::make($actionName, $actionBaseClass, $actionClass);
        }
    }

    public static function getRedirectAfterLogin(?string $context = null): string
    {
        $context = self::resolveContextName($context);

        $action = self::getAction('resolve_redirect_after_login', ResolveRedirectAfterLoginAction::class);

        return $action->execute($context);
    }

    public static function getDefaultRedirectAfterLogin(): string
    {
        return config('passkeys.defaults.redirect_to_after_login');
    }

    public static function getAllContexts(): array
    {
        return config('passkeys.contexts', []);
    }

    /**
     * @throws InvalidContext
     */
    public static function getContext(?string $name = null): array
    {
        $name ??= self::getDefaultContextName();

        self::ensureContextExists($name);

        return config("passkeys.contexts.{$name}");
    }

    public static function getDefaultContextName(): string
    {
        return config('passkeys.defaults.context');
    }

    public static function getGuard(?string $context = null): string
    {
        $context = self::resolveContextName($context);

        return config("passkeys.contexts.$context.guard");
    }

    public static function getConnection(?string $context = null): string
    {
        $context = self::resolveContextName($context);

        $action = self::getAction('resolve_connection', ResolveConnectionAction::class);

        return $action->execute($context);
    }

    public static function getDefaultConnection(): string
    {
        $connection = config('passkeys.defaults.connection');

        if ($connection === null) {
            return DB::getDefaultConnection();
        }

        return $connection;
    }

    public static function ensureContextExists(string $context): void
    {
        if (! array_key_exists($context, config('passkeys.contexts'))) {
            throw InvalidContext::make($context);
        }
    }

    public static function getContextDetectionResolvers(): array
    {
        return config('passkeys.context_detection.resolvers', []);
    }

    public static function getContextDetectionDomains(): array
    {
        return config('passkeys.context_detection.domains', []);
    }

    public static function getContextDetectionMiddleware(): array
    {
        return config('passkeys.context_detection.middleware', []);
    }

    public static function getContextDetectionRoutes(): array
    {
        return config('passkeys.context_detection.routes', []);
    }

    private static function resolveContextName(?string $context): string
    {
        $context ??= self::getDefaultContextName();
        self::ensureContextExists($context);

        return $context;
    }
}
