<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/ping', function () {
    return response()->json(['status' => 'ok'], 200);
});

Route::get('/debug-env', function (Request $request) {
    return response()->json([
        'APP_NAME' => env('APP_NAME'),
        'APP_ENV' => env('APP_ENV'),
        'APP_KEY' => env('APP_KEY'),
        'APP_DEBUG' => env('APP_DEBUG'),
        'APP_URL' => env('APP_URL'),
        'APP_LOCALE' => env('APP_LOCALE'),
        'APP_FALLBACK_LOCALE' => env('APP_FALLBACK_LOCALE'),
        'APP_FAKER_LOCALE' => env('APP_FAKER_LOCALE'),
        'APP_MAINTENANCE_DRIVER' => env('APP_MAINTENANCE_DRIVER'),
        'PHP_CLI_SERVER_WORKERS' => env('PHP_CLI_SERVER_WORKERS'),
        'BCRYPT_ROUNDS' => env('BCRYPT_ROUNDS'),
        'LOG_CHANNEL' => env('LOG_CHANNEL'),
        'LOG_STACK' => env('LOG_STACK'),
        'LOG_DEPRECATIONS_CHANNEL' => env('LOG_DEPRECATIONS_CHANNEL'),
        'LOG_LEVEL' => env('LOG_LEVEL'),
        'DB_CONNECTION' => env('DB_CONNECTION'),
        'DB_HOST' => env('DB_HOST'),
        'DB_PORT' => env('DB_PORT'),
        'DB_DATABASE' => env('DB_DATABASE'),
        'DB_USERNAME' => env('DB_USERNAME'),
        'DB_PASSWORD' => env('DB_PASSWORD'), // Cuidado ao exibir senhas!
        'SESSION_DRIVER' => env('SESSION_DRIVER'),
        'SESSION_LIFETIME' => env('SESSION_LIFETIME'),
        'SESSION_ENCRYPT' => env('SESSION_ENCRYPT'),
        'SESSION_PATH' => env('SESSION_PATH'),
        'SESSION_DOMAIN' => env('SESSION_DOMAIN'),
        'BROADCAST_CONNECTION' => env('BROADCAST_CONNECTION'),
        'QUEUE_CONNECTION' => env('QUEUE_CONNECTION'),
        'CACHE_STORE' => env('CACHE_STORE'),
        'MEMCACHED_HOST' => env('MEMCACHED_HOST'),
        'REDIS_CLIENT' => env('REDIS_CLIENT'),
        'REDIS_HOST' => env('REDIS_HOST'),
        'REDIS_PASSWORD' => env('REDIS_PASSWORD'),
        'REDIS_PORT' => env('REDIS_PORT'),
        'MAIL_MAILER' => env('MAIL_MAILER'),
        'MAIL_SCHEME' => env('MAIL_SCHEME'),
        'MAIL_HOST' => env('MAIL_HOST'),
        'MAIL_PORT' => env('MAIL_PORT'),
        'MAIL_USERNAME' => env('MAIL_USERNAME'),
        'MAIL_PASSWORD' => env('MAIL_PASSWORD'), // Cuidado ao exibir senhas!
        'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
        'MAIL_FROM_NAME' => env('MAIL_FROM_NAME'),
        'VITE_APP_NAME' => env('VITE_APP_NAME'),
        'NIGHTWATCH_TOKEN' => env('NIGHTWATCH_TOKEN'), // Cuidado ao exibir tokens!
        'NIGHTWATCH_REQUEST_SAMPLE_RATE' => env('NIGHTWATCH_REQUEST_SAMPLE_RATE'),
        'FILESYSTEM_DISK' => env('FILESYSTEM_DISK'),
        'AWS_ACCESS_KEY_ID' => env('AWS_ACCESS_KEY_ID'), // Cuidado ao exibir credenciais!
        'AWS_SECRET_ACCESS_KEY' => env('AWS_SECRET_ACCESS_KEY'), // Cuidado ao exibir credenciais!
        'AWS_BUCKET' => env('AWS_BUCKET'),
        'AWS_DEFAULT_REGION' => env('AWS_DEFAULT_REGION'),
        'AWS_USE_PATH_STYLE_ENDPOINT' => env('AWS_USE_PATH_STYLE_ENDPOINT'),
        'AWS_URL' => env('AWS_URL'),
    ]);
});
