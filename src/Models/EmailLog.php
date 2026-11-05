<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class EmailLog
{
    public static function create(array $data): void
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        Database::insert('email_log', $data);
    }

    public static function latest(int $limit = 30): array
    {
        return Database::fetchAll('SELECT * FROM email_log ORDER BY created_at DESC LIMIT ' . max(1, $limit));
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM email_log WHERE id = ?', [$id]);
    }

    public static function count(): int
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM email_log');
    }
}
