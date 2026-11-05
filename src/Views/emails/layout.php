<?php
/** @var string $subject */
/** @var string $preheader */
/** @var string $emailBody */
?>
<!DOCTYPE html>
<html lang="<?= e(locale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($subject) ?></title>
</head>
<body style="margin:0;padding:0;background:#eef3f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#0b2540;">
    <div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;"><?= e($preheader) ?></div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef3f9;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 18px 50px rgba(6,42,77,.12);">
                    <tr>
                        <td style="background:linear-gradient(135deg,#062a4d 0%,#0b3f73 60%,#0b6bff 100%);padding:30px 36px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <span style="display:inline-block;font-size:24px;font-weight:800;letter-spacing:.16em;color:#ffffff;">TRIVEX</span>
                                        <div style="font-size:11px;letter-spacing:.28em;text-transform:uppercase;color:#8fd8ff;margin-top:4px;">swim · bike · run</div>
                                    </td>
                                    <td align="right">
                                        <span style="display:inline-block;padding:7px 14px;border-radius:999px;background:rgba(255,255,255,.14);color:#ffffff;font-size:11px;font-weight:600;letter-spacing:.08em;">TRIATHLON STORE</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:38px 36px 30px;">
                            <h1 style="margin:0 0 12px;font-size:25px;line-height:1.25;color:#062a4d;"><?= e($subject) ?></h1>
                            <?= $emailBody ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 36px 30px;">
                            <div style="border-top:1px solid #e5edf6;padding-top:22px;color:#64809c;font-size:12px;line-height:1.7;">
                                <p style="margin:0 0 6px;"><?= e(t('email.footer')) ?></p>
                                <p style="margin:0;color:#93a9bd;"><?= e(t('email.footer_note')) ?></p>
                                <p style="margin:16px 0 0;">
                                    <a href="<?= e(email_url('/')) ?>" style="color:#0b6bff;text-decoration:none;font-weight:600;">trivex.pt</a>
                                    <span style="color:#c3d2e0;"> · </span>
                                    <a href="<?= e(email_url('/suporte')) ?>" style="color:#64809c;text-decoration:none;"><?= e(t('support.title')) ?></a>
                                </p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f6f9fd;padding:16px 36px;color:#93a9bd;font-size:11px;text-align:center;">
                            © <?= date('Y') ?> TRIVEX · <?= e(t('common.demo_notice')) ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
