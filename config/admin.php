<?php

return [
    // URL path of the admin panel. Use something less guessable than "admin" in production.
    'path' => env('ADMIN_PATH', 'admin'),

    // Every admin must set up an authenticator app (two-factor) before using the panel.
    'require_mfa' => (bool) env('ADMIN_REQUIRE_MFA', true),
];
