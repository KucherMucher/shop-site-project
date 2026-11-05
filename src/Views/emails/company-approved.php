<?php
/** @var array $company */
/** @var string $link */
?>
<p style="margin:0 0 8px;font-size:19px;font-weight:700;color:#062a4d;"><?= e(t('company.status_approved')) ?> 🎉</p>
<p style="margin:0 0 22px;font-size:15px;line-height:1.7;color:#3c5772;"><?= e(t('mail.company_approved_intro')) ?></p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f0fbf5;border:1px solid #c9efd9;border-radius:12px;margin:0 0 22px;">
    <tr>
        <td style="padding:16px 18px;font-size:13px;line-height:1.8;color:#226b45;">
            <strong style="display:block;font-size:14px;color:#14532d;"><?= e($company['name']) ?></strong>
            <?= e(t('company.status_approved')) ?>
        </td>
    </tr>
</table>

<p style="margin:0 0 22px;font-size:14px;line-height:1.7;color:#3c5772;"><?= e(t('company.dashboard_text')) ?></p>

<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 22px;">
    <tr>
        <td style="border-radius:12px;background:#0b6bff;">
            <a href="<?= e($link) ?>" style="display:inline-block;padding:14px 28px;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;"><?= e(t('nav.login')) ?> →</a>
        </td>
    </tr>
</table>

<p style="margin:0;font-size:14px;color:#3c5772;"><?= e(t('email.greeting')) ?></p>
