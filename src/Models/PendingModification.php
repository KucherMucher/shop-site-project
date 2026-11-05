<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class PendingModification
{
    public static function create(string $entityType, int $entityId, string $action, array $payload, string $summary, int $requestedBy): int
    {
        return Database::insert('pending_modifications', [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'summary' => $summary,
            'requested_by' => $requestedBy,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function pending(): array
    {
        return Database::fetchAll(
            "SELECT pm.*, u.name AS requester_name, u.email AS requester_email
             FROM pending_modifications pm JOIN users u ON u.id = pm.requested_by
             WHERE pm.status = 'pending' ORDER BY pm.created_at ASC"
        );
    }

    public static function all(): array
    {
        return Database::fetchAll(
            "SELECT pm.*, u.name AS requester_name, r.name AS reviewer_name
             FROM pending_modifications pm
             JOIN users u ON u.id = pm.requested_by
             LEFT JOIN users r ON r.id = pm.reviewed_by
             ORDER BY FIELD(pm.status,'pending','approved','rejected'), pm.created_at DESC"
        );
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM pending_modifications WHERE id = ?', [$id]);
    }

    public static function countPending(): int
    {
        return (int) Database::scalar("SELECT COUNT(*) FROM pending_modifications WHERE status = 'pending'");
    }

    public static function mark(int $id, string $status, int $reviewerId, ?string $note = null): void
    {
        Database::update('pending_modifications', [
            'status' => $status,
            'reviewed_by' => $reviewerId,
            'review_note' => $note,
            'reviewed_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $id]);
    }
}
