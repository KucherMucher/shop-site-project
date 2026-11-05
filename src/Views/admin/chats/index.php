<?php
/** @var array $chats */
/** @var array $counts */
/** @var string $view */
$isArchive = $view === 'archive';
?>
<div class="admin-page-head">
    <div>
        <span class="eyebrow"><?= icon('chat', '', 13) ?> <?= e(t('admin.chats')) ?></span>
        <h1><?= e($isArchive ? t('admin.archive') : t('admin.chats')) ?></h1>
        <p><?= e($isArchive ? t('admin.archive_subtitle') : t('admin.chats_subtitle')) ?></p>
    </div>
    <div class="admin-page-head__actions">
        <span class="status-pill status-blue"><span class="pulse-dot"></span> <?= (int) $counts['open'] ?> <?= e(t('chat.status.open')) ?></span>
        <span class="status-pill status-amber"><?= (int) $counts['in_progress'] ?> <?= e(t('chat.status.in_progress')) ?></span>
    </div>
</div>

<div class="admin-tabs">
    <a class="admin-tab <?= !$isArchive ? 'is-active' : '' ?>" href="<?= url('/admin/chats') ?>"><?= icon('chat', '', 14) ?> <?= e(t('admin.chats')) ?> <span><?= (int) $counts['open'] + (int) $counts['in_progress'] + (int) $counts['concluded'] ?></span></a>
    <a class="admin-tab <?= $isArchive ? 'is-active' : '' ?>" href="<?= url('/admin/chats/arquivo') ?>"><?= icon('box', '', 14) ?> <?= e(t('admin.archive')) ?> <span><?= (int) $counts['archived'] ?></span></a>
</div>

<?php if (!$chats): ?>
    <div class="admin-card"><div class="admin-empty admin-empty--lg"><?= icon('chat', '', 34) ?><p><?= e(t('admin.chat_empty')) ?></p></div></div>
<?php else: ?>
    <div class="chat-queue">
        <?php foreach ($chats as $chat): ?>
            <article class="chat-queue__item <?= $chat['status'] === 'open' ? 'is-waiting' : '' ?>">
                <span class="chat-queue__avatar"><?= e(initials($chat['user_name'])) ?></span>
                <div class="chat-queue__main">
                    <div class="chat-queue__top">
                        <strong><?= e($chat['subject']) ?></strong>
                    </div>
                    <p><?= e(mb_strimwidth((string) ($chat['last_message'] ?? ''), 0, 130, '…')) ?></p>
                    <div class="chat-queue__meta">
                        <span><?= icon('user', '', 12) ?> <?= e($chat['user_name']) ?></span>
                        <span><?= icon('message', '', 12) ?> <?= (int) $chat['user_messages'] ?> <?= e(t('admin.chat_last_message')) ?></span>
                        <?php if (!empty($chat['moderator_name'])): ?><span><?= icon('shield', '', 12) ?> <?= e($chat['moderator_name']) ?></span><?php endif; ?>
                        <span><?= icon('clock', '', 12) ?> <?= e(format_date($chat['last_message_at'], true)) ?></span>
                    </div>
                </div>
                <div class="chat-queue__actions">
                    <span class="status-pill <?= $chat['status'] === 'open' ? 'status-blue' : ($chat['status'] === 'in_progress' ? 'status-amber' : ($chat['status'] === 'concluded' ? 'status-green' : 'status-gray')) ?>"><?= e(chat_status_label($chat['status'])) ?></span>
                    <?php if ($chat['status'] === 'open'): ?>
                        <span class="admin-chip admin-chip--amber"><?= e(t('admin.chat_waiting')) ?></span>
                    <?php endif; ?>
                    <a class="btn btn--primary btn--sm" href="<?= url('/admin/chats/' . $chat['id']) ?>">
                        <?= icon($chat['status'] === 'open' ? 'chat' : 'eye', '', 14) ?>
                        <?= e($chat['status'] === 'open' ? t('admin.chat_join') : t('admin.view')) ?>
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
