<?php
/** @var array $order */
/** @var array $items */
/** @var string $link */
/** @var bool $isGuest */
?>
<p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#3c5772;"><?= e(t('email.hello', ['name' => $order['shipping_name']])) ?></p>
<p style="margin:0 0 6px;font-size:19px;font-weight:700;color:#062a4d;"><?= e(t('order.email_heading')) ?></p>
<p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#3c5772;"><?= e(t('order.email_intro')) ?></p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2f7fe;border-radius:12px;margin:0 0 24px;">
    <tr>
        <td style="padding:14px 18px;font-size:12px;color:#64809c;text-transform:uppercase;letter-spacing:.08em;"><?= e(t('checkout.order_number')) ?></td>
        <td align="right" style="padding:14px 18px;font-size:14px;font-weight:700;color:#062a4d;"><?= e($order['order_number']) ?></td>
    </tr>
</table>

<p style="margin:0 0 10px;font-size:13px;font-weight:700;color:#062a4d;text-transform:uppercase;letter-spacing:.08em;"><?= e(t('email.order_summary')) ?></p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 18px;border-collapse:collapse;">
    <?php foreach ($items as $item): ?>
        <tr>
            <td style="padding:12px 0;border-bottom:1px solid #e8f0f9;">
                <strong style="display:block;font-size:14px;color:#0b2540;"><?= e($item['product_name']) ?></strong>
                <span style="font-size:12px;color:#7b93a9;"><?= e($item['variant_label']) ?> · <?= (int) $item['quantity'] ?> × <?= money((float) $item['unit_price']) ?></span>
            </td>
            <td align="right" valign="top" style="padding:12px 0;border-bottom:1px solid #e8f0f9;font-size:14px;font-weight:700;color:#062a4d;"><?= money((float) $item['line_total']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
    <tr>
        <td style="padding:5px 0;font-size:13px;color:#64809c;"><?= e(t('cart.subtotal')) ?></td>
        <td align="right" style="padding:5px 0;font-size:13px;color:#0b2540;"><?= money((float) $order['subtotal']) ?></td>
    </tr>
    <tr>
        <td style="padding:5px 0;font-size:13px;color:#64809c;"><?= e(t('cart.shipping')) ?></td>
        <td align="right" style="padding:5px 0;font-size:13px;color:#0b2540;"><?= (float) $order['shipping'] <= 0 ? e(t('cart.free')) : money((float) $order['shipping']) ?></td>
    </tr>
    <tr>
        <td style="padding:12px 0 0;border-top:1px solid #e8f0f9;font-size:15px;font-weight:700;color:#062a4d;"><?= e(t('cart.total')) ?></td>
        <td align="right" style="padding:12px 0 0;border-top:1px solid #e8f0f9;font-size:18px;font-weight:800;color:#0b6bff;"><?= money((float) $order['total']) ?></td>
    </tr>
</table>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
    <tr>
        <td valign="top" width="50%" style="padding-right:12px;">
            <p style="margin:0 0 6px;font-size:12px;font-weight:700;color:#062a4d;text-transform:uppercase;letter-spacing:.08em;"><?= e(t('email.shipping_address')) ?></p>
            <p style="margin:0;font-size:13px;line-height:1.7;color:#3c5772;">
                <strong><?= e($order['shipping_name']) ?></strong><br>
                <?= e($order['shipping_address']) ?><br>
                <?= e($order['shipping_postal_code']) ?> <?= e($order['shipping_city']) ?><br>
                <?= e($order['shipping_country']) ?>
            </p>
        </td>
        <td valign="top" width="50%" style="padding-left:12px;">
            <p style="margin:0 0 6px;font-size:12px;font-weight:700;color:#062a4d;text-transform:uppercase;letter-spacing:.08em;"><?= e(t('email.payment_method')) ?></p>
            <p style="margin:0;font-size:13px;line-height:1.7;color:#3c5772;">
                <?= e(t('order.payment.' . $order['payment_method'])) ?><br>
                <span style="color:#7b93a9;"><?= e(t('order.payment_status.' . $order['payment_status'])) ?></span>
                <?php if (!empty($order['payment_reference'])): ?><br><strong style="color:#062a4d;"><?= e($order['payment_reference']) ?></strong><?php endif; ?>
            </p>
        </td>
    </tr>
</table>

<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 22px;">
    <tr>
        <td style="border-radius:12px;background:#0b6bff;">
            <a href="<?= e($link) ?>" style="display:inline-block;padding:14px 28px;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;"><?= e(t('order.email_button')) ?> →</a>
        </td>
    </tr>
</table>

<p style="margin:0 0 8px;font-size:13px;line-height:1.7;color:#64809c;"><?= e(t('order.email_shipping')) ?></p>
<?php if ($isGuest): ?>
    <p style="margin:0;font-size:12px;line-height:1.7;color:#93a9bd;"><?= e(t('order.email_guest_note')) ?></p>
<?php endif; ?>
<p style="margin:22px 0 0;font-size:14px;color:#3c5772;"><?= e(t('email.greeting')) ?></p>
