<?php
/** @var array $pending */
/** @var array $history */
$typeLabels = [
    'user_status' => t('admin.users'),
    'variant_stock' => t('admin.stock'),
    'order_status' => t('admin.orders'),
    'company_status' => t('admin.companies'),
    'product_status' => t('admin.proposals'),
    'page_active' => t('admin.pages'),
];
$typeIcon = [
    'user_status' => 'user',
    'variant_stock' => 'box',
    'order_status' => 'package',
    'company_status' => 'building',
    'product_status' => 'tag',
    'page_active' => 'file',
];
?>
<div class="admin-page-head">
    <div>
        <span class="eyebrow"><?= icon('shield-check', '', 13) ?> <?= e(t('admin.approvals')) ?></span>
        <h1><?= e(t('admin.approvals')) ?></h1>
        <p><?= e(t('admin.approvals_subtitle')) ?></p>
    </div>
    <span class="status-pill status-amber"><?= icon('clock', '', 14) ?> <?= count($pending) ?> <?= e(t('admin.pending')) ?></span>
</div>

<?php if ($pending): ?>
    <div class="approval-list">
        <?php foreach ($pending as $item): ?>
            <?php $payload = json_decode((string) $item['payload'], true) ?: []; ?>
            <article class="approval-card">
                <div class="approval-card__head">
                    <span class="approval-card__icon"><?= icon($typeIcon[$item['entity_type']] ?? 'settings', '', 19) ?></span>
                    <div class="approval-card__title">
                        <span class="admin-chip"><?= e($typeLabels[$item['entity_type']] ?? ucfirst(str_replace('_', ' ', $item['entity_type']))) ?></span>
                        <h2><?= e($item['summary']) ?></h2>
                        <small><?= e(t('admin.requested_by')) ?> <strong><?= e($item['requester_name']) ?></strong> · <?= e(format_date($item['created_at'], true)) ?></small>
                    </div>
                    <span class="status-pill status-amber"><?= e(t('admin.pending')) ?></span>
                </div>

                <div class="approval-card__payload">
                    <?php foreach ($payload as $key => $value): ?>
                        <div><span><?= e(ucfirst(str_replace('_', ' ', (string) $key))) ?></span><strong><?= e(is_bool($value) ? ($value ? t('common.yes') : t('common.no')) : (is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value)) ?></strong></div>
                    <?php endforeach; ?>
                    <div><span><?= e(t('admin.entity')) ?></span><strong>#<?= (int) $item['entity_id'] ?> · <?= e($item['action']) ?></strong></div>
                </div>

                <div class="approval-card__actions">
                    <form action="<?= url('/admin/aprovacoes/' . $item['id'] . '/aprovar') ?>" method="post" class="approval-card__form">
                        <?= csrf_field() ?>
                        <input type="text" name="note" placeholder="<?= e(t('admin.review_note')) ?>" aria-label="<?= e(t('admin.review_note')) ?>">
                        <button class="btn btn--primary btn--sm" type="submit"><?= icon('check', '', 15) ?> <?= e(t('admin.approve_change')) ?></button>
                    </form>
                    <form action="<?= url('/admin/aprovacoes/' . $item['id'] . '/rejeitar') ?>" method="post" class="approval-card__form">
                        <?= csrf_field() ?>
                        <input type="text" name="note" placeholder="<?= e(t('admin.reject_reason')) ?>" aria-label="<?= e(t('admin.reject_reason')) ?>">
                        <button class="btn btn--ghost btn--sm btn--danger-ghost" type="submit"><?= icon('close', '', 15) ?> <?= e(t('admin.reject_change')) ?></button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="admin-card"><div class="admin-empty admin-empty--lg"><?= icon('check-circle', '', 36) ?><p><?= e(t('admin.no_approvals')) ?></p></div></div>
<?php endif; ?>

<section class="admin-card admin-card--flush admin-card--history">
    <div class="admin-card__head admin-card__head--padded">
        <div>
            <span class="eyebrow"><?= icon('clock', '', 13) ?> <?= e(t('admin.approvals')) ?></span>
            <h2><?= e(t('admin.approvals_history')) ?></h2>
        </div>
    </div>
    <?php if ($history): ?>
        <div class="table-wrap">
            <table class="admin-table">
                <thead><tr><th><?= e(t('admin.change_type')) ?></th><th><?= e(t('admin.change_summary')) ?></th><th><?= e(t('admin.requested_by')) ?></th><th><?= e(t('admin.review_note')) ?></th><th><?= e(t('common.status')) ?></th><th><?= e(t('common.date')) ?></th></tr></thead>
                <tbody>
                    <?php foreach ($history as $item): ?>
                        <tr>
                            <td data-label="<?= e(t('admin.change_type')) ?>"><span class="admin-chip"><?= e($typeLabels[$item['entity_type']] ?? ucfirst(str_replace('_', ' ', $item['entity_type']))) ?></span></td>
                            <td data-label="<?= e(t('admin.change_summary')) ?>"><?= e($item['summary']) ?></td>
                            <td data-label="<?= e(t('admin.requested_by')) ?>"><?= e($item['requester_name']) ?></td>
                            <td data-label="<?= e(t('admin.review_note')) ?>"><?= e($item['review_note'] ?: '—') ?></td>
                            <td data-label="<?= e(t('common.status')) ?>"><span class="status-pill <?= $item['status'] === 'approved' ? 'status-green' : 'status-red' ?>"><?= e(t('admin.' . $item['status'])) ?></span></td>
                            <td data-label="<?= e(t('common.date')) ?>"><?= e(format_date($item['reviewed_at'] ?? $item['created_at'], true)) ?><small class="muted"><?= e($item['reviewer_name'] ?? '') ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="admin-empty"><?= icon('clock', '', 26) ?><p><?= e(t('admin.no_data')) ?></p></div>
    <?php endif; ?>
</section>
