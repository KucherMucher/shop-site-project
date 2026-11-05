<?php

declare(strict_types=1);

/**
 * TRIVEX installer — creates the database, imports schema + seed data.
 * Usage: php database/install.php
 * WARNING: this drops and recreates the `trivex` database.
 */

require dirname(__DIR__) . '/src/bootstrap.php';

$config = config('db');
$dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $config['host'], $config['port']);

try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "✖ Could not connect to MySQL: {$e->getMessage()}\n");
    fwrite(STDERR, "  Check that MySQL is running in Laragon and that .env has the right credentials.\n");
    exit(1);
}

$file = __DIR__ . '/database.sql';
if (!is_file($file)) {
    fwrite(STDERR, "✖ database.sql not found.\n");
    exit(1);
}

echo "→ Creating database `{$config['database']}` and importing seed data...\n";

try {
    $sql = file_get_contents($file);
    $pdo->exec($sql);
} catch (PDOException $e) {
    fwrite(STDERR, "✖ Import failed: {$e->getMessage()}\n");
    exit(1);
}

// Verify a few expected records.
$check = new PDO($dsn . ';dbname=' . $config['database'], $config['username'], $config['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$counts = [
    'roles' => (int) $check->query('SELECT COUNT(*) FROM roles')->fetchColumn(),
    'users' => (int) $check->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'products' => (int) $check->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'variants' => (int) $check->query('SELECT COUNT(*) FROM product_variants')->fetchColumn(),
    'orders' => (int) $check->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
];

echo "✓ Database imported successfully.\n\n";
foreach ($counts as $table => $count) {
    echo str_pad($table, 12) . " {$count}\n";
}

echo <<<TXT

Demo accounts
─────────────────────────────────────────────
Admin       admin@trivex.pt       Admin123!
Moderator   moderador@trivex.pt   Mod12345!
Customer    cliente@trivex.pt     Cliente123!
Company     empresa@trivex.pt     Empresa123!
─────────────────────────────────────────────

Open http://projeto.test (Laragon virtual host)
or run: php -S 127.0.0.1:8090 -t public public/router.php

TXT;
