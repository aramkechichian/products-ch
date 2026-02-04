<?php

return [

    'default' => 'default',

    'documentations' => [

        'default' => [

            'api' => [
                'title' => 'Products API',
            ],

            /*
             * Routes configuration
             */
            'routes' => [
                'api' => 'api/documentation',
                'docs' => 'docs',
                'oauth2_callback' => 'api/oauth2-callback',
            ],

            /*
             * Paths configuration
             */
            'paths' => [
                'docs_json' => 'api-docs.json',

                // Los docs se generan en storage (NO en public)
                'docs' => storage_path('api-docs'),

                // 👇 CLAVE: NO usar assets en public
                'assets' => null,

                'annotations' => [
                    base_path('app'),
                ],
            ],
        ],
    ],

    /*
     * Swagger UI assets
     * Estos assets se sirven desde vendor vía Laravel, NO desde public/
     */
    'swagger_ui_assets_path' => 'vendor/swagger-api/swagger-ui/dist',

    /*
     * Security
     */
    'securityDefinitions' => [
        'bearerAuth' => [
            'type' => 'apiKey',
            'description' => 'Enter token in format (Bearer <token>)',
            'name' => 'Authorization',
            'in' => 'header',
        ],
    ],

    'generate_always' => true,
    'generate_yaml_copy' => false,

    'proxy' => false,

    'additional_config_url' => null,

    'operations_sort' => null,
    'tags_sort' => null,

    'constants' => [
        'L5_SWAGGER_CONST_HOST' => env('APP_URL', 'http://localhost:8080'),
    ],
];