<?php
/**
 * DWMS 2.0 configuration.
 * Copy this file to app/config.php and fill in your shared-hosting details.
 */
return [
    'app' => [
        'name'      => 'DWMS 2.0',
        'tagline'   => 'Digital Workforce Management System',
        'base_url'  => '',                 // e.g. '/dwms' when installed in a sub folder, '' at domain root
        'debug'     => false,
        'timezone'  => 'Asia/Kolkata',
        'org'       => 'Kerala Development and Innovation Strategic Council',
        'org_short' => 'K-DISC',
    ],
    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'name'     => 'dwms',
        'user'     => 'root',
        'pass'     => '',
        'charset'  => 'utf8mb4',
    ],
    'mail' => [
        // MVP uses PHP mail(); OTPs are also written to the verification table.
        'from'      => 'no-reply@example.com',
        'from_name' => 'DWMS 2.0',
        // When true the e-mail / Aadhaar OTP is shown on screen instead of being mailed.
        'demo_otp'  => true,
    ],
    'security' => [
        'session_name'   => 'dwms_session',
        'session_ttl'    => 7200,
        'max_upload_mb'  => 5,
    ],
];
