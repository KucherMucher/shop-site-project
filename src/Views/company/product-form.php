<?php
/** @var array $company */
/** @var array $categories */
/** @var array|null $product */
/** @var array $variants */
/** @var array $colorImages */
$isEdit = isset($product) && is_array($product);
$pageScripts = ['company.js'];
$productVariants = $isEdit ? ($variants ?? []) : [];

$variantSizes = array_map(static fn (array $variant): string => (string) $variant['size'], $productVariants);
$variantColors = array_map(static fn (array $variant): string => (string) $variant['color'], $productVariants);
$variantStocks = array_map(static fn (array $variant): string => (string) $variant['stock'], $productVariants);
$variantIds = array_map(static fn (array $variant): string => (string) $variant['id'], $productVariants);

$oldSizes = array_values((array) old('variant_size', $variantSizes));
$oldColors = array_values((array) old('variant_color', $variantColors));
$oldStocks = array_values((array) old('variant_stock', $variantStocks));
$oldVariantIds = array_values((array) old('variant_id', $variantIds));

$productSpecs = $isEdit ? ($product['specs'] ?? []) : [];
$oldLabelPt = array_values((array) old('spec_label_pt', array_column($productSpecs, 'label_pt')));
$oldLabelEn = array_values((array) old('spec_label_en', array_column($productSpecs, 'label_en')));
$oldValues = array_values((array) old('spec_value', array_column($productSpecs, 'value')));

$variantRows = max(1, count($oldSizes));
$specRows = max(1, count($oldLabelPt));
$defaultSizes = ['XS', 'S', 'M', 'L', 'XL'];

$mainImage = $isEdit ? (string) ($product['image'] ?? '') : '';
$mainImageUrl = $mainImage !== '' ? asset('img/' . $mainImage) : '';
$existingGallery = $isEdit ? array_values(array_filter((array) ($product['gallery'] ?? []), 'is_string')) : [];
$oldGallery = array_values((array) old('existing_gallery', $existingGallery));

$colorMap = $isEdit ? ($colorImages ?? []) : [];
$oldColorRows = old('color_image_color', null);
if (is_array($oldColorRows)) {
    $colorRows = $oldColorRows;
} else {
    $colorRows = [];
    foreach ($oldColors as $color) {
        $color = trim((string) $color);
        if ($color !== '' && !in_array($color, $colorRows, true)) {
            $colorRows[] = $color;
        }
    }
    foreach (array_keys($colorMap) as $color) {
        if (!in_array($color, $colorRows, true)) {
            $colorRows[] = (string) $color;
        }
    }
}

$existingColorInput = (array) old('existing_color_image', []);
if (!is_array($oldColorRows)) {
    foreach ($colorRows as $key => $color) {
        $existingColorInput[$key] = $colorMap[$color] ?? [];
    }
}

$val = static fn (string $key, mixed $default = ''): mixed => old($key, $default);
$status = $isEdit ? (string) ($product['status'] ?? 'pending') : 'pending';
$statusClass = match ($status) {
    'active' => 'status-green',
    'rejected' => 'status-red',
    default => 'status-amber',
};
$statusIcon = match ($status) {
    'active' => 'check-circle',
    'rejected' => 'alert',
    default => 'clock',
};
?>
<div class="company-section-head">
    <div>
        <a class="back-link" href="<?= url('/empresa/produtos') ?>"><?= icon('arrow-left', '', 14) ?> <?= e(t('company.products')) ?></a>
        <h2><?= e($isEdit ? t('company.edit_product') : t('company.new_product')) ?></h2>
        <p><?= e($isEdit ? t('company.edit_product_intro') : t('company.product_form_intro')) ?></p>
    </div>
    <span class="status-pill <?= $statusClass ?>"><?= icon($statusIcon, '', 14) ?> <?= e(t('company.proposal_' . $status)) ?></span>
</div>

<?php if (error('form') || error('variants') || error('image') || error('gallery') || error('color_images')): ?>
    <div class="alert alert--error">
        <?= icon('alert', '', 17) ?>
        <?= e(error('form') ?: (error('variants') ?: (error('image') ?: (error('gallery') ?: error('color_images'))))) ?>
    </div>
<?php endif; ?>

