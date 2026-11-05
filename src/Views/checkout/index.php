<?php
/** @var array $items */
/** @var float $subtotal */
/** @var float $shipping */
/** @var float $total */
/** @var array|null $user */
/** @var array $addresses */
/** @var array|null $defaultAddress */
/** @var array $cartProblems */
$paymentMethod = (string) old('payment_method', 'card');
$sameBilling = old('billing_same', '1') !== '0';
$val = static function (string $key, string $default = '') use ($defaultAddress): string {
    $fallback = $defaultAddress[$key] ?? $default;
    return (string) old($key, $fallback);
};
$addressJson = [];
foreach ($addresses as $address) {
    $addressJson[(string) $address['id']] = [
        'name' => $address['name'],
        'phone' => $address['phone'] ?? '',
        'address' => $address['address'],
        'city' => $address['city'],
        'postal_code' => $address['postal_code'],
        'country' => $address['country'],
    ];
}
?>
<section class="page-head page-head--compact">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="<?= url('/') ?>"><?= e(t('product.breadcrumb_home')) ?></a>
            <?= icon('chevron-right', '', 13) ?>
            <a href="<?= url('/carrinho') ?>"><?= e(t('cart.title')) ?></a>
            <?= icon('chevron-right', '', 13) ?>
            <span><?= e(t('checkout.title')) ?></span>
        </nav>
        <div class="checkout-heading">
            <div>
                <span class="eyebrow"><?= icon('lock', '', 13) ?> <?= e(t('cart.secure')) ?></span>
                <h1><?= e(t('checkout.title')) ?></h1>
            </div>
            <ol class="checkout-steps">
                <li class="is-active"><span>1</span> <?= e(t('checkout.step_contact')) ?></li>
                <li class="is-active"><span>2</span> <?= e(t('checkout.step_shipping')) ?></li>
                <li class="is-active"><span>3</span> <?= e(t('checkout.step_payment')) ?></li>
            </ol>
        </div>
    </div>
</section>

