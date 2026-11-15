<?php
// Database credentials. This is the only file to edit when moving from
// XAMPP to InfinityFree: replace the four values with the ones shown in
// the InfinityFree control panel (MySQL Databases section).
//
// The connection itself is opened once, in app/models/Database.php.

return [
    'host'    => 'localhost',        // InfinityFree: something like sql123.infinityfree.com
    'name'    => 'maintenance_db',   // InfinityFree: something like if0_12345678_maintenance_db
    'user'    => 'root',             // InfinityFree: something like if0_12345678
    'pass'    => '',                 // InfinityFree: your hosting account password
    'charset' => 'utf8mb4',
];
