<?php
// Database credentials with automatic environment detection.
// Works seamlessly on both local (XAMPP) and online (InfinityFree)
// so you can run the application in both environments without editing this file.
//
// The connection itself is opened once, in app/models/Database.php.

$httpHost = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';

// Check if explicitly on InfinityFree hosting domains
$isInfinityFree = (
    str_contains($httpHost, 'infinityfree')
    || str_contains($httpHost, 'epizy.com')
    || str_contains($httpHost, 'rf.gd')
);

// Check if running in a local development environment (XAMPP / localhost / CLI)
$isLocal = !$isInfinityFree && (
    PHP_SAPI === 'cli'
    || empty($httpHost)
    || $httpHost === 'localhost'
    || str_starts_with($httpHost, 'localhost:')
    || $httpHost === '127.0.0.1'
    || str_starts_with($httpHost, '127.0.0.1:')
    || $httpHost === '::1'
    || str_starts_with($httpHost, '192.168.')
    || str_starts_with($httpHost, '10.')
    || str_ends_with($httpHost, '.test')
    || str_ends_with($httpHost, '.local')
    || str_ends_with($httpHost, '.localhost')
);

if ($isLocal) {
    // Local environment (XAMPP default)
    return [
        'host'    => 'localhost',
        'name'    => 'maintenance_db',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ];
}

// Online / Production environment (InfinityFree)
return [
    'host'    => 'sql309.infinityfree.com',
    'name'    => 'if0_43448047_maintenance_db',
    'user'    => 'if0_43448047',
    'pass'    => 'iHXOZJCoKf',
    'charset' => 'utf8mb4',
];
