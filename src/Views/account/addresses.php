<?php
/** @var array $addresses */
?>
<div class="section-head section-head--account">
    <div>
        <span class="eyebrow"><?= icon('map-pin', '', 13) ?> <?= e(t('account.nav_addresses')) ?></span>
        <h2><?= e(t('account.addresses_title')) ?></h2>
        <p><?= e(t('account.addresses_subtitle')) ?></p>
    </div>
</div>

<div class="addresses-layout">
    <div class="addresses-list">
        <?php if (!$addresses): ?>
            <div class="empty-state empty-state--panel">
                <span class="empty-state__icon"><?= icon('map-pin', '', 34) ?></span>
                <h3><?= e(t('account.no_addresses')) ?></h3>
                <p><?= e(t('account.addresses_subtitle')) ?></p>
            </div>
        <?php endif; ?>

        <?php foreach ($addresses as $address): ?>
            <article class="address-card">
                <div class="address-card__head">
                    <span class="address-card__icon"><?= icon('map-pin', '', 19) ?></span>
                    <div>
                        <strong><?= e($address['label']) ?></strong>
                        <small><?= e($address['name']) ?></small>
                    </div>
                    <div class="address-card__badges">
                        <?php if (!empty($address['is_default_shipping'])): ?><span class="status-pill status-blue"><?= icon('truck', '', 12) ?> <?= e(t('account.default_shipping')) ?></span><?php endif; ?>
                        <?php if (!empty($address['is_default_billing'])): ?><span class="status-pill status-gray"><?= e(t('account.default_billing')) ?></span><?php endif; ?>
                    </div>
                </div>
                <p><?= e($address['address']) ?><br><?= e($address['postal_code']) ?> <?= e($address['city']) ?><br><?= e($address['country']) ?></p>
                <?php if (!empty($address['phone'])): ?><p class="muted"><?= icon('phone', '', 14) ?> <?= e($address['phone']) ?></p><?php endif; ?>
                <div class="address-card__actions">
                    <?php if (empty($address['is_default_shipping'])): ?>
                        <form action="<?= url('/conta/moradas/' . $address['id'] . '/predefinir') ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="type" value="shipping">
                            <button type="submit" class="link-button"><?= icon('check', '', 14) ?> <?= e(t('account.set_default')) ?></button>
                        </form>
                    <?php endif; ?>
                    <form action="<?= url('/conta/moradas/' . $address['id'] . '/eliminar') ?>" method="post" data-confirm="<?= e(t('account.address_delete_confirm')) ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="link-button link-button--danger"><?= icon('trash', '', 14) ?> <?= e(t('common.delete')) ?></button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <aside class="addresses-aside">
        <section class="content-card content-card--form">
            <div class="content-card__head">
                <span class="eyebrow"><?= icon('plus', '', 13) ?> <?= e(t('account.addresses_title')) ?></span>
                <h2><?= e(t('account.add_address')) ?></h2>
            </div>
            <form action="<?= url('/conta/moradas') ?>" method="post" class="form-stack">
                <?= csrf_field() ?>
                <div class="form-grid">
                    <label class="field">
                        <span><?= e(t('account.address_label')) ?></span>
                        <input type="text" name="label" value="<?= e(old('label')) ?>" placeholder="<?= e(t('account.address_label_hint')) ?>">
                    </label>
                    <label class="field">
                        <span><?= e(t('common.name')) ?> *</span>
                        <input type="text" name="name" value="<?= e(old('name')) ?>" required autocomplete="name">
                    </label>
                    <label class="field field--full">
                        <span><?= e(t('common.address')) ?> *</span>
                        <input type="text" name="address" value="<?= e(old('address')) ?>" required autocomplete="street-address">
                    </label>
                    <label class="field">
                        <span><?= e(t('common.postal_code')) ?> *</span>
                        <input type="text" name="postal_code" value="<?= e(old('postal_code')) ?>" required autocomplete="postal-code">
                    </label>
                    <label class="field">
                        <span><?= e(t('common.city')) ?> *</span>
                        <input type="text" name="city" value="<?= e(old('city')) ?>" required autocomplete="address-level2">
                    </label>
                    <label class="field">
                        <span><?= e(t('common.phone')) ?></span>
                        <input type="tel" name="phone" value="<?= e(old('phone')) ?>" autocomplete="tel">
                    </label>
                    <label class="field">
                        <span><?= e(t('common.country')) ?></span>
                        <select name="country">
                            <option value="Portugal">Portugal</option>
                            <option value="Espanha">Espanha</option>
                            <option value="França">França</option>
                        </select>
                    </label>
                </div>
                <div class="check-stack">
                    <label class="check-row">
                        <input type="checkbox" name="is_default_shipping" value="1">
                        <span class="check-row__box"><?= icon('check', '', 12) ?></span>
                        <span><?= e(t('account.default_shipping')) ?></span>
                    </label>
                    <label class="check-row">
                        <input type="checkbox" name="is_default_billing" value="1">
                        <span class="check-row__box"><?= icon('check', '', 12) ?></span>
                        <span><?= e(t('account.default_billing')) ?></span>
                    </label>
                </div>
                <button type="submit" class="btn btn--primary btn--block"><?= icon('plus', '', 16) ?> <?= e(t('account.add_address')) ?></button>
            </form>
        </section>
    </aside>
</div>
