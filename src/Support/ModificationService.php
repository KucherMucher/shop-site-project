<?php

declare(strict_types=1);

namespace App\Support;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Company;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Page;
use App\Models\PendingModification;
use App\Models\Product;
use App\Models\User;

final class ModificationService
{
    /**
     * Admin changes are applied immediately. Moderator changes are queued for approval.
     *
     * @return array{pending: bool, message: string}
     */
    public static function submit(string $entityType, int $entityId, string $action, array $payload, string $summary): array
    {
        $actor = Auth::user();
        if (!$actor) {
            throw new \RuntimeException('Unauthenticated');
        }

        if ($actor['role_slug'] === 'admin') {
            self::apply($entityType, $entityId, $payload, (int) $actor['id']);
            return ['pending' => false, 'message' => t('admin.changes_saved')];
        }

        PendingModification::create($entityType, $entityId, $action, $payload, $summary, (int) $actor['id']);
        Notification::notifyAdmins(
            'modification',
            t('notify.moderation_title'),
            t('notify.moderation_message', ['name' => $actor['name'], 'summary' => $summary]),
            '/admin/aprovacoes'
        );

        return ['pending' => true, 'message' => t('admin.change_pending')];
    }

    public static function approve(int $modificationId, int $adminId, ?string $note = null): void
    {
        $modification = PendingModification::find($modificationId);
        if (!$modification || $modification['status'] !== 'pending') {
            throw new \RuntimeException(t('admin.not_found'));
        }

        $payload = json_decode($modification['payload'], true) ?: [];

        Database::transaction(function () use ($modification, $payload, $adminId, $note) {
            self::apply($modification['entity_type'], (int) $modification['entity_id'], $payload, $adminId);
            PendingModification::mark((int) $modification['id'], 'approved', $adminId, $note);
            Notification::create(
                (int) $modification['requested_by'],
                'modification',
                t('notify.approved_title'),
                t('notify.approved_message', ['summary' => $modification['summary']]),
                '/admin/aprovacoes'
            );
        });
    }

    public static function reject(int $modificationId, int $adminId, ?string $note = null): void
    {
        $modification = PendingModification::find($modificationId);
        if (!$modification || $modification['status'] !== 'pending') {
            throw new \RuntimeException(t('admin.not_found'));
        }
        PendingModification::mark($modificationId, 'rejected', $adminId, $note);
        Notification::create(
            (int) $modification['requested_by'],
            'modification',
            t('notify.rejected_title'),
            t('notify.rejected_message', ['summary' => $modification['summary']]),
            '/admin/aprovacoes'
        );
    }

    public static function apply(string $entityType, int $entityId, array $payload, int $adminId): bool
    {
        switch ($entityType) {
            case 'variant_stock':
                $stock = (int) ($payload['stock'] ?? -1);
                if ($stock < 0) {
                    throw new \RuntimeException(t('admin.invalid_payload'));
                }
                Database::update('product_variants', ['stock' => $stock], 'id = :id', ['id' => $entityId]);
                return true;

            case 'order_status':
                $status = (string) ($payload['status'] ?? '');
                if (!in_array($status, Order::STATUSES, true)) {
                    throw new \RuntimeException(t('admin.invalid_payload'));
                }
                $order = Order::find($entityId);
                if (!$order) {
                    throw new \RuntimeException(t('admin.not_found'));
                }
                Order::updateStatus($entityId, $status, $payload['tracking_code'] ?? null);
                if ($order['user_id']) {
                    Notification::create(
                        (int) $order['user_id'],
                        'order',
                        t('notify.order_status_title', ['number' => $order['order_number']]),
                        t('notify.order_status_message', ['status' => t('order.status.' . $status)]),
                        '/conta/encomendas/' . $entityId
                    );
                }
                return true;

            case 'page_active':
                $page = Page::find($entityId);
                if (!$page) {
                    throw new \RuntimeException(t('admin.not_found'));
                }
                if ($page['slug'] === 'home') {
                    throw new \RuntimeException(t('admin.home_locked'));
                }
                Page::setActive($entityId, !empty($payload['active']) ? 1 : 0);
                return true;

            case 'user_status':
                $status = (string) ($payload['status'] ?? '');
                if (!in_array($status, ['active', 'suspended'], true)) {
                    throw new \RuntimeException(t('admin.invalid_payload'));
                }
                $target = User::find($entityId);
                if (!$target || $target['role_slug'] === 'admin') {
                    throw new \RuntimeException(t('admin.cannot_change_admin'));
                }
                User::setStatus($entityId, $status);
                return true;

            case 'company_status':
                $status = (string) ($payload['status'] ?? '');
                if (!in_array($status, ['approved', 'rejected'], true)) {
                    throw new \RuntimeException(t('admin.invalid_payload'));
                }
                $company = Company::find($entityId);
                if (!$company) {
                    throw new \RuntimeException(t('admin.not_found'));
                }
                Company::setStatus($entityId, $status, $adminId, $payload['note'] ?? null);
                if (!empty($company['owner_id'])) {
                    User::update((int) $company['owner_id'], ['status' => $status === 'approved' ? 'active' : 'pending']);
                    Notification::create(
                        (int) $company['owner_id'],
                        'company',
                        $status === 'approved' ? t('notify.company_approved_title') : t('notify.company_rejected_title'),
                        t('notify.company_status_message', ['company' => $company['name']]),
                        '/empresa/painel'
                    );
                }
                return true;

            case 'product_status':
                $product = Product::find($entityId);
                if (!$product) {
                    throw new \RuntimeException(t('admin.not_found'));
                }
                $status = (string) ($payload['status'] ?? '');
                if (!in_array($status, ['active', 'rejected'], true)) {
                    throw new \RuntimeException(t('admin.invalid_payload'));
                }
                Product::setStatus($entityId, $status, $payload['note'] ?? null, $adminId);
                if ($product['company_id']) {
                    foreach (Database::fetchAll('SELECT id FROM users WHERE company_id = ?', [$product['company_id']]) as $owner) {
                        Notification::create(
                            (int) $owner['id'],
                            'product',
                            $status === 'active' ? t('notify.product_approved_title') : t('notify.product_rejected_title'),
                            t('notify.product_status_message', ['name' => $product['name_pt']]),
                            '/empresa/painel'
                        );
                    }
                }
                return true;
        }

        throw new \RuntimeException(t('admin.invalid_payload'));
    }
}
