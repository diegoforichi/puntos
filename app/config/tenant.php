<?php

return [
    'default_email_domain' => env('TENANT_DEFAULT_EMAIL_DOMAIN', 'puntos.local'),
    'default_passwords' => [
        'admin' => env('TENANT_DEFAULT_PASSWORD_ADMIN', 'admin123'),
        'supervisor' => env('TENANT_DEFAULT_PASSWORD_SUPERVISOR', 'supervisor123'),
        'operario' => env('TENANT_DEFAULT_PASSWORD_OPERARIO', 'operario123'),
    ],
];