<form action="<?= $isEdit ? url('/empresa/produtos/' . $product['id']) : url('/empresa/produtos') ?>" method="post" enctype="multipart/form-data" class="company-form" data-product-form>
    <?= csrf_field() ?>

    <div class="company-form__layout">
        <div class="company-form__main">
            <section class="content-card">
                <div class="content-card__head">
                    <span class="eyebrow"><?= icon('file', '', 13) ?> 01</span>
                    <h2><?= e(t('product.description')) ?></h2>
                </div>
                <div class="form-grid">
                    <label class="field">
                        <span><?= e(t('company.name_pt')) ?> *</span>
                        <input type="text" name="name_pt" value="<?= e($val('name_pt', $product['name_pt'] ?? '')) ?>" required maxlength="150" class="<?= error('name_pt') ? 'is-invalid' : '' ?>" data-live-slug>
                        <?php if (error('name_pt')): ?><small class="field__error"><?= e(error('name_pt')) ?></small><?php endif; ?>
                    </label>
                    <label class="field">
                        <span><?= e(t('company.name_en')) ?></span>
                        <input type="text" name="name_en" value="<?= e($val('name_en', $product['name_en'] ?? '')) ?>" maxlength="150">
                        <small class="field__hint"><?= e(t('common.optional')) ?> — <?= e(t('common.cancel')) ?>: <?= e(t('company.name_pt')) ?></small>
                    </label>
                    <label class="field">
                        <span><?= e(t('company.category')) ?> *</span>
                        <select name="category_id" required>
                            <option value="">—</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>" <?= (int) $val('category_id', $product['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>><?= e(localized($category, 'name')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="field">
                        <span><?= e(t('company.brand')) ?></span>
                        <input type="text" name="brand" value="<?= e($val('brand', $product['brand'] ?? $company['name'])) ?>" maxlength="80">
                    </label>
                    <label class="field field--full">
                        <span><?= e(t('company.short_pt')) ?></span>
                        <input type="text" name="short_pt" value="<?= e($val('short_pt', $product['short_pt'] ?? '')) ?>" maxlength="180" placeholder="<?= e(t('common.optional')) ?>">
                    </label>
                    <label class="field field--full">
                        <span><?= e(t('company.short_en')) ?></span>
                        <input type="text" name="short_en" value="<?= e($val('short_en', $product['short_en'] ?? '')) ?>" maxlength="180" placeholder="<?= e(t('common.optional')) ?>">
                    </label>
                    <label class="field field--full">
                        <span><?= e(t('company.description_pt')) ?></span>
                        <textarea name="description_pt" rows="6" maxlength="4000"><?= e($val('description_pt', $product['description_pt'] ?? '')) ?></textarea>
                    </label>
                    <label class="field field--full">
                        <span><?= e(t('company.description_en')) ?></span>
                        <textarea name="description_en" rows="4" maxlength="4000"><?= e($val('description_en', $product['description_en'] ?? '')) ?></textarea>
                    </label>
                </div>
            </section>

            <section class="content-card">
                <div class="content-card__head content-card__head--row">
                    <div>
                        <span class="eyebrow"><?= icon('box', '', 13) ?> 02</span>
                        <h2><?= e(t('company.variants')) ?></h2>
                    </div>
                    <button type="button" class="btn btn--ghost btn--sm" data-add-variant><?= icon('plus', '', 15) ?> <?= e(t('company.add_variant')) ?></button>
                </div>

                <div class="repeater" data-variants>
                    <?php for ($i = 0; $i < $variantRows; $i++): ?>
                        <div class="repeater__row repeater__row--variant" data-repeater-row>
                            <input type="hidden" name="variant_id[]" value="<?= e($oldVariantIds[$i] ?? '') ?>">
                            <span class="repeater__index"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                            <label class="field">
                                <span><?= e(t('company.variant_size')) ?> *</span>
                                <input type="text" name="variant_size[]" value="<?= e($oldSizes[$i] ?? '') ?>" list="size-options" required maxlength="20">
                            </label>
                            <label class="field">
                                <span><?= e(t('company.variant_color')) ?></span>
                                <input type="text" name="variant_color[]" value="<?= e($oldColors[$i] ?? '') ?>" maxlength="40" placeholder="Preto">
                            </label>
                            <label class="field">
                                <span><?= e(t('company.variant_stock')) ?></span>
                                <input type="number" name="variant_stock[]" value="<?= e($oldStocks[$i] ?? '0') ?>" min="0" max="9999">
                            </label>
                            <button type="button" class="icon-btn repeater__remove" data-remove-row aria-label="<?= e(t('common.delete')) ?>"><?= icon('trash', '', 16) ?></button>
                        </div>
                    <?php endfor; ?>
                </div>
                <datalist id="size-options">
                    <?php foreach ($defaultSizes as $size): ?><option value="<?= e($size) ?>"></option><?php endforeach; ?>
                    <option value="36"></option><option value="37"></option><option value="38"></option><option value="39"></option><option value="40"></option><option value="41"></option><option value="42"></option><option value="43"></option><option value="44"></option><option value="45"></option><option value="Único"></option>
                </datalist>

                <template data-variant-template>
                    <div class="repeater__row repeater__row--variant" data-repeater-row>
                        <input type="hidden" name="variant_id[]" value="">
                        <span class="repeater__index">00</span>
                        <label class="field"><span><?= e(t('company.variant_size')) ?> *</span><input type="text" name="variant_size[]" list="size-options" required maxlength="20"></label>
                        <label class="field"><span><?= e(t('company.variant_color')) ?></span><input type="text" name="variant_color[]" maxlength="40" placeholder="Preto"></label>
                        <label class="field"><span><?= e(t('company.variant_stock')) ?></span><input type="number" name="variant_stock[]" value="0" min="0" max="9999"></label>
                        <button type="button" class="icon-btn repeater__remove" data-remove-row aria-label="<?= e(t('common.delete')) ?>"><?= icon('trash', '', 16) ?></button>
                    </div>
                </template>
            </section>

            <section class="content-card">
                <div class="content-card__head content-card__head--row">
                    <div>
                        <span class="eyebrow"><?= icon('list', '', 13) ?> 03</span>
                        <h2><?= e(t('company.specs')) ?></h2>
                    </div>
                    <button type="button" class="btn btn--ghost btn--sm" data-add-spec><?= icon('plus', '', 15) ?> <?= e(t('company.specs')) ?></button>
                </div>

                <div class="repeater" data-specs>
                    <?php for ($i = 0; $i < $specRows; $i++): ?>
                        <div class="repeater__row repeater__row--spec" data-repeater-row>
                            <label class="field"><span><?= e(t('company.name_pt')) ?></span><input type="text" name="spec_label_pt[]" value="<?= e($oldLabelPt[$i] ?? '') ?>" maxlength="60" placeholder="Material"></label>
                            <label class="field"><span><?= e(t('company.name_en')) ?></span><input type="text" name="spec_label_en[]" value="<?= e($oldLabelEn[$i] ?? '') ?>" maxlength="60" placeholder="Material"></label>
                            <label class="field"><span><?= e(t('admin.value')) ?></span><input type="text" name="spec_value[]" value="<?= e($oldValues[$i] ?? '') ?>" maxlength="120" placeholder="Poliéster reciclado"></label>
                            <button type="button" class="icon-btn repeater__remove" data-remove-row aria-label="<?= e(t('common.delete')) ?>"><?= icon('trash', '', 16) ?></button>
                        </div>
                    <?php endfor; ?>
                </div>

                <template data-spec-template>
                    <div class="repeater__row repeater__row--spec" data-repeater-row>
                        <label class="field"><span><?= e(t('company.name_pt')) ?></span><input type="text" name="spec_label_pt[]" maxlength="60" placeholder="Material"></label>
                        <label class="field"><span><?= e(t('company.name_en')) ?></span><input type="text" name="spec_label_en[]" maxlength="60" placeholder="Material"></label>
                        <label class="field"><span><?= e(t('admin.value')) ?></span><input type="text" name="spec_value[]" maxlength="120" placeholder="Poliéster reciclado"></label>
                        <button type="button" class="icon-btn repeater__remove" data-remove-row aria-label="<?= e(t('common.delete')) ?>"><?= icon('trash', '', 16) ?></button>
                    </div>
                </template>
            </section>

            <section class="content-card">
                <div class="content-card__head content-card__head--row">
                    <div>
                        <span class="eyebrow"><?= icon('image', '', 13) ?> 04</span>
                        <h2><?= e(t('company.color_photos')) ?></h2>
                    </div>
                    <span class="color-photos__count" data-color-photos-count><?= count($colorRows) ?></span>
                </div>
                <p class="field__hint"><?= e(t('company.color_photos_hint')) ?></p>
                <div class="color-photos" data-color-photos>
                    <?php foreach ($colorRows as $key => $color): ?>
                        <?php $images = (array) ($existingColorInput[$key] ?? []); ?>
                        <?php require BASE_PATH . '/src/Views/company/_color-photo.php'; ?>
                    <?php endforeach; ?>
                </div>
                <p class="field__hint color-photos__empty" data-color-photos-empty <?= $colorRows ? 'hidden' : '' ?>><?= e(t('company.color_photos_empty')) ?></p>
                <template data-color-photo-template>
                    <?php $key = '__KEY__'; $color = ''; $images = []; ?>
                    <?php require BASE_PATH . '/src/Views/company/_color-photo.php'; ?>
                </template>
            </section>
        </div>

        <aside class="company-form__aside">
            <section class="content-card">
                <div class="content-card__head">
                    <span class="eyebrow"><?= icon('credit-card', '', 13) ?> 05</span>
                    <h2><?= e(t('common.price')) ?></h2>
                </div>
                <div class="form-stack">
                    <label class="field">
                        <span><?= e(t('company.base_price')) ?> *</span>
                        <span class="field__control">
                            <?= icon('tag', '', 16) ?>
                            <input type="number" name="base_price" value="<?= e($val('base_price', $product['base_price'] ?? '')) ?>" min="0.01" step="0.01" required placeholder="0,00" class="<?= error('base_price') ? 'is-invalid' : '' ?>">
                        </span>
                        <?php if (error('base_price')): ?><small class="field__error"><?= e(error('base_price')) ?></small><?php endif; ?>
                    </label>
                    <label class="field">
                        <span><?= e(t('company.sale_price')) ?></span>
                        <span class="field__control">
                            <?= icon('sparkles', '', 16) ?>
                            <input type="number" name="sale_price" value="<?= e($val('sale_price', $product['sale_price'] ?? '')) ?>" min="0.01" step="0.01" placeholder="<?= e(t('common.optional')) ?>">
                        </span>
                    </label>
                    <p class="field__hint"><?= e(t('product.tax_note')) ?></p>
                </div>
            </section>

            <section class="content-card">
                <div class="content-card__head">
                    <span class="eyebrow"><?= icon('image', '', 13) ?> 06</span>
                    <h2><?= e(t('company.image')) ?></h2>
                </div>
                <label class="upload <?= $mainImageUrl !== '' ? 'has-preview' : '' ?>" data-upload data-existing="<?= e($mainImageUrl) ?>">
                    <span class="upload__placeholder" data-upload-placeholder>
                        <?= icon('image', '', 30) ?>
                        <strong><?= e(t('company.image')) ?></strong>
                        <small><?= e(t('company.image_hint')) ?></small>
                    </span>
                    <span class="upload__preview" data-upload-preview <?= $mainImageUrl !== '' ? 'style="background-image:url(\'' . e($mainImageUrl) . '\')"' : 'hidden' ?>></span>
                    <span class="upload__change" data-upload-change <?= $mainImageUrl !== '' ? '' : 'hidden' ?>><?= icon('refresh', '', 13) ?> <?= e(t('company.change_image')) ?></span>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-upload-input>
                </label>
                <p class="field__hint"><?= e(t('company.image_note')) ?></p>
                <?php if (error('image')): ?><small class="field__error"><?= e(error('image')) ?></small><?php endif; ?>
            </section>

            <section class="content-card">
                <div class="content-card__head">
                    <span class="eyebrow"><?= icon('image', '', 13) ?> 07</span>
                    <h2><?= e(t('company.gallery')) ?></h2>
                </div>
                <label class="upload upload--gallery" data-gallery-upload>
                    <span class="upload__placeholder">
                        <?= icon('plus', '', 24) ?>
                        <strong><?= e(t('company.add_gallery')) ?></strong>
                        <small><?= e(t('company.gallery_hint')) ?></small>
                    </span>
                    <input type="file" name="gallery_images[]" accept="image/jpeg,image/png,image/webp" multiple data-gallery-input>
                </label>
                <div class="gallery-previews" data-gallery-previews>
                    <?php foreach ($oldGallery as $image): ?>
                        <span class="gallery-preview" data-existing-gallery>
                            <img src="<?= asset('img/' . $image) ?>" alt="" loading="lazy">
                            <input type="hidden" name="existing_gallery[]" value="<?= e($image) ?>">
                            <button type="button" class="gallery-preview__remove" data-gallery-remove aria-label="<?= e(t('common.delete')) ?>"><?= icon('close', '', 12) ?></button>
                        </span>
                    <?php endforeach; ?>
                </div>
                <?php if (error('gallery')): ?><small class="field__error"><?= e(error('gallery')) ?></small><?php endif; ?>
            </section>

            <section class="content-card content-card--submit">
                <div class="alert alert--info"><?= icon('info', '', 16) ?> <?= e(t('company.product_form_intro')) ?></div>
                <button class="btn btn--primary btn--block" type="submit"><?= icon('sparkles', '', 16) ?> <?= e($isEdit ? t('company.save_changes') : t('company.submit_proposal')) ?></button>
                <a class="btn btn--ghost btn--block" href="<?= url('/empresa/produtos') ?>"><?= e(t('common.cancel')) ?></a>
            </section>
        </aside>
    </div>
</form>
