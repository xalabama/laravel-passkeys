<?php

return [
    /*
     * These class are responsible for performing core tasks regarding passkeys.
     * You can customize them by creating a class that extends the default, and
     * by specifying your custom class name here.
     */
    'actions' => [
        /*
         * Passkey Related Actions
         */
        'generate_passkey_register_options' => Spatie\LaravelPasskeys\Actions\GeneratePasskeyRegisterOptionsAction::class,
        'store_passkey' => Spatie\LaravelPasskeys\Actions\StorePasskeyAction::class,
        'generate_passkey_authentication_options' => \Spatie\LaravelPasskeys\Actions\GeneratePasskeyAuthenticationOptionsAction::class,
        'find_passkey' => Spatie\LaravelPasskeys\Actions\FindPasskeyToAuthenticateAction::class,
        'configure_ceremony_step_manager_factory' => Spatie\LaravelPasskeys\Actions\ConfigureCeremonyStepManagerFactoryAction::class,

        /*
         * Context Resolver Actions
         */
        'resolve_connection' => \Spatie\LaravelPasskeys\Actions\Resolvers\ResolveConnectionAction::class,
        'resolve_relying_party' => \Spatie\LaravelPasskeys\Actions\Resolvers\ResolveRelyingPartyAction::class,
        'resolve_redirect_after_login' => \Spatie\LaravelPasskeys\Actions\Resolvers\ResolveRedirectAfterLoginAction::class,
        'resolve_context' => \Spatie\LaravelPasskeys\Actions\Resolvers\ResolveContextAction::class,
    ],

    /*
     * The model that handles passkeys.
     */
    'passkey_model' => Spatie\LaravelPasskeys\Models\Passkey::class,

    /*
     * The default config options will use as fallback for defined contexts.
     */
    'defaults' => [
        /*
         * The default context that is used if no context was resolved.
         */
        'context' => 'app',

        /*
         * This is used to determine the database connection.
         * Set null to use the applications default database connection.
         */
        'connection' => null,

        /*
         * These properties will be used to generate the passkey.
         */
        'relying_party' => [
            'name' => config('app.name'),
            'id' => config('app.url'),
            'icon' => null,
        ],

        /*
         * After a successful authentication attempt using a passkey
         * we'll redirect to this URL.
         */
        'redirect_to_after_login' => '/dashboard',
    ],

    /*
     * You can define multiple contexts where passkeys available.
     * You can overwrite in each context the definition for each config key inside defaults.
     * If you need to define dynamic configurations, e.g. for multi tenancy,
     * you can define custom context resolver actions inside the 'actions' array.
     */
    'contexts' => [
        'app' => [
            /*
             * The authenticatable model for this context.
             */
            'authenticatable' => App\Models\User::class,

            /*
             * The auth-guard for this context.
             */
            'guard' => 'web',
        ]
    ],

    /*
     * This section is used to handle the context detection.
     * If you have only one context configured, context detection will be ignored.
     */
    'context_detection' => [

        /*
         * Context resolvers are handle the logic to resolve the current context.
         * Resolvers are implemented as a match-first strategy which means that the order indicates their priority.
         * You can remove unused resolvers, add custom resolvers or change their order.
         */
        'resolvers' => [
            Spatie\LaravelPasskeys\Actions\Resolvers\Context\DomainContextResolver::class,
            Spatie\LaravelPasskeys\Actions\Resolvers\Context\MiddlewareContextResolver::class,
            Spatie\LaravelPasskeys\Actions\Resolvers\Context\RouteContextResolver::class,
        ],

        /*
         * Here you can add a domain map with exact or wildcard domains.
         *
         * Supported key patterns:
         * - admin.example.com
         * - *.example.com
         *
         * Domain -> Context
         */
        'domains' => [],

        /*
         * Here you can add a middleware-map with middleware alias names.
         *
         * Middleware-Alias -> Context
         */
        'middleware' => [],

        /*
         * Here you can add a route map with different key patterns.
         *
         * Supported key patterns:
         * - name:admin.* (Route name with wildcards)
         * - path:/admin/* (Route path with wildcards)
         * - admin.* (Handled like name prefixed)
         *
         * Pattern -> Context
         */
        'routes' => [],
    ]
];
