<?php

use Knuckles\Scribe\Config\AuthIn;
use Knuckles\Scribe\Config\Defaults;
use Knuckles\Scribe\Extracting\Strategies;

use function Knuckles\Scribe\Config\configureStrategy;
use function Knuckles\Scribe\Config\removeStrategies;

// Scribe is a dev-only dependency: return an empty config when it is absent
// (`composer install --no-dev`) so this file cannot fatal on the classes above.
if (! class_exists(AuthIn::class)) {
    return [];
}

return [
    'title' => 'CtrlPanel.gg API Documentation',

    'description' => '',

    'intro_text' => <<<'INTRO'
            This documentation aims to provide all the information you need to work with our API.

            <aside>As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
            You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).</aside>
        INTRO,

    'base_url' => config('app.url'),

    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/*'],
                'domains' => ['*'],
            ],

            // Force-include or exclude routes beyond the match rules above.
            'include' => [],
            'exclude' => [],
        ],
    ],

    'type' => 'laravel',

    'theme' => 'default',

    'static' => [
        'output_path' => 'public/docs',
    ],
    'laravel' => [
        'add_routes' => false,
        'docs_url' => '/docs',
        'assets_directory' => null,
        'middleware' => [],
    ],

    'external' => [
        'html_attributes' => [],
    ],

    'try_it_out' => [
        'enabled' => true,
        'base_url' => null,

        // Fetch a Sanctum CSRF token and send it as X-XSRF-TOKEN.
        'use_csrf' => false,
        'csrf_url' => '/sanctum/csrf-cookie',
    ],

    'auth' => [
        'enabled' => true,
        'default' => true,

        // Where the auth value is sent.
        'in' => AuthIn::BEARER->value,
        'name' => 'key',

        // Value used by Scribe for its own response calls; never shown in docs.
        'use_value' => env('SCRIBE_AUTH_KEY'),
        'placeholder' => '{YOUR_AUTH_KEY}',

        'extra_info' => '',
    ],

    'example_languages' => [
        'bash',
        'javascript',
    ],

    'postman' => [
        'enabled' => false,
        'overrides' => [],
    ],

    'openapi' => [
        'enabled' => true,

        // Supported: '3.0.3' or '3.1.0'.
        'version' => '3.0.3',

        'overrides' => [
            'servers' => [
                [
                    'url' => '{protocol}://{domain}',
                    'description' => 'Your Panel Installation',
                    'variables' => [
                        'protocol' => [
                            'default' => 'https',
                            'enum' => ['http', 'https'],
                        ],
                        'domain' => [
                            'default' => 'panel.example.com',
                            'description' => 'The domain where your panel is installed',
                        ],
                    ],
                ],
            ],
        ],

        // Extra generators extending OpenApiGenerator.
        'generators' => [],
    ],

    'groups' => [
        'default' => 'Endpoints',
        'order' => [],
    ],

    'logo' => false,

    // Tokens: {date:<format>} and {git:short|long}.
    'last_updated' => 'Last updated: {date:F j, Y}',

    'examples' => [
        // Fix generated example values across runs.
        'faker_seed' => 1234,

        // Order Scribe tries when generating example models.
        'models_source' => ['factoryCreate', 'factoryMake', 'databaseFirst'],
    ],

    // Extraction strategies per stage; tweak via configureStrategy()/removeStrategies().
    'strategies' => [
        'metadata' => [
            ...Defaults::METADATA_STRATEGIES,
        ],
        'headers' => [
            ...Defaults::HEADERS_STRATEGIES,
            Strategies\StaticData::withSettings(data: [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]),
        ],
        'urlParameters' => [
            ...Defaults::URL_PARAMETERS_STRATEGIES,
        ],
        'queryParameters' => [
            ...Defaults::QUERY_PARAMETERS_STRATEGIES,
            \App\Docs\Strategies\ExtractQueryBuilderParams::class,
        ],
        'bodyParameters' => [
            ...Defaults::BODY_PARAMETERS_STRATEGIES,
        ],
        'responses' => configureStrategy(
            Defaults::RESPONSES_STRATEGIES,
            Strategies\Responses\ResponseCalls::withSettings(
                only: ['api/*'],
                config: ['app.debug' => false],
            )
        ),
        'responseFields' => [
            ...Defaults::RESPONSE_FIELDS_STRATEGIES,
        ],
    ],

    'database_connections_to_transact' => [config('database.default')],

    'fractal' => [
        'serializer' => null,
    ],
];
