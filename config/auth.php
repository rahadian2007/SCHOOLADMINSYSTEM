<?php

return [

    'defaults' => [
        'guard' => 'api',          // Default guard adalah 'api' pakai JWT
        'passwords' => 'kasirs',   // Default password reset (opsional)
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'jwt',          // Gunakan JWT driver
            'provider' => 'kasirs',    // Provider 'kasirs' untuk model Kasir
            'hash' => false,           // Password sudah di-hash, jadi false
        ],

        // Jika mau, bisa tambah guard 'kasir' (optional)

           'kasir' => [
             'driver' => 'jwt',
             'provider' => 'kasirs',
            'hash' => false,
         ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        'kasirs' => [
            'driver' => 'eloquent',
            'model' => App\Models\Kasir::class,
        ],
    ],

    'passwords' => [
        'kasirs' => [
            'provider' => 'kasirs',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
