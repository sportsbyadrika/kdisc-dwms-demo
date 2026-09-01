<?php
/**
 * DWMS 2.0 configuration.
 *
 * Every value is read from the .env file at the project root, with a safe
 * default here — so in normal use you only edit .env and never touch this file.
 *
 * If app/config.php exists it is used instead of this file, which is handy for
 * a host that cannot keep a .env outside version control.
 */
return [
    'app' => [
        'name'      => env('APP_NAME', 'DWMS 2.0'),
        'tagline'   => env('APP_TAGLINE', 'Digital Workforce Management System'),
        // '' at a domain or sub-domain root, '/dwms' when installed in a sub folder.
        'base_url'  => rtrim((string) env('APP_BASE_URL', ''), '/'),
        'debug'     => (bool) env('APP_DEBUG', false),
        'timezone'  => env('APP_TIMEZONE', 'Asia/Kolkata'),
        'org'       => env('APP_ORG', 'Kerala Development and Innovation Strategic Council'),
        'org_short' => env('APP_ORG_SHORT', 'K-DISC'),
    ],
    'db' => [
        'host'    => env('DB_HOST', 'localhost'),
        'port'    => (int) env('DB_PORT', 3306),
        'name'    => env('DB_NAME', 'dwms'),
        'user'    => env('DB_USER', 'root'),
        'pass'    => (string) env('DB_PASS', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
    ],
    'mail' => [
        // The MVP uses PHP's mail(); OTPs are also stored in verification_codes.
        'from'      => env('MAIL_FROM', 'no-reply@example.com'),
        'from_name' => env('MAIL_FROM_NAME', 'DWMS 2.0'),
        // true shows the OTP on screen instead of mailing it. Set to false in production.
        'demo_otp'  => (bool) env('MAIL_DEMO_OTP', true),
    ],
    'security' => [
        'session_name'  => env('SESSION_NAME', 'dwms_session'),
        'session_ttl'   => (int) env('SESSION_TTL', 7200),
        'max_upload_mb' => (int) env('MAX_UPLOAD_MB', 5),
    ],
];
