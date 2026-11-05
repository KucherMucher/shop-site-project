<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Notification
{
    public static function create(int $userId, string $type, string $title, ?string $message = null, ?string $link = null): int
    {
        return Database::insert('notifications', [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function notifyStaff(string $type, string $title, ?string $message = null, ?string $link = null): void
    {
        $staff = Database::fetchAll("SELECT u.id FROM users u JOIN roles r ON r.id = u.role_id WHERE r.slug IN ('admin','moderator') AND u.status = 'active'");
        foreach ($staff as $member) {
            self::create((int) $member['id'], $type, $title, $message, $link);
        }
    }

    public static function notifyAdmins(string $type, string $title, ?string $message = null, ?string $link = null): void
    {
        foreach (User::admins() as $admin) {
            self::create((int) $admin['id'], $type, $title, $message, $link);
        }
    }

    public static function forUser(int $userId, int $limit = 20): array
    {
        return Database::fetchAll(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . max(1, $limit),
            [$userId]
        );
    }

    public static function unreadCount(int $userId): int
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL', [$userId]);
    }

    public static function markAllRead(int $userId): void
    {
        Database::query('UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL', [$userId]);
    }

    public static function markRead(int $id, int $userId): void
    {
        Database::query('UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ?', [$id, $userId]);
    }
}
