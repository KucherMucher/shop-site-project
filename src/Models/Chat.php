<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Chat
{
    public const SWEEP_OPEN_MINUTES = 2;
    public const ARCHIVE_MINUTES = 10;

    public static function start(int $userId, string $subject, string $body): int
    {
        return Database::transaction(function () use ($userId, $subject, $body) {
            $chatId = Database::insert('chats', [
                'user_id' => $userId,
                'subject' => mb_substr($subject, 0, 150),
                'status' => 'open',
                'last_message_at' => date('Y-m-d H:i:s'),
            ]);
            Database::insert('chat_messages', [
                'chat_id' => $chatId,
                'sender_id' => $userId,
                'sender_type' => 'user',
                'body' => $body,
            ]);
            Notification::notifyStaff(
                'chat',
                t('notify.new_chat_title'),
                t('notify.new_chat_message', ['subject' => mb_substr($subject, 0, 80)]),
                '/admin/chats/' . $chatId
            );
            return $chatId;
        });
    }

    public static function find(int $id): ?array
    {
        return Database::fetch(
            'SELECT c.*, u.name AS user_name, u.email AS user_email, m.name AS moderator_name
             FROM chats c
             JOIN users u ON u.id = c.user_id
             LEFT JOIN users m ON m.id = c.moderator_id
             WHERE c.id = ?',
            [$id]
        );
    }

    public static function findForUser(int $id, int $userId): ?array
    {
        return Database::fetch(
            'SELECT c.*, u.name AS user_name, m.name AS moderator_name
             FROM chats c JOIN users u ON u.id = c.user_id
             LEFT JOIN users m ON m.id = c.moderator_id
             WHERE c.id = ? AND c.user_id = ?',
            [$id, $userId]
        );
    }

    public static function forUser(int $userId): array
    {
        return Database::fetchAll(
            'SELECT c.*, m.name AS moderator_name,
                (SELECT body FROM chat_messages cm WHERE cm.chat_id = c.id ORDER BY cm.id DESC LIMIT 1) AS last_message
             FROM chats c LEFT JOIN users m ON m.id = c.moderator_id
             WHERE c.user_id = ? ORDER BY c.last_message_at DESC',
            [$userId]
        );
    }

    public static function latestOpenForUser(int $userId): ?array
    {
        return Database::fetch(
            "SELECT * FROM chats WHERE user_id = ? AND status IN ('open','in_progress') ORDER BY created_at DESC LIMIT 1",
            [$userId]
        );
    }

    public static function messages(int $chatId, int $afterId = 0): array
    {
        return Database::fetchAll(
            'SELECT cm.*, u.name AS sender_name FROM chat_messages cm
             LEFT JOIN users u ON u.id = cm.sender_id
             WHERE cm.chat_id = ? AND cm.id > ? ORDER BY cm.id ASC',
            [$chatId, $afterId]
        );
    }

    public static function postUserMessage(int $chatId, int $userId, string $body): bool
    {
        $chat = self::findForUser($chatId, $userId);
        if (!$chat || !in_array($chat['status'], ['open', 'in_progress'], true)) {
            return false;
        }
        Database::transaction(function () use ($chatId, $userId, $body) {
            Database::insert('chat_messages', [
                'chat_id' => $chatId,
                'sender_id' => $userId,
                'sender_type' => 'user',
                'body' => $body,
            ]);
            Database::query('UPDATE chats SET last_message_at = NOW() WHERE id = ?', [$chatId]);
        });
        return true;
    }

    public static function postModeratorMessage(int $chatId, int $moderatorId, string $body): bool
    {
        $chat = self::find($chatId);
        if (!$chat || $chat['status'] !== 'in_progress' || (int) $chat['moderator_id'] !== $moderatorId) {
            return false;
        }
        Database::transaction(function () use ($chatId, $moderatorId, $body) {
            Database::insert('chat_messages', [
                'chat_id' => $chatId,
                'sender_id' => $moderatorId,
                'sender_type' => 'moderator',
                'body' => $body,
            ]);
            Database::query(
                'UPDATE chats SET last_message_at = NOW(), last_moderator_activity = NOW() WHERE id = ?',
                [$chatId]
            );
        });
        return true;
    }

    public static function join(int $chatId, int $moderatorId): bool
    {
        $chat = self::find($chatId);
        if (!$chat) {
            return false;
        }
        if ($chat['status'] === 'in_progress') {
            if ((int) $chat['moderator_id'] === $moderatorId) {
                self::heartbeat($chatId, $moderatorId);
                return true;
            }
            return false;
        }
        if ($chat['status'] !== 'open') {
            return false;
        }

        $updated = Database::query(
            "UPDATE chats SET status = 'in_progress', moderator_id = ?, last_moderator_activity = NOW()
             WHERE id = ? AND status = 'open'",
            [$moderatorId, $chatId]
        )->rowCount();

        if ($updated === 1) {
            Database::insert('chat_messages', [
                'chat_id' => $chatId,
                'sender_id' => $moderatorId,
                'sender_type' => 'system',
                'body' => t('chat.system_joined'),
            ]);
            Database::query('UPDATE chats SET last_message_at = NOW() WHERE id = ?', [$chatId]);
            return true;
        }
        return false;
    }

    public static function heartbeat(int $chatId, int $moderatorId): void
    {
        Database::query(
            "UPDATE chats SET last_moderator_activity = NOW()
             WHERE id = ? AND moderator_id = ? AND status = 'in_progress'",
            [$chatId, $moderatorId]
        );
    }

    public static function leave(int $chatId, int $moderatorId): bool
    {
        $updated = Database::query(
            "UPDATE chats SET status = 'open', moderator_id = NULL, last_moderator_activity = NULL
             WHERE id = ? AND moderator_id = ? AND status = 'in_progress'",
            [$chatId, $moderatorId]
        )->rowCount();
        if ($updated) {
            Database::insert('chat_messages', [
                'chat_id' => $chatId,
                'sender_id' => null,
                'sender_type' => 'system',
                'body' => t('chat.system_reopened'),
            ]);
        }
        return $updated > 0;
    }

    public static function conclude(int $chatId, int $moderatorId): bool
    {
        $updated = Database::query(
            "UPDATE chats SET status = 'concluded', concluded_at = NOW(), last_moderator_activity = NOW()
             WHERE id = ? AND moderator_id = ? AND status = 'in_progress'",
            [$chatId, $moderatorId]
        )->rowCount();
        if ($updated) {
            Database::insert('chat_messages', [
                'chat_id' => $chatId,
                'sender_id' => $moderatorId,
                'sender_type' => 'system',
                'body' => t('chat.system_concluded'),
            ]);
            Database::query('UPDATE chats SET last_message_at = NOW() WHERE id = ?', [$chatId]);
        }
        return $updated > 0;
    }

    public static function queue(string $view = 'active'): array
    {
        $where = $view === 'archive'
            ? "c.status = 'archived'"
            : "c.status IN ('open','in_progress','concluded')";
        return Database::fetchAll(
            "SELECT c.*, u.name AS user_name, u.email AS user_email, m.name AS moderator_name,
                (SELECT body FROM chat_messages cm WHERE cm.chat_id = c.id ORDER BY cm.id DESC LIMIT 1) AS last_message,
                (SELECT COUNT(*) FROM chat_messages cm2 WHERE cm2.chat_id = c.id AND cm2.sender_type = 'user') AS user_messages
             FROM chats c JOIN users u ON u.id = c.user_id
             LEFT JOIN users m ON m.id = c.moderator_id
             WHERE $where
             ORDER BY FIELD(c.status,'open','in_progress','concluded','archived'), c.last_message_at DESC"
        );
    }

    public static function statusCounts(): array
    {
        $rows = Database::fetchAll('SELECT status, COUNT(*) AS total FROM chats GROUP BY status');
        $counts = ['open' => 0, 'in_progress' => 0, 'concluded' => 0, 'archived' => 0];
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }
        return $counts;
    }

    public static function sweep(): void
    {
        Database::query(
            "UPDATE chats SET status = 'open', moderator_id = NULL, last_moderator_activity = NULL
             WHERE status = 'in_progress'
               AND (last_moderator_activity IS NULL OR last_moderator_activity < DATE_SUB(NOW(), INTERVAL " . self::SWEEP_OPEN_MINUTES . ' MINUTE))'
        );
        Database::query(
            "UPDATE chats SET status = 'archived'
             WHERE status = 'concluded' AND concluded_at IS NOT NULL
               AND concluded_at < DATE_SUB(NOW(), INTERVAL " . self::ARCHIVE_MINUTES . ' MINUTE)'
        );
    }

    public static function messagesForAdmin(int $chatId, int $afterId = 0): array
    {
        return self::messages($chatId, $afterId);
    }

    public static function unreadForStaff(): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM chats WHERE status = 'open'");
    }
}
