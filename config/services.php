<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | ReNaPDiS - Servicios de Receta Digital
    |--------------------------------------------------------------------------
    |
    | Configuración para los servicios necesarios para cumplir con la
    | Ley 27.553 de Receta Electrónica y el registro ReNaPDiS
    |
    */

    'refeps' => [
        'url' => env('REFEPS_API_URL', 'https://sisa.msal.gov.ar/sisadoc/docs/refeps'),
        'api_key' => env('REFEPS_API_KEY', ''),
        'enabled' => env('REFEPS_ENABLED', false),
    ],

    'renaper' => [
        'url' => env('RENAPER_API_URL', 'https://api.renaper.gob.ar'),
        'api_key' => env('RENAPER_API_KEY', ''),
        'enabled' => env('RENAPER_ENABLED', false),
    ],

];
