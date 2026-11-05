<?php
/** @var array $chat */
/** @var array $messages */
/** @var array $chats */
$pageScripts = ['chat.js'];
$isClosed = !in_array($chat['status'], ['open', 'in_progress'], true);
$lastId = $messages ? (int) end($messages)['id'] : 0;
?>
<section class="section section--chat">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="<?= url('/') ?>"><?= e(t('product.breadcrumb_home')) ?></a>
            <?= icon('chevron-right', '', 13) ?>
            <a href="<?= url('/suporte') ?>"><?= e(t('support.title')) ?></a>
            <?= icon('chevron-right', '', 13) ?>
            <span><?= e($chat['subject']) ?></span>
        </nav>

        <div class="chat-layout" data-chat-window data-chat-id="<?= (int) $chat['id'] ?>" data-poll-url="<?= url('/suporte/chat/' . $chat['id'] . '/mensagens') ?>" data-send-url="<?= url('/suporte/chat/' . $chat['id'] . '/mensagens') ?>" data-status="<?= e($chat['status']) ?>">
            <aside class="chat-sidebar">
                <a class="btn btn--ghost btn--sm btn--block" href="<?= url('/suporte') ?>"><?= icon('arrow-left', '', 15) ?> <?= e(t('support.title')) ?></a>
                <div class="chat-sidebar__head">
                    <span><?= icon('message', '', 15) ?> <?= e(t('support.my_chats')) ?></span>
                </div>
                <div class="chat-sidebar__list">
                    <?php foreach ($chats as $item): ?>
                        <a class="chat-sidebar__item <?= (int) $item['id'] === (int) $chat['id'] ? 'is-active' : '' ?>" href="<?= url('/suporte/chat/' . $item['id']) ?>">
                            <strong><?= e($item['subject']) ?></strong>
                            <small><?= e(chat_status_label($item['status'])) ?></small>
                        </a>
                    <?php endforeach; ?>
                </div>
            </aside>

            <div class="chat-panel">
                <div class="chat-panel__head">
                    <div class="chat-panel__identity">
                        <span class="chat-panel__avatar"><?= icon('chat', '', 20) ?></span>
                        <span>
                            <strong><?= e($chat['subject']) ?></strong>
                            <small><?= e(t('chat.started')) ?> <?= e(format_date($chat['created_at'], true)) ?></small>
                        </span>
                    </div>
                    <div class="chat-panel__status">
                        <span class="status-pill" data-chat-status-pill><?= e(chat_status_label($chat['status'])) ?></span>
                        <small data-chat-moderator>
                            <?php if ($chat['status'] === 'in_progress'): ?>
                                <?= e(!empty($chat['moderator_name']) ? t('support.chat_in_progress', ['name' => $chat['moderator_name']]) : t('chat.waiting')) ?>
                            <?php elseif ($chat['status'] === 'open'): ?>
                                <?= e(t('chat.waiting')) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>

                <div class="chat-notice" data-chat-notice <?= $isClosed ? '' : 'hidden' ?>>
                    <?= icon($chat['status'] === 'archived' ? 'alert' : 'check-circle', '', 16) ?>
                    <span><?= e($chat['status'] === 'archived' ? t('chat.archived_notice') : t('chat.concluded_notice')) ?></span>
                </div>

                <div class="chat-messages" data-chat-messages>
                    <?php require BASE_PATH . '/src/Views/support/_messages.php'; ?>
                </div>

                <form class="chat-form" data-chat-form action="<?= url('/suporte/chat/' . $chat['id'] . '/mensagens') ?>" method="post" <?= $isClosed ? 'hidden' : '' ?>>
                    <?= csrf_field() ?>
                    <input type="hidden" name="after" value="<?= $lastId ?>" data-chat-after>
                    <label class="sr-only" for="chat-input"><?= e(t('chat.placeholder')) ?></label>
                    <textarea id="chat-input" name="message" rows="1" maxlength="2000" placeholder="<?= e(t('chat.placeholder')) ?>" required data-chat-input></textarea>
                    <button type="submit" class="btn btn--primary" data-chat-send><?= icon('message', '', 17) ?> <?= e(t('chat.send')) ?></button>
                </form>
                <p class="chat-form__hint">
                    <?= icon($chat['status'] === 'open' ? 'clock' : 'check-circle', '', 14) ?>
                    <span data-chat-hint>
                        <?php if ($chat['status'] === 'open'): ?><?= e(t('chat.waiting')) ?><?php elseif ($chat['status'] === 'in_progress'): ?><?= e(t('chat.you_are_assigned')) ?><?php else: ?><?= e(t('chat.auto_archive_note')) ?><?php endif; ?>
                    </span>
                </p>
            </div>
        </div>
    </div>
</section>
