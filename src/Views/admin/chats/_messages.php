<?php
/** @var array $messages */
/** @var int $adminId */
if (!$messages): ?>
    <div class="chat-empty">
        <?= icon('message', '', 28) ?>
        <p><?= e(t('chat.no_messages')) ?></p>
    </div>
<?php endif; ?>

<?php foreach ($messages as $message): ?>
    <?php
    $isSystem = $message['sender_type'] === 'system';
    $isModerator = $message['sender_type'] === 'moderator';
    $isMine = $isModerator && (int) ($message['sender_id'] ?? 0) === $adminId;
    $senderName = $message['sender_name']
        ?: ($message['sender_type'] === 'user' ? t('admin.chat_customer') : t('support.team'));
    ?>
    <?php if ($isSystem): ?>
        <div class="chat-system" data-message-id="<?= (int) $message['id'] ?>">
            <?= icon('info', '', 13) ?>
            <span><?= e($message['body']) ?></span>
            <time><?= e(date('H:i', strtotime($message['created_at']))) ?></time>
        </div>
    <?php else: ?>
        <div class="chat-message <?= $isMine ? 'is-mine' : '' ?> <?= $isModerator ? 'is-staff' : '' ?>" data-message-id="<?= (int) $message['id'] ?>">
            <span class="avatar avatar--sm <?= $isModerator ? 'avatar--amber' : '' ?>"><?= e(initials($senderName)) ?></span>
            <div class="chat-message__body">
                <span class="chat-message__meta">
                    <strong><?= e($senderName) ?></strong>
                    <?php if ($isModerator): ?><em><?= e(t('admin.role_moderator')) ?></em><?php endif; ?>
                    <time><?= e(date('H:i', strtotime($message['created_at']))) ?></time>
                </span>
                <div class="chat-message__bubble"><?= nl2br(e($message['body'])) ?></div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
