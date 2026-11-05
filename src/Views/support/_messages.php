<?php
/** @var array $messages */
/** @var array $chat */
/** @var int $currentUserId */
$currentUserId = (int) ($currentUserId ?? 0);
if (!$messages): ?>
    <div class="chat-empty" data-chat-empty>
        <?= icon('message', '', 30) ?>
        <p><?= e(t('chat.no_messages')) ?></p>
    </div>
<?php endif; ?>

<?php foreach ($messages as $message): ?>
    <?php
    $isSystem = $message['sender_type'] === 'system';
    $isMine = $message['sender_type'] === 'user' && (int) ($message['sender_id'] ?? 0) === $currentUserId;
    if ($message['sender_type'] === 'moderator') {
        $senderName = $message['sender_name'] ?: t('support.team');
    } else {
        $senderName = $isMine ? t('support.you') : ($message['sender_name'] ?: t('support.you'));
    }
    ?>
    <?php if ($isSystem): ?>
        <div class="chat-system" data-message-id="<?= (int) $message['id'] ?>">
            <?= icon('info', '', 13) ?>
            <span><?= e($message['body']) ?></span>
            <time><?= e(date('H:i', strtotime($message['created_at']))) ?></time>
        </div>
    <?php else: ?>
        <div class="chat-message <?= $isMine ? 'is-mine' : '' ?>" data-message-id="<?= (int) $message['id'] ?>">
            <span class="avatar avatar--sm"><?= e(initials($senderName)) ?></span>
            <div class="chat-message__body">
                <span class="chat-message__meta"><strong><?= e($senderName) ?></strong><time><?= e(date('H:i', strtotime($message['created_at']))) ?></time></span>
                <div class="chat-message__bubble"><?= nl2br(e($message['body'])) ?></div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
