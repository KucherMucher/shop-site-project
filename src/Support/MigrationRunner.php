<?php

declare(strict_types=1);

use App\Core\Database;

final class MigrationRunner
{
    public static function run(string $file): void
    {
        $sql = file_get_contents($file);
        $pdo = Database::pdo();
        $pdo->exec($sql);
    }
}
