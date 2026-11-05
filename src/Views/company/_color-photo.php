<?php
/** @var string|int $key */
/** @var string $color */
/** @var array $images */
$key = (string) $key;
$color = (string) $color;
$images = is_array($images) ? $images : [];
?>
<div class="color-photo" data-color-photo data-color-value="<?= e($color) ?>">
    <div class="color-photo__head">
        <span class="color-photo__dot" aria-hidden="true"></span>
        <strong data-color-name><?= e($color !== '' ? $color : '—') ?></strong>
    </div>
    <input type="hidden" name="color_image_color[<?= e($key) ?>]" value="<?= e($color) ?>" data-color-input>
    <div class="color-photo__grid" data-color-existing>
        <?php foreach ($images as $image): ?>
            <span class="color-photo__thumb" data-existing-color-image>
                <img src="<?= asset('img/' . $image) ?>" alt="" loading="lazy">
                <input type="hidden" name="existing_color_image[<?= e($key) ?>][]" value="<?= e($image) ?>">
                <button type="button" class="color-photo__thumb-remove" data-color-image-remove aria-label="<?= e(t('common.delete')) ?>"><?= icon('close', '', 11) ?></button>
            </span>
        <?php endforeach; ?>
    </div>
    <div class="color-photo__grid color-photo__grid--new" data-color-new></div>
    <label class="upload upload--compact">
        <?= icon('image', '', 19) ?>
        <span class="upload__compact-text">
            <strong><?= e(t('company.add_color_photo')) ?></strong>
            <small><?= e(t('company.image_hint')) ?></small>
        </span>
        <input type="file" name="color_image_file[<?= e($key) ?>][]" accept="image/jpeg,image/png,image/webp" multiple data-color-file>
    </label>
</div>
