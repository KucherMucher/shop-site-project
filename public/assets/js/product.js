/**
 * TRIVEX — product detail page.
 * Handles gallery, variant availability, live price/stock and quantity limits.
 */
(function () {
    'use strict';

    var page = document.querySelector('[data-product-page]');
    if (!page) return;

    var variants = [];
    try {
        variants = JSON.parse(page.getAttribute('data-variants') || '[]');
    } catch (e) {
        variants = [];
    }
    if (!Array.isArray(variants) || !variants.length) return;

    var lang = (document.documentElement.getAttribute('lang') || 'pt').toLowerCase().indexOf('en') === 0 ? 'en' : 'pt';
    var copy = {
        pt: {
            inStock: 'Em stock: {count} unidades',
            low: 'Últimas {count} unidades',
            out: 'Esgotado',
            add: 'Adicionar ao carrinho'
        },
        en: {
            inStock: 'In stock: {count} units',
            low: 'Only {count} left',
            out: 'Out of stock',
            add: 'Add to cart'
        }
    }[lang];

    var priceEl = page.querySelector('[data-product-price]');
    var stockEl = page.querySelector('[data-stock-display]');
    var skuEl = page.querySelector('[data-variant-sku]');
    var variantInput = page.querySelector('[data-variant-input]');
    var colorLabel = page.querySelector('[data-color-label]');
    var sizeLabel = page.querySelector('[data-size-label]');
    var addButton = page.querySelector('[data-add-button]');
    var addLabel = page.querySelector('[data-add-label]');
    var qtyInput = page.querySelector('[data-qty] input[name="quantity"]');
    var form = page.querySelector('[data-product-form]');
    var colorButtons = Array.prototype.slice.call(page.querySelectorAll('[data-color]'));
    var sizeButtons = Array.prototype.slice.call(page.querySelectorAll('[data-size]'));

    var state = {
        color: null,
        size: null
    };

    var activeColor = page.querySelector('.variant-color.is-active');
    var activeSize = page.querySelector('.variant-size.is-active');
    state.color = activeColor ? activeColor.getAttribute('data-color') : variants[0].color;
    state.size = activeSize ? activeSize.getAttribute('data-size') : variants[0].size;

    function byColor(color) {
        return variants.filter(function (variant) { return variant.color === color; });
    }

    function findVariant(preferStock) {
        var exact = null;
        var sameColor = null;
        var sameSize = null;

        variants.forEach(function (variant) {
            if (variant.color === state.color && variant.size === state.size) {
                if (!exact || (preferStock && variant.stock > 0 && exact.stock <= 0)) exact = variant;
            }
            if (variant.color === state.color && variant.stock > 0 && !sameColor) sameColor = variant;
            if (variant.size === state.size && variant.stock > 0 && !sameSize) sameSize = variant;
        });

        if (exact && (exact.stock > 0 || !preferStock)) return exact;
        if (preferStock) return sameColor || sameSize || exact || variants[0];
        return exact || sameColor || sameSize || variants[0];
    }

    function paintStock(stock) {
        if (!stockEl) return;
        stockEl.textContent = '';
        var dot = document.createElement('span');
        dot.className = 'stock-dot ' + (stock <= 0 ? 'is-out' : (stock <= 5 ? 'is-low' : 'is-in'));
        stockEl.appendChild(dot);
        var label = stock <= 0
            ? copy.out
            : (stock <= 5 ? copy.low : copy.inStock).replace('{count}', String(stock));
        stockEl.appendChild(document.createTextNode(label));
        stockEl.classList.toggle('is-out', stock <= 0);
        stockEl.classList.toggle('is-low', stock > 0 && stock <= 5);
        stockEl.classList.toggle('is-in', stock > 5);
    }

    function paintAvailability() {
        sizeButtons.forEach(function (button) {
            var size = button.getAttribute('data-size');
            var available = variants.some(function (variant) {
                return variant.color === state.color && variant.size === size && variant.stock > 0;
            });
            button.classList.toggle('is-disabled', !available);
            button.setAttribute('aria-disabled', available ? 'false' : 'true');
        });

        colorButtons.forEach(function (button) {
            var color = button.getAttribute('data-color');
            var available = variants.some(function (variant) {
                return variant.color === color && variant.stock > 0;
            });
            button.classList.toggle('is-dimmed', !available);
        });
    }

    function applyVariant(preferStock) {
        var variant = findVariant(preferStock);
        if (!variant) return;
        state.color = variant.color;
        state.size = variant.size;

        if (variantInput) variantInput.value = String(variant.id);
        if (priceEl && variant.price_label) priceEl.textContent = variant.price_label;
        if (skuEl) skuEl.textContent = variant.sku || '—';
        if (colorLabel) colorLabel.textContent = variant.color;
        if (sizeLabel) sizeLabel.textContent = variant.size;

        colorButtons.forEach(function (button) {
            button.classList.toggle('is-active', button.getAttribute('data-color') === variant.color);
        });
        sizeButtons.forEach(function (button) {
            button.classList.toggle('is-active', button.getAttribute('data-size') === variant.size);
        });

        paintStock(variant.stock);
        paintAvailability();
        paintGallery(variant.images || []);

        if (qtyInput) {
            var max = Math.max(1, Math.min(10, variant.stock || 1));
            qtyInput.setAttribute('max', String(max));
            if (parseInt(qtyInput.value, 10) > max) qtyInput.value = String(max);
        }

        if (addButton) addButton.disabled = variant.stock <= 0;
        if (addLabel) addLabel.textContent = variant.stock <= 0 ? copy.out : copy.add;
    }

    colorButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            state.color = button.getAttribute('data-color');
            applyVariant(true);
        });
    });

    sizeButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (button.classList.contains('is-disabled')) return;
            state.size = button.getAttribute('data-size');
            applyVariant(true);
        });
    });

    if (form) {
        form.addEventListener('submit', function (event) {
            var variant = findVariant(false);
            if (!variant || variant.stock <= 0) {
                event.preventDefault();
                if (addButton) addButton.disabled = true;
                return;
            }
            if (variantInput) variantInput.value = String(variant.id);
        });
    }

    /* Gallery ---------------------------------------------------------- */
    var mainImage = page.querySelector('#product-main-image');
    var thumbsWrap = page.querySelector('.product-gallery__thumbs');
    var zoomButton = page.querySelector('[data-gallery-zoom]');
    var defaultGallery = Array.prototype.slice.call(page.querySelectorAll('[data-gallery-thumb]')).map(function (thumb) {
        return thumb.getAttribute('data-src');
    }).filter(Boolean);

    function resetZoom() {
        if (mainImage) mainImage.classList.remove('is-zoomed');
        if (zoomButton) zoomButton.setAttribute('aria-pressed', 'false');
    }

    function paintGallery(colorImages) {
        if (!mainImage || !thumbsWrap) return;
        var list = defaultGallery.slice();
        if (colorImages && colorImages.length) {
            list = colorImages.concat(defaultGallery.filter(function (source) {
                return colorImages.indexOf(source) === -1;
            }));
        }

        var unique = [];
        list.forEach(function (source) {
            if (source && unique.indexOf(source) === -1) unique.push(source);
        });
        if (!unique.length) return;

        mainImage.setAttribute('src', unique[0]);
        thumbsWrap.textContent = '';
        unique.forEach(function (source, index) {
            var thumb = document.createElement('button');
            thumb.type = 'button';
            thumb.className = 'product-gallery__thumb' + (index === 0 ? ' is-active' : '');
            thumb.setAttribute('data-gallery-thumb', '');
            thumb.setAttribute('data-src', source);
            thumb.setAttribute('aria-label', (index + 1).toString());

            var image = document.createElement('img');
            image.src = source;
            image.alt = '';
            image.loading = 'lazy';
            image.width = 160;
            image.height = 160;
            thumb.appendChild(image);
            thumbsWrap.appendChild(thumb);
        });
        resetZoom();
    }

    if (thumbsWrap) {
        thumbsWrap.addEventListener('click', function (event) {
            var thumb = event.target.closest('[data-gallery-thumb]');
            if (!thumb) return;
            var source = thumb.getAttribute('data-src');
            if (!mainImage || !source) return;
            mainImage.setAttribute('src', source);
            Array.prototype.slice.call(thumbsWrap.querySelectorAll('[data-gallery-thumb]')).forEach(function (item) {
                item.classList.remove('is-active');
            });
            thumb.classList.add('is-active');
            resetZoom();
        });
    }

    if (mainImage) {
        mainImage.addEventListener('click', function () {
            mainImage.classList.toggle('is-zoomed');
            if (zoomButton) zoomButton.setAttribute('aria-pressed', mainImage.classList.contains('is-zoomed') ? 'true' : 'false');
        });
    }

    if (zoomButton && mainImage) {
        zoomButton.addEventListener('click', function () {
            mainImage.classList.toggle('is-zoomed');
            zoomButton.setAttribute('aria-pressed', mainImage.classList.contains('is-zoomed') ? 'true' : 'false');
        });
    }

    applyVariant(false);
})();
