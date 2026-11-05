/**
 * TRIVEX — global storefront behaviour.
 * Vanilla JS, no dependencies. All hooks are data-attributes so the
 * server-rendered markup stays in charge.
 */
(function () {
    'use strict';

    var root = document.documentElement;
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ------------------------------------------------------------------
     * Small helpers
     * ---------------------------------------------------------------- */
    function on(target, event, handler, options) {
        if (!target) return;
        target.addEventListener(event, handler, options || false);
    }

    function all(selector, scope) {
        return Array.prototype.slice.call((scope || document).querySelectorAll(selector));
    }

    function closest(el, selector) {
        return el && el.closest ? el.closest(selector) : null;
    }

    function debounce(fn, wait) {
        var timer = null;
        return function () {
            var args = arguments;
            var context = this;
            window.clearTimeout(timer);
            timer = window.setTimeout(function () {
                fn.apply(context, args);
            }, wait);
        };
    }

    function dispatch(el, name) {
        if (!el) return;
        el.dispatchEvent(new Event(name, { bubbles: true }));
    }

    /* ------------------------------------------------------------------
     * Theme
     * ---------------------------------------------------------------- */
    function currentTheme() {
        return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }

    function applyTheme(theme, persist) {
        root.setAttribute('data-theme', theme === 'dark' ? 'dark' : 'light');
        var meta = document.querySelector('meta[name="theme-color"]');
        if (meta) meta.setAttribute('content', theme === 'dark' ? '#041d35' : '#062a4d');
        if (persist) {
            try {
                window.localStorage.setItem('trivex-theme', theme);
            } catch (e) {
                /* storage unavailable — theme still applies for this page */
            }
        }
    }

    function initTheme() {
        applyTheme(currentTheme(), false);
        all('[data-theme-toggle]').forEach(function (button) {
            on(button, 'click', function () {
                var next = currentTheme() === 'dark' ? 'light' : 'dark';
                applyTheme(next, true);
            });
        });
    }

    /* ------------------------------------------------------------------
     * Mobile menu, mega menu, account dropdown
     * ---------------------------------------------------------------- */
    function initMenus() {
        var mobile = document.querySelector('[data-mobile-menu]');

        function openMobile() {
            if (!mobile) return;
            mobile.classList.add('is-open');
            mobile.setAttribute('aria-hidden', 'false');
            document.body.classList.add('no-scroll');
            all('[data-menu-open]').forEach(function (b) { b.setAttribute('aria-expanded', 'true'); });
            var first = mobile.querySelector('a, button');
            if (first) first.focus({ preventScroll: true });
        }

        function closeMobile() {
            if (!mobile) return;
            mobile.classList.remove('is-open');
            mobile.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('no-scroll');
            all('[data-menu-open]').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
        }

        all('[data-menu-open]').forEach(function (button) { on(button, 'click', openMobile); });
        all('[data-menu-close]').forEach(function (button) { on(button, 'click', closeMobile); });

        var shopToggle = document.querySelector('[data-shop-toggle]');
        var shopPanel = document.querySelector('[data-shop-panel]');
        if (shopToggle && shopPanel) {
            on(shopToggle, 'click', function (event) {
                event.stopPropagation();
                var open = shopPanel.classList.toggle('is-open');
                shopToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                var account = document.querySelector('[data-account-dropdown].is-open');
                if (account) account.classList.remove('is-open');
            });
        }

        var accountToggle = document.querySelector('[data-account-toggle]');
        var accountDropdown = document.querySelector('[data-account-dropdown]');
        if (accountToggle && accountDropdown) {
            on(accountToggle, 'click', function (event) {
                event.stopPropagation();
                var open = accountDropdown.classList.toggle('is-open');
                accountToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (shopPanel) shopPanel.classList.remove('is-open');
            });
        }

        on(document, 'click', function (event) {
            if (shopPanel && !closest(event.target, '[data-shop-menu]')) {
                shopPanel.classList.remove('is-open');
                if (shopToggle) shopToggle.setAttribute('aria-expanded', 'false');
            }
            if (accountDropdown && !closest(event.target, '[data-account-menu]')) {
                accountDropdown.classList.remove('is-open');
                if (accountToggle) accountToggle.setAttribute('aria-expanded', 'false');
            }
        });

        on(document, 'keydown', function (event) {
            if (event.key !== 'Escape') return;
            closeMobile();
            if (shopPanel) shopPanel.classList.remove('is-open');
            if (accountDropdown) accountDropdown.classList.remove('is-open');
            all('.modal.is-open').forEach(closeModal);
        });
    }

    /* ------------------------------------------------------------------
     * Header search suggestions
     * ---------------------------------------------------------------- */
    function initSearch() {
        var form = document.querySelector('[data-search-form]');
        if (!form) return;
        var input = form.querySelector('[data-search-input]');
        var results = form.querySelector('[data-search-results]');
        var body = form.querySelector('[data-search-body]');
        var allLink = form.querySelector('[data-search-all]');
        if (!input || !results || !body) return;

        var action = form.getAttribute('action') || '/pesquisa';
        var api = action.replace(/\/pesquisa\/?$/, '/api/pesquisa');
        if (api === action) api = '/api/pesquisa';

        function hide() {
            results.hidden = true;
            results.classList.remove('is-open');
        }

        function show() {
            results.hidden = false;
            results.classList.add('is-open');
        }

        function makeItem(item) {
            var link = document.createElement('a');
            link.className = 'search-suggestion';
            link.href = item.url;

            var image = document.createElement('img');
            image.src = item.image;
            image.alt = '';
            image.loading = 'lazy';
            image.width = 48;
            image.height = 48;

            var info = document.createElement('span');
            info.className = 'search-suggestion__info';
            var name = document.createElement('strong');
            name.textContent = item.name;
            var meta = document.createElement('small');
            meta.textContent = [item.brand, item.category].filter(Boolean).join(' · ');
            info.appendChild(name);
            info.appendChild(meta);

            var price = document.createElement('span');
            price.className = 'search-suggestion__price';
            var strong = document.createElement('strong');
            strong.textContent = item.price;
            price.appendChild(strong);
            if (item.old_price) {
                var old = document.createElement('del');
                old.textContent = item.old_price;
                price.appendChild(old);
            }

            link.appendChild(image);
            link.appendChild(info);
            link.appendChild(price);
            return link;
        }

        var search = debounce(function () {
            var query = input.value.trim();
            if (query.length < 2) {
                hide();
                return;
            }
            fetch(api + '?q=' + encodeURIComponent(query), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (response) { return response.ok ? response.json() : null; })
                .then(function (data) {
                    if (!data) { hide(); return; }
                    body.textContent = '';
                    if (!data.items || !data.items.length) {
                        var empty = document.createElement('p');
                        empty.className = 'search-suggestion__empty';
                        empty.textContent = input.getAttribute('data-empty') || 'Sem resultados.';
                        body.appendChild(empty);
                    } else {
                        data.items.forEach(function (item) { body.appendChild(makeItem(item)); });
                    }
                    if (allLink) allLink.href = action + '?q=' + encodeURIComponent(query);
                    show();
                })
                .catch(function () { hide(); });
        }, 220);

        on(input, 'input', search);
        on(input, 'focus', function () {
            if (input.value.trim().length >= 2 && body.childElementCount) show();
        });
        on(input, 'keydown', function (event) {
            if (event.key === 'Escape') hide();
        });
        on(document, 'click', function (event) {
            if (!closest(event.target, '[data-search-form]')) hide();
        });
        on(form, 'submit', hide);
    }

    /* ------------------------------------------------------------------
     * Quantity steppers + auto-submitting forms
     * ---------------------------------------------------------------- */
    function clampQty(input) {
        var min = parseInt(input.getAttribute('min'), 10);
        var max = parseInt(input.getAttribute('max'), 10);
        var value = parseInt(input.value, 10);
        if (isNaN(value)) value = isNaN(min) ? 1 : min;
        if (!isNaN(min) && value < min) value = min;
        if (!isNaN(max) && max > 0 && value > max) value = max;
        input.value = String(value);
        return value;
    }

    function initQuantities() {
        on(document, 'click', function (event) {
            var button = closest(event.target, '[data-qty-minus], [data-qty-plus]');
            if (!button) return;
            event.preventDefault();
            var wrap = closest(button, '[data-qty]') || closest(button, '.qty-control');
            if (!wrap) return;
            var input = wrap.querySelector('input[type="number"], input[name="quantity"]');
            if (!input) return;
            var step = button.hasAttribute('data-qty-plus') ? 1 : -1;
            input.value = String((parseInt(input.value, 10) || 0) + step);
            clampQty(input);
            dispatch(input, 'change');
        });

        all('[data-qty] input, .qty-control input').forEach(function (input) {
            on(input, 'blur', function () { clampQty(input); });
        });

        all('[data-auto-submit-form]').forEach(function (form) {
            var timer = null;
            var submit = debounce(function () {
                if (form.dataset.submitting === '1') return;
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }, 320);

            on(form, 'change', function (event) {
                if (event.target && event.target.matches('input[type="number"], select')) {
                    window.clearTimeout(timer);
                    submit();
                }
            });
        });
    }

    /* ------------------------------------------------------------------
     * Confirmation prompts
     * ---------------------------------------------------------------- */
    function initConfirm() {
        on(document, 'submit', function (event) {
            var form = event.target;
            if (!form || form.tagName !== 'FORM') return;
            var submitter = event.submitter || null;
            var message = (submitter && submitter.getAttribute('data-confirm')) || form.getAttribute('data-confirm');
            if (!message) return;
            if (form.dataset.confirmed === '1') {
                delete form.dataset.confirmed;
                return;
            }
            if (!window.confirm(message)) {
                event.preventDefault();
                event.stopImmediatePropagation();
                return;
            }
            form.dataset.confirmed = '1';
        }, true);
    }

    /* ------------------------------------------------------------------
     * Flash messages
     * ---------------------------------------------------------------- */
    function initFlash() {
        all('[data-flash-close]').forEach(function (button) {
            on(button, 'click', function () {
                var flash = closest(button, '.flash') || closest(button, '.alert');
                if (flash) flash.remove();
            });
        });

        all('.flash').forEach(function (flash) {
            window.setTimeout(function () {
                flash.classList.add('is-leaving');
                window.setTimeout(function () { flash.remove(); }, 350);
            }, 6500);
        });
    }

    /* ------------------------------------------------------------------
     * Checkout: saved addresses, billing, payment method
     * ---------------------------------------------------------------- */
    function initCheckout() {
        var list = document.querySelector('[data-address-list]');
        if (list) {
            var addresses = {};
            try {
                addresses = JSON.parse(list.getAttribute('data-addresses') || '{}');
            } catch (e) {
                addresses = {};
            }

            function fields() {
                var result = {};
                all('[data-ship-field]').forEach(function (field) {
                    result[field.getAttribute('data-ship-field')] = field;
                });
                return result;
            }

            function fill(address) {
                var map = fields();
                Object.keys(map).forEach(function (key) {
                    if (address && Object.prototype.hasOwnProperty.call(address, key)) {
                        map[key].value = address[key] || '';
                    }
                });
                var country = document.querySelector('select[name="shipping_country"]');
                if (country && address && address.country) {
                    var found = false;
                    all('option', country).forEach(function (option) {
                        if (option.value === address.country) {
                            option.selected = true;
                            found = true;
                        }
                    });
                    if (!found) {
                        var option = document.createElement('option');
                        option.value = address.country;
                        option.textContent = address.country;
                        country.appendChild(option);
                        country.value = address.country;
                    }
                }
            }

            all('[data-address-select]').forEach(function (radio) {
                on(radio, 'change', function () {
                    all('.saved-address').forEach(function (label) { label.classList.remove('is-selected'); });
                    var label = closest(radio, '.saved-address');
                    if (label) label.classList.add('is-selected');
                    if (radio.hasAttribute('data-new-address')) {
                        fill(null);
                        all('[data-ship-field]').forEach(function (field) { field.value = ''; });
                        var nameField = document.querySelector('[data-ship-field="name"]');
                        if (nameField) nameField.focus({ preventScroll: true });
                    } else {
                        fill(addresses[radio.value] || null);
                    }
                });
            });
        }

        var billingSame = document.querySelector('[data-billing-same]');
        var billingFields = document.querySelector('[data-billing-fields]');
        if (billingSame && billingFields) {
            function syncBilling() {
                billingFields.hidden = billingSame.checked;
            }
            on(billingSame, 'change', syncBilling);
            syncBilling();
        }

        var paymentRadios = all('[data-payment-radio]');
        var paymentPanels = all('[data-payment-panel]');
        if (paymentRadios.length) {
            function syncPayment() {
                var selected = paymentRadios.filter(function (r) { return r.checked; })[0] || paymentRadios[0];
                paymentRadios.forEach(function (radio) {
                    var option = closest(radio, '.payment-option');
                    if (option) option.classList.toggle('is-active', radio === selected);
                });
                paymentPanels.forEach(function (panel) {
                    panel.hidden = panel.getAttribute('data-payment-panel') !== selected.value;
                });
            }
            paymentRadios.forEach(function (radio) { on(radio, 'change', syncPayment); });
            syncPayment();
        }
    }

    /* ------------------------------------------------------------------
     * Password reveal
     * ---------------------------------------------------------------- */
    function initPasswords() {
        all('[data-password-toggle]').forEach(function (button) {
            on(button, 'click', function () {
                var wrap = closest(button, '.field__control') || closest(button, '.field') || button.parentElement;
                var input = wrap ? wrap.querySelector('[data-password-input]') : null;
                if (!input) return;
                var showing = input.getAttribute('type') === 'text';
                input.setAttribute('type', showing ? 'password' : 'text');
                button.setAttribute('aria-pressed', showing ? 'false' : 'true');
                button.classList.toggle('is-active', !showing);
            });
        });
    }

    /* ------------------------------------------------------------------
     * Demo accounts (login page)
     * ---------------------------------------------------------------- */
    function initDemoAccounts() {
        all('[data-demo-email]').forEach(function (button) {
            on(button, 'click', function () {
                var email = document.querySelector('input[name="email"]');
                var password = document.querySelector('input[name="password"]');
                if (email) {
                    email.value = button.getAttribute('data-demo-email') || '';
                    dispatch(email, 'input');
                }
                if (password) {
                    password.value = button.getAttribute('data-demo-password') || '';
                    dispatch(password, 'input');
                }
                var submit = document.querySelector('.auth-form button[type="submit"]');
                if (submit) submit.focus();
            });
        });
    }

    /* ------------------------------------------------------------------
     * Filters drawer (catalog)
     * ---------------------------------------------------------------- */
    function initFilters() {
        var drawer = document.querySelector('[data-filters]');
        var backdrop = document.querySelector('[data-filters-backdrop]');
        if (!drawer) return;

        function open() {
            drawer.classList.add('is-open');
            if (backdrop) backdrop.classList.add('is-open');
            document.body.classList.add('no-scroll');
            all('[data-filters-open]').forEach(function (b) { b.setAttribute('aria-expanded', 'true'); });
        }

        function close() {
            drawer.classList.remove('is-open');
            if (backdrop) backdrop.classList.remove('is-open');
            document.body.classList.remove('no-scroll');
            all('[data-filters-open]').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
        }

        all('[data-filters-open]').forEach(function (b) { on(b, 'click', open); });
        all('[data-filters-close]').forEach(function (b) { on(b, 'click', close); });
        on(backdrop, 'click', close);
        on(document, 'keydown', function (event) {
            if (event.key === 'Escape') close();
        });
    }

    /* ------------------------------------------------------------------
     * Reveal on scroll
     * ---------------------------------------------------------------- */
    function initReveal() {
        var items = all('[data-reveal]');
        if (!items.length) return;

        if (reducedMotion || !('IntersectionObserver' in window)) {
            items.forEach(function (item) { item.classList.add('is-visible'); });
            return;
        }

        root.classList.add('reveal-ready');
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.06 });

        items.forEach(function (item) { observer.observe(item); });
    }

    /* ------------------------------------------------------------------
     * Countdown timers
     * ---------------------------------------------------------------- */
    function initCountdowns() {
        all('[data-countdown]').forEach(function (timer) {
            var deadline = parseInt(timer.getAttribute('data-deadline') || '0', 10);
            if (!deadline) return;
            var target = deadline > 100000000000 ? deadline : deadline * 1000;
            var days = timer.querySelector('[data-days]');
            var hours = timer.querySelector('[data-hours]');
            var minutes = timer.querySelector('[data-minutes]');
            var seconds = timer.querySelector('[data-seconds]');

            function pad(value) {
                return value < 10 ? '0' + value : String(value);
            }

            function tick() {
                var remaining = Math.max(0, target - Date.now());
                var totalSeconds = Math.floor(remaining / 1000);
                var d = Math.floor(totalSeconds / 86400);
                var h = Math.floor((totalSeconds % 86400) / 3600);
                var m = Math.floor((totalSeconds % 3600) / 60);
                var s = totalSeconds % 60;
                if (days) days.textContent = pad(d);
                if (hours) hours.textContent = pad(h);
                if (minutes) minutes.textContent = pad(m);
                if (seconds) seconds.textContent = pad(s);
                if (remaining <= 0) window.clearInterval(handle);
            }

            var handle = window.setInterval(tick, 1000);
            tick();
        });
    }

    /* ------------------------------------------------------------------
     * Modals (size guide)
     * ---------------------------------------------------------------- */
    function openModal(modal) {
        if (!modal) return;
        modal.hidden = false;
        modal.classList.add('is-open');
        document.body.classList.add('no-scroll');
        var focusable = modal.querySelector('[data-modal-close], button, a');
        if (focusable) focusable.focus({ preventScroll: true });
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.hidden = true;
        document.body.classList.remove('no-scroll');
    }

    function initModals() {
        all('[data-size-guide]').forEach(function (button) {
            on(button, 'click', function () {
                openModal(document.querySelector('[data-size-modal]'));
            });
        });
        all('[data-modal-close]').forEach(function (button) {
            on(button, 'click', function (event) {
                event.preventDefault();
                closeModal(closest(button, '.modal'));
            });
        });
        all('.modal').forEach(function (modal) {
            on(modal, 'click', function (event) {
                if (event.target === modal) closeModal(modal);
            });
        });
    }

    /* ------------------------------------------------------------------
     * Rating widget keyboard/active state
     * ---------------------------------------------------------------- */
    function initRating() {
        all('[data-rating-widget]').forEach(function (form) {
            var inputs = all('input[name="rating"]', form);
            inputs.forEach(function (input) {
                on(input, 'change', function () {
                    inputs.forEach(function (item) {
                        var label = item.closest('label');
                        if (label) label.classList.toggle('is-active', item.checked);
                    });
                });
            });
        });
    }

    /* ------------------------------------------------------------------
     * Accordions — keep one open at a time (optional enhancement)
     * ---------------------------------------------------------------- */
    function initAccordions() {
        all('[data-accordion]').forEach(function (group) {
            var items = all('details', group);
            items.forEach(function (item) {
                on(item, 'toggle', function () {
                    if (!item.open) return;
                    items.forEach(function (other) {
                        if (other !== item) other.open = false;
                    });
                });
            });
        });
    }

    /* ------------------------------------------------------------------
     * Header elevation on scroll
     * ---------------------------------------------------------------- */
    function initHeaderScroll() {
        var header = document.querySelector('[data-header]');
        if (!header) return;
        var update = function () {
            header.classList.toggle('is-scrolled', window.scrollY > 8);
        };
        on(window, 'scroll', debounce(update, 60), { passive: true });
        update();
    }

    /* ------------------------------------------------------------------
     * Boot
     * ---------------------------------------------------------------- */
    function boot() {
        initTheme();
        initMenus();
        initSearch();
        initQuantities();
        initConfirm();
        initFlash();
        initCheckout();
        initPasswords();
        initDemoAccounts();
        initFilters();
        initReveal();
        initCountdowns();
        initModals();
        initRating();
        initAccordions();
        initHeaderScroll();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