<section class="section section--checkout">
    <div class="container">
        <?php if ($cartProblems): ?>
            <div class="alert alert--error">
                <?= icon('alert', '', 18) ?>
                <div>
                    <strong><?= e(t('cart.insufficient_stock', ['name' => '', 'stock' => 0])) ?></strong>
                    <?php foreach ($cartProblems as $problem): ?><p><?= e($problem) ?></p><?php endforeach; ?>
                    <a href="<?= url('/carrinho') ?>"><?= e(t('checkout.edit_cart')) ?></a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (error('checkout')): ?>
            <div class="alert alert--error"><?= icon('alert', '', 18) ?> <?= e(error('checkout')) ?></div>
        <?php endif; ?>

        <form action="<?= url('/checkout') ?>" method="post" class="checkout-form" data-checkout-form>
            <?= csrf_field() ?>
            <div class="checkout-layout">
                <div class="checkout-main">
                    <?php if (!$user): ?>
                        <div class="checkout-login-note">
                            <?= icon('user', '', 20) ?>
                            <p><?= e(t('checkout.login_for_history')) ?></p>
                            <a class="btn btn--ghost btn--sm" href="<?= url('/login') ?>"><?= e(t('nav.login')) ?></a>
                        </div>
                    <?php endif; ?>

                    <section class="checkout-card">
                        <div class="checkout-card__head">
                            <span class="checkout-card__num">1</span>
                            <div><h2><?= e(t('checkout.contact_info')) ?></h2><p><?= e(t('checkout.email_hint')) ?></p></div>
                        </div>
                        <div class="form-grid">
                            <label class="field">
                                <span><?= e(t('common.email')) ?> *</span>
                                <input type="email" name="email" value="<?= e($val('email', $user['email'] ?? '')) ?>" required autocomplete="email" class="<?= error('email') ? 'is-invalid' : '' ?>">
                                <?php if (error('email')): ?><small class="field__error"><?= e(error('email')) ?></small><?php endif; ?>
                            </label>
                            <label class="field">
                                <span><?= e(t('common.phone')) ?></span>
                                <input type="tel" name="shipping_phone" value="<?= e($val('shipping_phone', $user['phone'] ?? '')) ?>" autocomplete="tel" class="<?= error('shipping_phone') ? 'is-invalid' : '' ?>" placeholder="+351 900 000 000">
                                <?php if (error('shipping_phone')): ?><small class="field__error"><?= e(error('shipping_phone')) ?></small><?php endif; ?>
                            </label>
                        </div>
                    </section>

                    <section class="checkout-card">
                        <div class="checkout-card__head">
                            <span class="checkout-card__num">2</span>
                            <div><h2><?= e(t('checkout.shipping_details')) ?></h2><p><?= e(t('checkout.ship_to')) ?></p></div>
                        </div>

                        <?php if ($addresses): ?>
                            <div class="saved-addresses" data-address-list data-addresses="<?= e(json_encode($addressJson, JSON_UNESCAPED_UNICODE)) ?>">
                                <span class="saved-addresses__label"><?= e(t('checkout.choose_address')) ?></span>
                                <?php foreach ($addresses as $index => $address): ?>
                                    <label class="saved-address <?= $index === 0 ? 'is-selected' : '' ?>">
                                        <input type="radio" name="address_id" value="<?= (int) $address['id'] ?>" <?= $index === 0 ? 'checked' : '' ?> data-address-select>
                                        <span class="saved-address__body">
                                            <span class="saved-address__top"><strong><?= e($address['label']) ?></strong><?php if (!empty($address['is_default_shipping'])): ?><em><?= e(t('account.default_shipping')) ?></em><?php endif; ?></span>
                                            <span><?= e($address['name']) ?> · <?= e($address['address']) ?>, <?= e($address['postal_code']) ?> <?= e($address['city']) ?></span>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                                <label class="saved-address saved-address--new <?= $addresses ? '' : 'is-selected' ?>">
                                    <input type="radio" name="address_id" value="" <?= $addresses ? '' : 'checked' ?> data-address-select data-new-address>
                                    <span class="saved-address__body"><span class="saved-address__top"><strong><?= icon('plus', '', 14) ?> <?= e(t('checkout.new_address')) ?></strong></span></span>
                                </label>
                            </div>
                        <?php endif; ?>

                        <div class="form-grid">
                            <label class="field field--full">
                                <span><?= e(t('common.name')) ?> *</span>
                                <input type="text" name="shipping_name" value="<?= e($val('shipping_name', $user['name'] ?? '')) ?>" required autocomplete="name" class="<?= error('shipping_name') ? 'is-invalid' : '' ?>" data-ship-field="name">
                                <?php if (error('shipping_name')): ?><small class="field__error"><?= e(error('shipping_name')) ?></small><?php endif; ?>
                            </label>
                            <label class="field field--full">
                                <span><?= e(t('common.address')) ?> *</span>
                                <input type="text" name="shipping_address" value="<?= e($val('shipping_address')) ?>" required autocomplete="street-address" class="<?= error('shipping_address') ? 'is-invalid' : '' ?>" data-ship-field="address">
                                <?php if (error('shipping_address')): ?><small class="field__error"><?= e(error('shipping_address')) ?></small><?php endif; ?>
                            </label>
                            <label class="field">
                                <span><?= e(t('common.postal_code')) ?> *</span>
                                <input type="text" name="shipping_postal_code" value="<?= e($val('shipping_postal_code')) ?>" required autocomplete="postal-code" class="<?= error('shipping_postal_code') ? 'is-invalid' : '' ?>" data-ship-field="postal_code">
                            </label>
                            <label class="field">
                                <span><?= e(t('common.city')) ?> *</span>
                                <input type="text" name="shipping_city" value="<?= e($val('shipping_city')) ?>" required autocomplete="address-level2" class="<?= error('shipping_city') ? 'is-invalid' : '' ?>" data-ship-field="city">
                            </label>
                            <label class="field">
                                <span><?= e(t('common.country')) ?> *</span>
                                <select name="shipping_country" autocomplete="country-name">
                                    <option value="Portugal" <?= $val('shipping_country', 'Portugal') === 'Portugal' ? 'selected' : '' ?>>Portugal</option>
                                    <option value="Espanha" <?= $val('shipping_country') === 'Espanha' ? 'selected' : '' ?>>Espanha</option>
                                    <option value="França" <?= $val('shipping_country') === 'França' ? 'selected' : '' ?>>França</option>
                                </select>
                            </label>
                            <label class="field">
                                <span><?= e(t('common.notes')) ?> <em>(<?= e(t('common.optional')) ?>)</em></span>
                                <input type="text" name="notes" value="<?= e(old('notes')) ?>" maxlength="500" placeholder="<?= e(t('checkout.notes_placeholder')) ?>">
                            </label>
                        </div>

                        <?php if ($user): ?>
                            <label class="check-row check-row--bordered">
                                <input type="checkbox" name="save_address" value="1" <?= old('save_address') ? 'checked' : '' ?>>
                                <span class="check-row__box"><?= icon('check', '', 12) ?></span>
                                <span><?= e(t('checkout.save_address')) ?></span>
                            </label>
                        <?php endif; ?>
                    </section>

                    <section class="checkout-card">
                        <div class="checkout-card__head">
                            <span class="checkout-card__num">3</span>
                            <div><h2><?= e(t('checkout.billing_details')) ?></h2></div>
                        </div>
                        <label class="check-row check-row--bordered">
                            <input type="checkbox" name="billing_same" value="1" <?= $sameBilling ? 'checked' : '' ?> data-billing-same>
                            <span class="check-row__box"><?= icon('check', '', 12) ?></span>
                            <span><?= e(t('checkout.same_as_shipping')) ?></span>
                        </label>
                        <div class="billing-fields" data-billing-fields <?= $sameBilling ? 'hidden' : '' ?>>
                            <div class="form-grid">
                                <label class="field field--full"><span><?= e(t('common.name')) ?></span><input type="text" name="billing_name" value="<?= e(old('billing_name', $defaultAddress['name'] ?? '')) ?>"></label>
                                <label class="field field--full"><span><?= e(t('common.address')) ?></span><input type="text" name="billing_address" value="<?= e(old('billing_address', $defaultAddress['address'] ?? '')) ?>"></label>
                                <label class="field"><span><?= e(t('common.postal_code')) ?></span><input type="text" name="billing_postal_code" value="<?= e(old('billing_postal_code', $defaultAddress['postal_code'] ?? '')) ?>"></label>
                                <label class="field"><span><?= e(t('common.city')) ?></span><input type="text" name="billing_city" value="<?= e(old('billing_city', $defaultAddress['city'] ?? '')) ?>"></label>
                            </div>
                        </div>
                    </section>

                    <section class="checkout-card">
                        <div class="checkout-card__head">
                            <span class="checkout-card__num">4</span>
                            <div><h2><?= e(t('checkout.payment_method')) ?></h2><p><?= e(t('checkout.simulation_notice')) ?></p></div>
                        </div>

                        <div class="payment-options" data-payment-options>
                            <label class="payment-option <?= $paymentMethod === 'card' ? 'is-active' : '' ?>">
                                <input type="radio" name="payment_method" value="card" <?= $paymentMethod === 'card' ? 'checked' : '' ?> data-payment-radio>
                                <span class="payment-option__icon"><?= icon('credit-card', '', 22) ?></span>
                                <span class="payment-option__body"><strong><?= e(t('checkout.card')) ?></strong><small><?= e(t('checkout.card_desc')) ?></small></span>
                                <span class="payment-option__check"></span>
                            </label>
                            <label class="payment-option <?= $paymentMethod === 'mbway' ? 'is-active' : '' ?>">
                                <input type="radio" name="payment_method" value="mbway" <?= $paymentMethod === 'mbway' ? 'checked' : '' ?> data-payment-radio>
                                <span class="payment-option__icon"><?= icon('smartphone', '', 22) ?></span>
                                <span class="payment-option__body"><strong><?= e(t('checkout.mbway')) ?></strong><small><?= e(t('checkout.mbway_desc')) ?></small></span>
                                <span class="payment-option__check"></span>
                            </label>
                            <label class="payment-option <?= $paymentMethod === 'multibanco' ? 'is-active' : '' ?>">
                                <input type="radio" name="payment_method" value="multibanco" <?= $paymentMethod === 'multibanco' ? 'checked' : '' ?> data-payment-radio>
                                <span class="payment-option__icon"><?= icon('barcode', '', 22) ?></span>
                                <span class="payment-option__body"><strong><?= e(t('checkout.multibanco')) ?></strong><small><?= e(t('checkout.multibanco_desc')) ?></small></span>
                                <span class="payment-option__check"></span>
                            </label>
                        </div>

                        <div class="payment-panel" data-payment-panel="card" <?= $paymentMethod !== 'card' ? 'hidden' : '' ?>>
                            <div class="payment-panel__notice"><?= icon('info', '', 16) ?> <?= e(t('checkout.card_simulated')) ?></div>
                            <div class="form-grid">
                                <label class="field field--full"><span><?= e(t('checkout.card_number')) ?></span><input type="text" name="card_number" value="4242 4242 4242 4242" inputmode="numeric" autocomplete="cc-number"></label>
                                <label class="field field--full"><span><?= e(t('checkout.card_name')) ?></span><input type="text" name="card_name" value="<?= e($val('shipping_name', $user['name'] ?? '')) ?>" autocomplete="cc-name"></label>
                                <label class="field"><span><?= e(t('checkout.card_expiry')) ?></span><input type="text" name="card_expiry" value="12/30" autocomplete="cc-exp"></label>
                                <label class="field"><span><?= e(t('checkout.card_cvv')) ?></span><input type="text" name="card_cvv" value="123" inputmode="numeric" autocomplete="cc-csc"></label>
                            </div>
                        </div>

                        <div class="payment-panel" data-payment-panel="mbway" <?= $paymentMethod !== 'mbway' ? 'hidden' : '' ?>>
                            <div class="payment-panel__notice"><?= icon('smartphone', '', 16) ?> <?= e(t('checkout.mbway_notice')) ?></div>
                            <p class="payment-panel__text"><?= e(t('checkout.mbway_phone')) ?>: <strong><?= e($val('shipping_phone', $user['phone'] ?? '')) ?: '—' ?></strong></p>
                        </div>

                        <div class="payment-panel" data-payment-panel="multibanco" <?= $paymentMethod !== 'multibanco' ? 'hidden' : '' ?>>
                            <div class="payment-panel__notice"><?= icon('barcode', '', 16) ?> <?= e(t('checkout.multibanco_desc')) ?></div>
                            <p class="payment-panel__text"><?= e(t('checkout.multibanco_after')) ?></p>
                        </div>
                    </section>

                    <div class="checkout-terms">
                        <?= icon('lock', '', 15) ?>
                        <p><?= e(t('checkout.terms_notice')) ?></p>
                    </div>
                </div>

                <aside class="checkout-aside">
                    <div class="summary-card summary-card--sticky">
                        <div class="summary-card__head">
                            <h2><?= icon('package', '', 18) ?> <?= e(t('checkout.summary')) ?></h2>
                            <a href="<?= url('/carrinho') ?>"><?= e(t('checkout.edit_cart')) ?></a>
                        </div>
                        <div class="checkout-items">
                            <?php foreach ($items as $item): ?>
                                <?php $variant = $item['variant']; ?>
                                <div class="checkout-item">
                                    <span class="checkout-item__media">
                                        <img src="<?= asset('img/' . $variant['image']) ?>" alt="" loading="lazy" width="80" height="80">
                                        <em><?= (int) $item['quantity'] ?></em>
                                    </span>
                                    <span class="checkout-item__info">
                                        <strong><?= e(localized($variant, 'name')) ?></strong>
                                        <small><?= e($variant['size']) ?> · <?= e($variant['color']) ?></small>
                                    </span>
                                    <strong><?= money($item['line_total']) ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="summary-card__lines">
                            <div><span><?= e(t('cart.subtotal')) ?></span><strong><?= money($subtotal) ?></strong></div>
                            <div><span><?= e(t('cart.shipping')) ?></span><strong class="<?= $shipping <= 0 ? 'is-free' : '' ?>"><?= $shipping <= 0 ? e(t('cart.free')) : money($shipping) ?></strong></div>
                            <div><span><?= e(t('cart.vat_included')) ?></span><small>—</small></div>
                        </div>
                        <div class="summary-card__total">
                            <span><?= e(t('cart.total')) ?><small><?= e(t('cart.vat_included')) ?></small></span>
                            <strong><?= money($total) ?></strong>
                        </div>
                        <button class="btn btn--primary btn--lg btn--block" type="submit" <?= $cartProblems ? 'disabled' : '' ?>>
                            <?= icon('lock', '', 17) ?> <?= e(t('checkout.place_order')) ?>
                        </button>
                        <p class="summary-card__note"><?= icon('shield-check', '', 14) ?> <?= e(t('checkout.payment_simulated')) ?></p>
                    </div>

                    <div class="checkout-delivery">
                        <span><?= icon('truck', '', 20) ?></span>
                        <div><strong><?= e(t('checkout.delivery_estimate')) ?></strong><p><?= e(t('checkout.delivery_estimate_value')) ?></p></div>
                    </div>
                </aside>
            </div>
        </form>
    </div>
</section>
