<?php
/** @var array $company */
/** @var array $user */
?>
<p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#3c5772;"><?= e(t('email.hello', ['name' => $user['name']])) ?></p>
<p style="margin:0 0 8px;font-size:19px;font-weight:700;color:#062a4d;"><?= e(t('auth.company_pending_title')) ?></p>
<p style="margin:0 0 22px;font-size:15px;line-height:1.7;color:#3c5772;"><?= e(t('auth.company_pending_text')) ?></p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2f7fe;border-radius:12px;margin:0 0 22px;">
    <tr>
        <td style="padding:16px 18px;font-size:13px;line-height:1.9;color:#3c5772;">
            <strong style="display:block;font-size:14px;color:#062a4d;"><?= e($company['name']) ?></strong>
            <?= e(t('admin.na')) ?> <?= e(t('company.contact')) ?>: <?= e($company['contact_name'] ?? '') ?><br>
            <?= e($user['email']) ?><br>
            <?= e($company['city'] ?? '') ?>
        </td>
    </tr>
</table>

<p style="margin:0 0 8px;font-size:13px;line-height:1.7;color:#64809c;"><?= e(t('company.pending_step_1')) ?></p>
<p style="margin:0 0 8px;font-size:13px;line-height:1.7;color:#64809c;"><?= e(t('company.pending_step_2')) ?></p>
<p style="margin:0 0 22px;font-size:13px;line-height:1.7;color:#64809c;"><?= e(t('company.pending_step_3')) ?></p>

<p style="margin:0;font-size:14px;color:#3c5772;"><?= e(t('email.greeting')) ?></p>
