/* Code shared by every page: helpers, product cards, the cart (localStorage),
   the cart badge in the header and the live search suggestions. */
(() => {
  'use strict';

  const MAX_QTY = 10;                       // most units of one item per order
  const API_SEARCH = 'api/search.php';

  // ---------- Helpers ----------
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) =>
    ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  const money = new Intl.NumberFormat('en-IE', { style: 'currency', currency: 'EUR' });

  async function getJSON(url, signal) {
    const res = await fetch(url, { signal });
    if (!res.ok) throw new Error('Request failed');
    return res.json();
  }

  const debounce = (fn, ms) => {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
  };

  // Simple line icons, used when a product has no photo.
  const ICONS = {
    swim:      '<circle cx="34" cy="13" r="4"/><path d="M4 28c4-4 8-4 12 0s8 4 12 0 8-4 12 0 4 2 4 2M4 40c4-4 8-4 12 0s8 4 12 0 8-4 12 0"/>',
    bike:      '<circle cx="12" cy="32" r="8"/><circle cx="36" cy="32" r="8"/><path d="M12 32l8-16h10l6 16M20 16l8 16"/>',
    run:       '<path d="M6 38v-8l8-14 6 4 4-2 8 10h10a4 4 0 0 1 4 4v6z"/>',
    nutrition: '<path d="M24 6c8 10 12 16 12 22a12 12 0 0 1-24 0c0-6 4-12 12-22z"/>',
    tech:      '<rect x="14" y="12" width="20" height="24" rx="6"/><path d="M18 12l2-6h8l2 6M18 36l2 6h8l2-6"/>'
  };

  // ---------- Product card (used on the home page and on product pages) ----------
  function cardHTML(p) {
    const onSale = p.sale_price !== null && p.sale_price < p.price;
    const discount = onSale ? Math.round((1 - p.sale_price / p.price) * 100) : 0;

    let stock = '<span class="stock">In stock</span>';
    if (p.stock === 0) stock = '<span class="stock stock--low">Out of stock</span>';
    else if (p.stock <= 5) stock = '<span class="stock stock--low">Only ' + p.stock + ' left</span>';

    const price = onSale
      ? '<span class="price price--sale">' + money.format(p.sale_price) + '</span><span class="price__old">' + money.format(p.price) + '</span>'
      : '<span class="price">' + money.format(p.price) + '</span>';

    const art = p.image
      ? '<img src="' + esc(p.image) + '" alt="" width="800" height="600" loading="lazy">'
      : '<svg viewBox="0 0 48 48" aria-hidden="true">' + (ICONS[p.category_slug] || ICONS.tech) + '</svg>';

    return '<article class="card">' +
      '<div class="card__art card__art--' + esc(p.category_slug) + '">' +
        (onSale ? '<span class="card__tag">-' + discount + '%</span>' : '') + art +
      '</div>' +
      '<div class="card__body">' +
        '<span class="card__brand">' + esc(p.brand) + ' &middot; ' + esc(p.category_name) + '</span>' +
        '<h3 class="card__name"><a class="card__link" href="product.php?id=' + p.id + '">' + esc(p.name) + '</a></h3>' +
        '<p class="card__desc">' + esc(p.description) + '</p>' +
        '<div class="card__foot"><div>' + price + '</div>' + stock + '</div>' +
      '</div>' +
    '</article>';
  }

  // ---------- Cart (saved in the browser with localStorage) ----------
  // Stored as a list of { v: variantId, q: quantity }. Names and prices are NOT stored,
  // the cart page asks the server for them, so prices and stock are always current.
  const CART_KEY = 'transition.cart';

  function readCart() {
    try {
      const raw = JSON.parse(localStorage.getItem(CART_KEY) || '[]');
      if (!Array.isArray(raw)) return [];
      return raw
        .filter((i) => Number.isInteger(i.v) && Number.isInteger(i.q) && i.q > 0)
        .map((i) => ({ v: i.v, q: Math.min(i.q, MAX_QTY) }));
    } catch (err) {
      return [];                            // broken or blocked storage: start with an empty cart
    }
  }

  function writeCart(items) {
    try { localStorage.setItem(CART_KEY, JSON.stringify(items)); } catch (err) { /* storage full or blocked */ }
    document.dispatchEvent(new CustomEvent('cart:change'));
  }

  const Cart = {
    items: readCart,
    count: () => readCart().reduce((n, i) => n + i.q, 0),
    qtyOf: (v) => (readCart().find((i) => i.v === v) || { q: 0 }).q,
    add(v, q) {
      const items = readCart();
      const line = items.find((i) => i.v === v);
      if (line) line.q = Math.min(line.q + q, MAX_QTY);
      else items.push({ v, q: Math.min(q, MAX_QTY) });
      writeCart(items);
    },
    setQty(v, q) {
      const items = readCart().filter((i) => i.v !== v || q > 0);
      const line = items.find((i) => i.v === v);
      if (line) line.q = Math.min(q, MAX_QTY);
      writeCart(items);
    },
    remove: (v) => writeCart(readCart().filter((i) => i.v !== v)),
    clear: () => writeCart([])
  };

  // ---------- Cart badge in the header ----------
  function updateBadge() {
    const n = Cart.count();
    document.querySelectorAll('[data-cart-count]').forEach((el) => {
      el.textContent = n;
      el.hidden = n === 0;
    });
    const link = document.querySelector('.cart-link');
    if (link) link.setAttribute('aria-label', n ? 'Cart, ' + n + (n === 1 ? ' item' : ' items') : 'Cart, empty');
  }
  document.addEventListener('cart:change', updateBadge);
  window.addEventListener('storage', (e) => {          // the cart changed in another tab
    if (e.key === CART_KEY) document.dispatchEvent(new CustomEvent('cart:change'));
  });
  updateBadge();

  // ---------- Search box: live suggestions ----------
  const form = document.getElementById('search-form');
  const input = document.getElementById('search-input');
  const list = document.getElementById('suggestions');

  const escRegex = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

  // Wraps the matched words in <mark>. The text is escaped piece by piece.
  function highlight(text, terms) {
    if (!terms.length) return esc(text);
    const re = new RegExp('(' + terms.map(escRegex).join('|') + ')', 'gi');
    return text.split(re).map((part, i) => (i % 2 ? '<mark>' + esc(part) + '</mark>' : esc(part))).join('');
  }

  let controller = null;
  let activeIndex = -1;
  let found = [];

  function closeList() {
    list.hidden = true;
    list.innerHTML = '';
    input.setAttribute('aria-expanded', 'false');
    input.removeAttribute('aria-activedescendant');
    activeIndex = -1;
    found = [];
  }

  function setActive(i) {
    const items = list.querySelectorAll('li[data-id]');
    items.forEach((li, idx) => li.setAttribute('aria-selected', String(idx === i)));
    activeIndex = i;
    if (i >= 0) input.setAttribute('aria-activedescendant', items[i].id);
    else input.removeAttribute('aria-activedescendant');
  }

  const openProduct = (id) => { window.location.href = 'product.php?id=' + id; };

  async function loadSuggestions() {
    const q = input.value.trim();
    if (q.length < 2) { closeList(); return; }

    controller?.abort();
    controller = new AbortController();

    try {
      const data = await getJSON(API_SEARCH + '?' + new URLSearchParams({ q, limit: 6 }), controller.signal);
      found = data.products;
      const terms = q.split(/\s+/).filter(Boolean).slice(0, 6);

      list.innerHTML = found.length
        ? found.map((p, i) =>
            '<li role="option" id="sugg-' + i + '" aria-selected="false" data-id="' + p.id + '">' +
              '<span>' + highlight(p.name, terms) + '</span>' +
              '<span class="sugg__meta">' + esc(p.category_name) + ' &middot; ' + money.format(p.sale_price ?? p.price) + '</span>' +
            '</li>').join('')
        : '<li class="sugg__empty" role="option" aria-disabled="true">No matches for \u201C' + esc(q) + '\u201D</li>';

      list.hidden = false;
      input.setAttribute('aria-expanded', 'true');
      activeIndex = -1;
    } catch (err) {
      if (err.name !== 'AbortError') closeList();
    }
  }

  if (form && input && list) {
    input.addEventListener('input', debounce(loadSuggestions, 200));

    input.addEventListener('keydown', (e) => {
      const count = list.querySelectorAll('li[data-id]').length;
      if (e.key === 'ArrowDown' && count) { e.preventDefault(); setActive((activeIndex + 1) % count); }
      else if (e.key === 'ArrowUp' && count) { e.preventDefault(); setActive((activeIndex - 1 + count) % count); }
      else if (e.key === 'Escape') { closeList(); }
      else if (e.key === 'Enter' && activeIndex >= 0) { e.preventDefault(); openProduct(found[activeIndex].id); }
    });

    list.addEventListener('click', (e) => {
      const li = e.target.closest('li[data-id]');
      if (li) openProduct(li.dataset.id);
    });

    document.addEventListener('click', (e) => { if (!form.contains(e.target)) closeList(); });

    // On the home page the results are shown in place (app.js listens for this event).
    // On other pages the form simply goes to index.php?q=...
    form.addEventListener('submit', (e) => {
      closeList();
      if (document.body.dataset.page === 'home') {
        e.preventDefault();
        document.dispatchEvent(new CustomEvent('shop:search', { detail: { q: input.value } }));
      }
    });
  }

  window.Shop = { esc, money, getJSON, cardHTML, Cart, MAX_QTY, API_SEARCH };
})();
