<?php
/** @var array $notifications */
$icons = ['chat' => 'chat', 'moderation' => 'shield-check', 'order' => 'package', 'company' => 'building', 'proposal' => 'tag', 'review' => 'star'];
?>
<div class="admin-page-head">
    <div>
        <span class="eyebrow"><?= icon('bell', '', 13) ?> <?= e(t('notify.title')) ?></span>
        <h1><?= e(t('notify.title')) ?></h1>
        <p><?= e(t('admin.notifications_subtitle')) ?></p>
    </div>
    <span class="admin-chip"><?= icon('bell', '', 14) ?> <?= count($notifications) ?></span>
</div>

<section class="admin-card admin-card--flush">
    <?php if ($notifications): ?>
        <div class="notification-feed">
            <?php foreach ($notifications as $notification): ?>
                <a class="notification-item <?= empty($notification['read_at']) ? 'is-unread' : '' ?>" href="<?= url($notification['link'] ?: '/admin') ?>">
                    <span class="notification-item__icon notification-item__icon--<?= e($notification['type']) ?>"><?= icon($icons[$notification['type']] ?? 'bell', '', 18) ?></span>
                    <span class="notification-item__body">
                        <strong><?= e($notification['title']) ?></strong>
                        <?php if (!empty($notification['message'])): ?><p><?= e($notification['message']) ?></p><?php endif; ?>
                        <small><?= e(format_date($notification['created_at'], true)) ?></small>
                    </span>
                    <?php if (empty($notification['read_at'])): ?><span class="notification-item__dot"></span><?php endif; ?>
                    <?= icon('chevron-right', '', 16) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="admin-empty admin-empty--lg"><?= icon('bell', '', 34) ?><p><?= e(t('notify.empty')) ?></p></div>
    <?php endif; ?>
</section>
