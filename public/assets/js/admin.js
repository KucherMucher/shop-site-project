/**
 * TRIVEX — backoffice interactions.
 * Loaded after app.js on every admin screen.
 */
(function () {
    'use strict';

    function all(selector, scope) {
        return Array.prototype.slice.call((scope || document).querySelectorAll(selector));
    }

    function closest(el, selector) {
        return el && el.closest ? el.closest(selector) : null;
    }

    /* ------------------------------------------------------------------
     * Responsive sidebar
     * ---------------------------------------------------------------- */
    function initSidebar() {
        var sidebar = document.querySelector('[data-admin-sidebar]');
        var overlay = document.querySelector('[data-admin-overlay]');
        if (!sidebar) return;

        function open() {
            sidebar.classList.add('is-open');
            if (overlay) overlay.classList.add('is-open');
            document.body.classList.add('no-scroll');
        }

        function close() {
            sidebar.classList.remove('is-open');
            if (overlay) overlay.classList.remove('is-open');
            document.body.classList.remove('no-scroll');
        }

        all('[data-admin-open]').forEach(function (button) {
            button.addEventListener('click', open);
        });
        all('[data-admin-close]').forEach(function (button) {
            button.addEventListener('click', close);
        });
        if (overlay) overlay.addEventListener('click', close);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') close();
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 960) close();
        });
    }

    /* ------------------------------------------------------------------
     * Stock steppers
     * ---------------------------------------------------------------- */
    function initStockSteppers() {
        all('[data-stock-step]').forEach(function (button) {
            button.addEventListener('click', function () {
                var form = closest(button, 'form');
                var input = form ? form.querySelector('input[name="stock"]') : null;
                if (!input) return;
                var step = parseInt(button.getAttribute('data-stock-step'), 10) || 0;
                var min = parseInt(input.getAttribute('min'), 10);
                var max = parseInt(input.getAttribute('max'), 10);
                var value = (parseInt(input.value, 10) || 0) + step;
                if (!isNaN(min) && value < min) value = min;
                if (!isNaN(max) && value > max) value = max;
                input.value = String(value);
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    }

    /* ------------------------------------------------------------------
     * Company product status filters
     * ---------------------------------------------------------------- */
    function initCompanyFilters() {
        var group = document.querySelector('[data-company-filters]');
        var panel = document.querySelector('[data-company-products]');
        if (!group || !panel) return;

        var buttons = all('[data-company-filter]', group);
        var rows = all('[data-product-status]', panel);

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                var filter = button.getAttribute('data-company-filter') || 'all';
                buttons.forEach(function (item) { item.classList.toggle('is-active', item === button); });
                rows.forEach(function (row) {
                    row.hidden = filter !== 'all' && row.getAttribute('data-product-status') !== filter;
                });
            });
        });
    }

    /* ------------------------------------------------------------------
     * Auto-submit admin filter dropdowns
     * ---------------------------------------------------------------- */
    function initFilterForms() {
        all('.admin-filters select, .admin-toolbar select').forEach(function (select) {
            select.addEventListener('change', function () {
                var form = closest(select, 'form');
                if (!form) return;
                if (typeof form.requestSubmit === 'function') form.requestSubmit();
                else form.submit();
            });
        });
    }

    /* ------------------------------------------------------------------
     * Copy helpers (<code data-copy>)
     * ---------------------------------------------------------------- */
    function initCopy() {
        all('[data-copy]').forEach(function (element) {
            element.style.cursor = 'pointer';
            element.title = 'Copiar';
            element.addEventListener('click', function () {
                var text = element.getAttribute('data-copy') || element.textContent;
                if (!navigator.clipboard) return;
                navigator.clipboard.writeText(text.trim()).then(function () {
                    element.classList.add('is-copied');
                    window.setTimeout(function () { element.classList.remove('is-copied'); }, 1400);
                });
            });
        });
    }

    function boot() {
        initSidebar();
        initStockSteppers();
        initCompanyFilters();
        initFilterForms();
        initCopy();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
