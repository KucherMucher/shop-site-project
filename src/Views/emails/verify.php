<?php
/** @var array $user */
/** @var string $link */
?>
<p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#3c5772;"><?= e(t('email.hello', ['name' => $user['name']])) ?></p>
<p style="margin:0 0 26px;font-size:15px;line-height:1.7;color:#3c5772;"><?= e(t('auth.verify_email_intro')) ?></p>

<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 26px;">
    <tr>
        <td style="border-radius:12px;background:#0b6bff;">
            <a href="<?= e($link) ?>" style="display:inline-block;padding:14px 28px;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;letter-spacing:.02em;"><?= e(t('auth.verify_email_button')) ?> →</a>
        </td>
    </tr>
</table>

<div style="background:#f2f7fe;border:1px solid #dbe9fb;border-radius:12px;padding:16px 18px;margin:0 0 22px;">
    <p style="margin:0 0 6px;font-size:12px;font-weight:700;color:#062a4d;text-transform:uppercase;letter-spacing:.08em;">Link alternativo</p>
    <p style="margin:0;font-size:12px;line-height:1.6;color:#64809c;word-break:break-all;"><a href="<?= e($link) ?>" style="color:#0b6bff;text-decoration:none;"><?= e($link) ?></a></p>
</div>

<p style="margin:0;font-size:13px;line-height:1.7;color:#93a9bd;"><?= e(t('auth.verify_email_note')) ?></p>
<p style="margin:22px 0 0;font-size:14px;color:#3c5772;"><?= e(t('email.greeting')) ?></p>
