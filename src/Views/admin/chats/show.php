<?php
/** @var array $chat */
/** @var array $messages */
/** @var bool $isAssigned */
$isOpen = $chat['status'] === 'open';
$isActive = $chat['status'] === 'in_progress';
$isClosed = in_array($chat['status'], ['concluded', 'archived'], true);
$lastId = $messages ? (int) end($messages)['id'] : 0;
$adminId = (int) $currentUser['id'];
?>
<div class="admin-page-head">
    <div>
        <a class="back-link" href="<?= url('/admin/chats') ?>"><?= icon('arrow-left', '', 14) ?> <?= e(t('admin.chats')) ?></a>
        <h1><?= e($chat['subject']) ?></h1>
        <p><?= e($chat['user_name']) ?> · <?= e($chat['user_email']) ?> · <?= e(format_date($chat['created_at'], true)) ?></p>
    </div>
    <div class="admin-page-head__actions">
        <span class="status-pill <?= $isOpen ? 'status-blue' : ($isActive ? 'status-amber' : 'status-green') ?>" data-admin-chat-status><?= e(chat_status_label($chat['status'])) ?></span>
        <?php if ($isOpen): ?>
            <form action="<?= url('/admin/chats/' . $chat['id'] . '/entrar') ?>" method="post">
                <?= csrf_field() ?>
                <button class="btn btn--primary btn--sm" type="submit"><?= icon('chat', '', 15) ?> <?= e(t('chat.join')) ?></button>
            </form>
        <?php elseif ($isActive && $isAssigned): ?>
            <form action="<?= url('/admin/chats/' . $chat['id'] . '/sair') ?>" method="post" data-confirm="<?= e(t('chat.leave_confirm')) ?>">
                <?= csrf_field() ?>
                <button class="btn btn--ghost btn--sm" type="submit"><?= icon('logout', '', 15) ?> <?= e(t('chat.leave')) ?></button>
            </form>
            <form action="<?= url('/admin/chats/' . $chat['id'] . '/concluir') ?>" method="post" data-confirm="<?= e(t('admin.chat_conclude_confirm')) ?>">
                <?= csrf_field() ?>
                <button class="btn btn--primary btn--sm" type="submit"><?= icon('check-circle', '', 15) ?> <?= e(t('chat.conclude')) ?></button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="admin-chat"
     data-admin-chat
     data-chat-id="<?= (int) $chat['id'] ?>"
     data-poll-url="<?= url('/admin/chats/' . $chat['id'] . '/mensagens') ?>"
     data-send-url="<?= url('/admin/chats/' . $chat['id'] . '/mensagens') ?>"
     data-heartbeat-url="<?= url('/admin/chats/' . $chat['id'] . '/heartbeat') ?>"
     data-status="<?= e($chat['status']) ?>"
     data-assigned="<?= $isAssigned ? '1' : '0' ?>">

    <section class="admin-chat__panel">
        <div class="admin-chat__head">
            <div class="admin-chat__customer">
                <span class="avatar"><?= e(initials($chat['user_name'])) ?></span>
                <span><strong><?= e($chat['user_name']) ?></strong><small><?= e($chat['user_email']) ?></small></span>
            </div>
            <div class="admin-chat__assign">
                <?php if (!empty($chat['moderator_name'])): ?>
                    <span class="admin-chip admin-chip--amber"><?= icon('shield', '', 13) ?> <?= e(t('chat.assigned_to', ['name' => $chat['moderator_name']])) ?></span>
                <?php else: ?>
                    <span class="admin-chip"><?= icon('clock', '', 13) ?> <?= e(t('chat.waiting')) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($isOpen): ?>
            <div class="admin-chat__notice"><?= icon('info', '', 15) ?> <?= e(t('admin.chat_waiting')) ?> — <?= e(t('chat.join')) ?>.</div>
        <?php elseif ($isActive && !$isAssigned): ?>
            <div class="admin-chat__notice admin-chat__notice--warning"><?= icon('alert', '', 15) ?> <?= e(t('chat.occupied', ['name' => $chat['moderator_name'] ?? t('support.team')])) ?></div>
        <?php elseif ($isClosed): ?>
            <div class="admin-chat__notice"><?= icon('check-circle', '', 15) ?> <?= e($chat['status'] === 'archived' ? t('chat.archived_notice') : t('chat.concluded_notice')) ?></div>
        <?php endif; ?>

        <div class="chat-messages admin-chat__messages" data-admin-chat-messages>
            <?php require BASE_PATH . '/src/Views/admin/chats/_messages.php'; ?>
        </div>

        <?php if ($isActive && $isAssigned): ?>
            <form class="chat-form admin-chat__form" data-admin-chat-form action="<?= url('/admin/chats/' . $chat['id'] . '/mensagens') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="after" value="<?= $lastId ?>" data-admin-chat-after>
                <label class="sr-only" for="admin-chat-input"><?= e(t('chat.placeholder')) ?></label>
                <textarea id="admin-chat-input" name="message" rows="2" maxlength="2000" placeholder="<?= e(t('chat.placeholder')) ?>" required data-admin-chat-input></textarea>
                <button class="btn btn--primary" type="submit"><?= icon('message', '', 16) ?> <?= e(t('chat.send')) ?></button>
            </form>
        <?php endif; ?>
    </section>

    <aside class="admin-chat__side">
        <div class="admin-card">
            <div class="admin-card__head"><h2><?= icon('info', '', 16) ?> <?= e(t('chat.conversation')) ?></h2></div>
            <div class="admin-detail-list">
                <div><span><?= e(t('admin.chat_subject')) ?></span><strong><?= e($chat['subject']) ?></strong></div>
                <div><span><?= e(t('admin.chat_customer')) ?></span><strong><?= e($chat['user_name']) ?></strong></div>
                <div><span><?= e(t('admin.chat_moderator')) ?></span><strong><?= e($chat['moderator_name'] ?? t('admin.na')) ?></strong></div>
                <div><span><?= e(t('admin.chat_started')) ?></span><strong><?= e(format_date($chat['created_at'], true)) ?></strong></div>
                <div><span><?= e(t('chat.last_activity')) ?></span><strong><?= e(format_date($chat['last_message_at'], true)) ?></strong></div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card__head"><h2><?= icon('shield-check', '', 16) ?> <?= e(t('chat.status.' . $chat['status'])) ?></h2></div>
            <ul class="chat-state-list">
                <li class="<?= $chat['status'] === 'open' ? 'is-current' : '' ?>"><?= icon('chat', '', 14) ?> <?= e(t('chat.status.open')) ?></li>
                <li class="<?= $chat['status'] === 'in_progress' ? 'is-current' : '' ?>"><?= icon('clock', '', 14) ?> <?= e(t('chat.status.in_progress')) ?></li>
                <li class="<?= $chat['status'] === 'concluded' ? 'is-current' : '' ?>"><?= icon('check-circle', '', 14) ?> <?= e(t('chat.status.concluded')) ?></li>
                <li class="<?= $chat['status'] === 'archived' ? 'is-current' : '' ?>"><?= icon('box', '', 14) ?> <?= e(t('chat.status.archived')) ?></li>
            </ul>
            <p class="admin-form__note"><?= icon('info', '', 14) ?> <?= e(t('chat.auto_archive_note')) ?></p>
        </div>

        <a class="btn btn--ghost btn--block" href="<?= url('/admin/chats') ?>"><?= icon('arrow-left', '', 15) ?> <?= e(t('common.back')) ?></a>
    </aside>
</div>
