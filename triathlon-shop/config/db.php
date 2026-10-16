<?php
declare(strict_types=1);

// Database settings. These are the Laragon / XAMPP defaults.
// Change them if your MySQL user or password is different.
const DB_HOST = '127.0.0.1';
const DB_NAME = 'triathlon_shop';
const DB_USER = 'root';
const DB_PASS = '';

/** Returns one shared PDO connection (created on first use). */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}
