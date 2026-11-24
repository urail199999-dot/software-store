<?php

return [
    'site_key' => env('RECAPTCHA_SITE_KEY'),
    'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    'hostnames' => array_values(array_filter(array_map('trim', explode(',', (string) env('RECAPTCHA_HOSTNAMES', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)))))),
];
