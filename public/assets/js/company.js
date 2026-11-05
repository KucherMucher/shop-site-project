/**
 * TRIVEX — company product form.
 * Repeaters, image previews, gallery uploads and colour-specific photos.
 */
(function () {
    'use strict';

    var form = document.querySelector('[data-product-form]');
    if (!form) return;

    function all(selector, scope) {
        return Array.prototype.slice.call((scope || form).querySelectorAll(selector));
    }

    function closest(el, selector) {
        return el && el.closest ? el.closest(selector) : null;
    }

    function syncFileInput(input, files) {
        if (!input) return;
        try {
            var transfer = new DataTransfer();
            files.forEach(function (file) { transfer.items.add(file); });
            input.files = transfer.files;
        } catch (e) {
            /* Older browsers keep the original input files. */
        }
    }

    /* ------------------------------------------------------------------
     * Repeaters
     * ---------------------------------------------------------------- */
    var variants = form.querySelector('[data-variants]');
    var variantTemplate = form.querySelector('[data-variant-template]');
    var addVariant = form.querySelector('[data-add-variant]');
    var specs = form.querySelector('[data-specs]');
    var specTemplate = form.querySelector('[data-spec-template]');
    var addSpec = form.querySelector('[data-add-spec]');

    function refreshVariantIndexes() {
        if (!variants) return;
        all('[data-repeater-row]', variants).forEach(function (row, index) {
            var label = row.querySelector('.repeater__index');
            if (label) label.textContent = String(index + 1).padStart(2, '0');
        });
    }

    function addRepeaterRow(container, template, reset) {
        if (!container || !template) return null;
        var row = template.content.firstElementChild.cloneNode(true);
        if (reset) {
            all('input', row).forEach(function (input) {
                if (input.type === 'hidden' || input.type === 'text' || input.type === 'number') {
                    input.value = input.type === 'number' ? '0' : '';
                }
            });
        }
        container.appendChild(row);
        refreshVariantIndexes();
        syncColorRows();
        var focusable = row.querySelector('input');
        if (focusable) focusable.focus({ preventScroll: true });
        return row;
    }

    if (addVariant && variants && variantTemplate) {
        addVariant.addEventListener('click', function () {
            addRepeaterRow(variants, variantTemplate, true);
        });
    }

    if (addSpec && specs && specTemplate) {
        addSpec.addEventListener('click', function () {
            addRepeaterRow(specs, specTemplate, true);
        });
    }

    form.addEventListener('click', function (event) {
        var remove = closest(event.target, '[data-remove-row]');
        if (!remove) return;
        var row = closest(remove, '[data-repeater-row]');
        if (!row) return;
        var parent = row.parentElement;
        row.remove();
        if (parent === variants && variants && !all('[data-repeater-row]', variants).length) {
            addRepeaterRow(variants, variantTemplate, true);
        }
        refreshVariantIndexes();
        syncColorRows();
    });

    /* ------------------------------------------------------------------
     * Main image preview
     * ---------------------------------------------------------------- */
    var upload = form.querySelector('[data-upload]');
    if (upload) {
        var uploadInput = upload.querySelector('[data-upload-input]');
        var uploadPreview = upload.querySelector('[data-upload-preview]');
        var uploadChange = upload.querySelector('[data-upload-change]');
        var existingMain = upload.getAttribute('data-existing') || '';

        var showMain = function (src) {
            if (!uploadPreview) return;
            if (!src) {
                uploadPreview.hidden = true;
                uploadPreview.style.backgroundImage = '';
                upload.classList.remove('has-preview');
                if (uploadChange) uploadChange.hidden = true;
                return;
            }
            uploadPreview.style.backgroundImage = 'url(' + JSON.stringify(src) + ')';
            uploadPreview.hidden = false;
            upload.classList.add('has-preview');
            if (uploadChange) uploadChange.hidden = false;
        };

        showMain(existingMain);

        if (uploadInput) {
            uploadInput.addEventListener('change', function () {
                var file = uploadInput.files && uploadInput.files[0];
                showMain(file ? URL.createObjectURL(file) : existingMain);
            });
        }
    }

    /* ------------------------------------------------------------------
     * Gallery uploads
     * ---------------------------------------------------------------- */
    var galleryInput = form.querySelector('[data-gallery-input]');
    var galleryPreviews = form.querySelector('[data-gallery-previews]');
    var galleryFiles = [];

    function renderGalleryPreviews() {
        if (!galleryPreviews) return;
        all('.gallery-preview.is-new', galleryPreviews).forEach(function (item) { item.remove(); });
        galleryFiles.forEach(function (file, index) {
            var thumb = document.createElement('span');
            thumb.className = 'gallery-preview is-new';

            var image = document.createElement('img');
            image.src = URL.createObjectURL(file);
            image.alt = '';
            thumb.appendChild(image);

            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'gallery-preview__remove';
            remove.setAttribute('data-gallery-new-remove', '');
            remove.setAttribute('data-index', String(index));
            remove.setAttribute('aria-label', 'Remove');
            remove.innerHTML = '&times;';
            thumb.appendChild(remove);

            galleryPreviews.appendChild(thumb);
        });
    }

    if (galleryInput) {
        galleryInput.addEventListener('change', function () {
            galleryFiles = Array.prototype.slice.call(galleryInput.files || []);
            renderGalleryPreviews();
        });
    }

    form.addEventListener('click', function (event) {
        var removeExisting = closest(event.target, '[data-gallery-remove]');
        if (removeExisting) {
            var existing = closest(removeExisting, '[data-existing-gallery]');
            if (existing) existing.remove();
            return;
        }

        var removeNew = closest(event.target, '[data-gallery-new-remove]');
        if (removeNew) {
            var index = parseInt(removeNew.getAttribute('data-index'), 10);
            if (!isNaN(index)) {
                galleryFiles.splice(index, 1);
                syncFileInput(galleryInput, galleryFiles);
                renderGalleryPreviews();
            }
        }
    });

    /* ------------------------------------------------------------------
     * Colour photos
     * ---------------------------------------------------------------- */
    var colorWrap = form.querySelector('[data-color-photos]');
    var colorTemplate = form.querySelector('[data-color-photo-template]');
    var colorEmpty = form.querySelector('[data-color-photos-empty]');
    var colorCount = form.querySelector('[data-color-photos-count]');
    var colorKey = 0;

    function variantColorInputs() {
        return variants ? all('input[name="variant_color[]"]', variants) : [];
    }

    function uniqueColors() {
        var seen = {};
        var colors = [];
        variantColorInputs().forEach(function (input) {
            var color = (input.value || '').trim();
            if (color === '') return;
            var key = color.toLowerCase();
            if (seen[key]) return;
            seen[key] = true;
            colors.push(color);
        });
        return colors;
    }

    function findColorRow(color) {
        if (!colorWrap) return null;
        var key = color.toLowerCase();
        return all('[data-color-photo]', colorWrap).filter(function (row) {
            return (row.getAttribute('data-color-value') || '').toLowerCase() === key;
        })[0] || null;
    }

    function createColorRow(color) {
        if (!colorWrap || !colorTemplate) return null;
        var key = 'c' + (++colorKey);
        var holder = document.createElement('div');
        holder.innerHTML = colorTemplate.innerHTML.replace(/__KEY__/g, key).trim();
        var row = holder.firstElementChild;
        if (!row) return null;
        colorWrap.appendChild(row);
        return row;
    }

    function updateColorRow(row, color) {
        if (!row) return;
        row.setAttribute('data-color-value', color);
        var name = row.querySelector('[data-color-name]');
        if (name) name.textContent = color;
        var hidden = row.querySelector('[data-color-input]');
        if (hidden) hidden.value = color;
        row._files = row._files || [];
        renderColorNewPreviews(row);
    }

    function syncColorRows() {
        if (!colorWrap) return;
        var colors = uniqueColors();
        var wanted = {};
        var used = [];

        colors.forEach(function (color) {
            var key = color.toLowerCase();
            wanted[key] = true;
            var row = findColorRow(color);
            if (row && used.indexOf(row) !== -1) row = null;
            if (!row) row = createColorRow(color);
            if (!row) return;
            used.push(row);
            updateColorRow(row, color);
        });

        all('[data-color-photo]', colorWrap).forEach(function (row) {
            if (used.indexOf(row) === -1) row.remove();
        });

        if (colorEmpty) colorEmpty.hidden = colors.length > 0;
        if (colorCount) colorCount.textContent = String(colors.length);
    }

    function renderColorNewPreviews(row) {
        var target = row.querySelector('[data-color-new]');
        if (!target) return;
        target.textContent = '';
        var files = row._files || [];
        files.forEach(function (file, index) {
            var thumb = document.createElement('span');
            thumb.className = 'color-photo__thumb is-new';

            var image = document.createElement('img');
            image.src = URL.createObjectURL(file);
            image.alt = '';
            thumb.appendChild(image);

            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'color-photo__thumb-remove';
            remove.setAttribute('data-color-new-remove', '');
            remove.setAttribute('data-index', String(index));
            remove.setAttribute('aria-label', 'Remove');
            remove.innerHTML = '&times;';
            thumb.appendChild(remove);

            target.appendChild(thumb);
        });
    }

    if (colorWrap) {
        all('[data-color-photo]', colorWrap).forEach(function (row) {
            row._files = [];
            renderColorNewPreviews(row);
        });

        form.addEventListener('change', function (event) {
            var input = closest(event.target, '[data-color-file]');
            if (!input) return;
            var row = closest(input, '[data-color-photo]');
            if (!row) return;
            row._files = Array.prototype.slice.call(input.files || []);
            renderColorNewPreviews(row);
        });

        form.addEventListener('input', function (event) {
            if (event.target && event.target.matches('input[name="variant_color[]"]')) {
                syncColorRows();
            }
        });
        form.addEventListener('change', function (event) {
            if (event.target && event.target.matches('input[name="variant_color[]"]')) {
                syncColorRows();
            }
        });

        form.addEventListener('click', function (event) {
            var removeExisting = closest(event.target, '[data-color-image-remove]');
            if (removeExisting) {
                var existing = closest(removeExisting, '[data-existing-color-image]');
                if (existing) existing.remove();
                return;
            }

            var removeNew = closest(event.target, '[data-color-new-remove]');
            if (removeNew) {
                var row = closest(removeNew, '[data-color-photo]');
                var input = row ? row.querySelector('[data-color-file]') : null;
                var index = parseInt(removeNew.getAttribute('data-index'), 10);
                if (row && !isNaN(index)) {
                    row._files.splice(index, 1);
                    syncFileInput(input, row._files);
                    renderColorNewPreviews(row);
                }
            }
        });

        syncColorRows();
    }
})();
