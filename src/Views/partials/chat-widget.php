<?php
use App\Core\Auth;
use App\Models\Chat;

$chatUser = Auth::user();
$chatLink = url('/suporte');
if ($chatUser) {
    $activeChat = Chat::latestOpenForUser((int) $chatUser['id']);
    if ($activeChat) {
        $chatLink = url('/suporte/chat/' . $activeChat['id']);
    }
}
?>
<a class="support-fab" href="<?= $chatLink ?>" aria-label="<?= e(t('support.chat_aria')) ?>">
    <span class="support-fab__pulse"></span>
    <?= icon('chat', '', 22) ?>
    <span class="support-fab__label"><?= e(t('nav.support')) ?></span>
</a>
