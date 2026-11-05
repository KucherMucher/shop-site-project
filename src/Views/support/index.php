<?php
/** @var array|null $user */
/** @var array $chats */
/** @var array|null $activeChat */
$faqs = [
    ['q' => t('support.faq_1_q'), 'a' => t('support.faq_1_a')],
    ['q' => t('support.faq_2_q'), 'a' => t('support.faq_2_a')],
    ['q' => t('support.faq_3_q'), 'a' => t('support.faq_3_a')],
    ['q' => t('support.faq_4_q'), 'a' => t('support.faq_4_a')],
];
?>
<section class="page-head page-head--support">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="<?= url('/') ?>"><?= e(t('product.breadcrumb_home')) ?></a>
            <?= icon('chevron-right', '', 13) ?>
            <span><?= e(t('support.title')) ?></span>
        </nav>
        <div class="page-head__row">
            <div>
                <span class="eyebrow"><?= icon('headset', '', 13) ?> <?= e(t('support.title')) ?></span>
                <h1><?= e(t('support.title')) ?></h1>
                <p><?= e(t('support.subtitle')) ?></p>
            </div>
            <div class="support-status">
                <span class="support-status__dot"></span>
                <span><strong><?= e(t('support.online')) ?></strong><small><?= e(t('support.online_hint')) ?></small></span>
            </div>
        </div>
    </div>
</section>

<section class="section section--support">
    <div class="container">
        <div class="support-layout">
            <div class="support-main">
                <?php if (!$user): ?>
                    <div class="support-login">
                        <span class="support-login__icon"><?= icon('chat', '', 34) ?></span>
                        <h2><?= e(t('support.login_title')) ?></h2>
                        <p><?= e(t('support.login_text')) ?></p>
                        <div class="support-login__actions">
                            <a class="btn btn--primary" href="<?= url('/login') ?>"><?= icon('user', '', 16) ?> <?= e(t('nav.login')) ?></a>
                            <a class="btn btn--ghost" href="<?= url('/registo') ?>"><?= e(t('nav.register')) ?></a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php if ($activeChat): ?>
                        <div class="active-chat-banner">
                            <span class="active-chat-banner__icon"><?= icon('chat', '', 22) ?></span>
                            <div>
                                <strong><?= e($activeChat['subject']) ?></strong>
                                <small><?= e(chat_status_label($activeChat['status'])) ?> · <?= e(format_date($activeChat['last_message_at'], true)) ?></small>
                            </div>
                            <a class="btn btn--primary btn--sm" href="<?= url('/suporte/chat/' . $activeChat['id']) ?>"><?= e(t('support.open_chat')) ?> <?= icon('arrow-right', '', 14) ?></a>
                        </div>
                    <?php endif; ?>

                    <section class="content-card support-new">
                        <div class="content-card__head">
                            <span class="eyebrow"><?= icon('plus', '', 13) ?> <?= e(t('support.new_chat')) ?></span>
                            <h2><?= e(t('support.new_chat')) ?></h2>
                        </div>
                        <form action="<?= url('/suporte/chat') ?>" method="post" class="form-stack">
                            <?= csrf_field() ?>
                            <label class="field">
                                <span><?= e(t('support.subject')) ?></span>
                                <input type="text" name="subject" required maxlength="150" value="<?= e(old('subject')) ?>" placeholder="<?= e(t('support.subject_placeholder')) ?>">
                            </label>
                            <label class="field">
                                <span><?= e(t('support.message')) ?></span>
                                <textarea name="message" rows="4" required maxlength="2000" placeholder="<?= e(t('support.message_placeholder')) ?>"><?= e(old('message')) ?></textarea>
                            </label>
                            <button type="submit" class="btn btn--primary"><?= icon('chat', '', 16) ?> <?= e(t('support.start')) ?></button>
                        </form>
                    </section>

                    <section class="content-card">
                        <div class="content-card__head content-card__head--row">
                            <div>
                                <span class="eyebrow"><?= icon('message', '', 13) ?> <?= e(t('support.my_chats')) ?></span>
                                <h2><?= e(t('support.my_chats')) ?></h2>
                            </div>
                        </div>
                        <?php if ($chats): ?>
                            <div class="chat-list">
                                <?php foreach ($chats as $chat): ?>
                                    <a class="chat-list__item" href="<?= url('/suporte/chat/' . $chat['id']) ?>">
                                        <span class="chat-list__icon"><?= icon('chat', '', 18) ?></span>
                                        <span class="chat-list__info">
                                            <strong><?= e($chat['subject']) ?></strong>
                                            <small><?= e(mb_strimwidth((string) ($chat['last_message'] ?? ''), 0, 90, '…')) ?></small>
                                        </span>
                                        <span class="chat-list__meta">
                                            <span class="status-pill <?= $chat['status'] === 'open' ? 'status-blue' : ($chat['status'] === 'in_progress' ? 'status-amber' : 'status-gray') ?>"><?= e(chat_status_label($chat['status'])) ?></span>
                                            <small><?= e(format_date($chat['last_message_at'], true)) ?></small>
                                        </span>
                                        <?= icon('chevron-right', '', 16) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state empty-state--sm">
                                <?= icon('chat', '', 28) ?>
                                <p><?= e(t('support.no_chats')) ?></p>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <section class="content-card support-faq">
                    <div class="content-card__head">
                        <span class="eyebrow"><?= icon('info', '', 13) ?> FAQ</span>
                        <h2><?= e(t('support.faq_title')) ?></h2>
                    </div>
                    <div class="accordion" data-accordion>
                        <?php foreach ($faqs as $index => $faq): ?>
                            <details class="accordion__item" <?= $index === 0 ? 'open' : '' ?>>
                                <summary><?= e($faq['q']) ?> <?= icon('chevron-down', '', 16) ?></summary>
                                <p><?= e($faq['a']) ?></p>
                            </details>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <aside class="support-aside">
                <div class="side-card side-card--support">
                    <span class="eyebrow"><?= icon('headset', '', 13) ?> <?= e(t('support.title')) ?></span>
                    <h3><?= e(t('support.contact_title')) ?></h3>
                    <p><?= e(t('support.contact_text')) ?></p>
                    <ul class="contact-list">
                        <li><?= icon('mail', '', 16) ?> <a href="mailto:hello@trivex.pt">hello@trivex.pt</a></li>
                        <li><?= icon('phone', '', 16) ?> <a href="tel:+351210000000">+351 210 000 000</a></li>
                        <li><?= icon('clock', '', 16) ?> <?= e(t('footer.hours')) ?></li>
                    </ul>
                </div>
                <div class="side-card">
                    <h3><?= icon('truck', '', 17) ?> <?= e(t('home.trust_1_title')) ?></h3>
                    <p><?= e(t('home.trust_1_text')) ?></p>
                </div>
                <div class="side-card side-card--flat">
                    <span><?= icon('check-circle', '', 18) ?> <?= e(t('common.free_shipping', ['amount' => money(60)])) ?></span>
                    <span><?= icon('check-circle', '', 18) ?> <?= e(t('common.free_returns')) ?></span>
                    <span><?= icon('check-circle', '', 18) ?> <?= e(t('common.expert_support')) ?></span>
                </div>
            </aside>
        </div>
    </div>
</section>
