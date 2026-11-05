<?php
/** @var array $email */
?>
<div class="admin-page-head">
    <div>
        <a class="back-link" href="<?= url('/admin/mailbox') ?>"><?= icon('arrow-left', '', 14) ?> <?= e(t('admin.mailbox')) ?></a>
        <h1><?= e($email['subject']) ?></h1>
        <p><?= e(t('admin.mail_to')) ?> <?= e($email['to_email']) ?> · <?= e(format_date($email['created_at'], true)) ?></p>
    </div>
    <div class="admin-page-head__actions">
        <span class="admin-chip"><?= e($email['driver']) ?></span>
        <span class="status-pill <?= $email['status'] === 'sent' || $email['status'] === 'logged' ? 'status-green' : 'status-red' ?>"><?= e($email['status']) ?></span>
    </div>
</div>

<section class="admin-card admin-card--flush mail-preview">
    <div class="mail-preview__bar">
        <span><?= icon('mail', '', 15) ?> <?= e(t('admin.mail_preview')) ?></span>
        <span class="mail-preview__meta"><?= e(config('mail.from_name')) ?> &lt;<?= e(config('mail.from')) ?>&gt;</span>
    </div>
    <iframe class="mail-preview__frame" title="<?= e($email['subject']) ?>" sandbox="" srcdoc="<?= e($email['body']) ?>"></iframe>
</section>
