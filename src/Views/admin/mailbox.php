<?php
/** @var array $emails */
?>
<div class="admin-page-head">
    <div>
        <span class="eyebrow"><?= icon('mail', '', 13) ?> <?= e(t('admin.mailbox')) ?></span>
        <h1><?= e(t('admin.mailbox')) ?></h1>
        <p><?= e(t('admin.mailbox_subtitle')) ?></p>
    </div>
    <span class="admin-chip"><?= icon('settings', '', 14) ?> <?= e(t('admin.mail_driver')) ?>: <?= e(config('mail.driver')) ?></span>
</div>

<section class="admin-card admin-card--flush">
    <?php if ($emails): ?>
        <div class="table-wrap">
            <table class="admin-table admin-table--mail">
                <thead><tr><th><?= e(t('admin.mail_to')) ?></th><th><?= e(t('admin.mail_subject')) ?></th><th><?= e(t('admin.mail_driver')) ?></th><th><?= e(t('common.status')) ?></th><th><?= e(t('admin.mail_date')) ?></th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($emails as $email): ?>
                        <tr>
                            <td data-label="<?= e(t('admin.mail_to')) ?>"><?= e($email['to_email']) ?></td>
                            <td data-label="<?= e(t('admin.mail_subject')) ?>"><strong><?= e($email['subject']) ?></strong><small class="muted"><?= e(mb_strimwidth(strip_tags((string) $email['body']), 0, 90, '…')) ?></small></td>
                            <td data-label="<?= e(t('admin.mail_driver')) ?>"><span class="admin-chip"><?= e($email['driver']) ?></span></td>
                            <td data-label="<?= e(t('common.status')) ?>"><span class="status-pill <?= $email['status'] === 'sent' || $email['status'] === 'logged' ? 'status-green' : 'status-red' ?>"><?= e($email['status']) ?></span></td>
                            <td data-label="<?= e(t('admin.mail_date')) ?>"><?= e(format_date($email['created_at'], true)) ?></td>
                            <td class="admin-table__actions"><a class="btn btn--ghost btn--sm" href="<?= url('/admin/mailbox/' . $email['id']) ?>"><?= icon('eye', '', 14) ?> <?= e(t('admin.mail_preview')) ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="admin-empty admin-empty--lg"><?= icon('mail', '', 34) ?><p><?= e(t('admin.mail_empty')) ?></p></div>
    <?php endif; ?>
</section>
