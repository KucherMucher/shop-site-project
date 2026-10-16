(() => {
  'use strict';

  // ---------- Setup ----------
  const API = 'api/search.php';

  const $  = (sel) => document.querySelector(sel);
  const $$ = (sel) => document.querySelectorAll(sel);

  const form        = $('#search-form');
  const input       = $('#search-input');
  const suggestions = $('#suggestions');
  const sortSelect  = $('#sort-select');
  const resultsSec  = $('#results');
  const resultsGrid = $('#results-grid');
  const resultsTitle = $('#results-title');
  const resultsCount = $('#results-count');
  const featuredSec = $('#featured');
  const featuredGrid = $('#featured-grid');

  // Current search state. This is what the page shows.
  const state = { q: '', category: '', sort: 'relevance' };

  const money = new Intl.NumberFormat('en-IE', { style: 'currency', currency: 'EUR' });

  // Simple line icons for the product tiles.
  const ICONS = {
    swim:      '<circle cx="34" cy="13" r="4"/><path d="M4 28c4-4 8-4 12 0s8 4 12 0 8-4 12 0 4 2 4 2M4 40c4-4 8-4 12 0s8 4 12 0 8-4 12 0"/>',
    bike:      '<circle cx="12" cy="32" r="8"/><circle cx="36" cy="32" r="8"/><path d="M12 32l8-16h10l6 16M20 16l8 16"/>',
    run:       '<path d="M6 38v-8l8-14 6 4 4-2 8 10h10a4 4 0 0 1 4 4v6z"/>',
    nutrition: '<path d="M24 6c8 10 12 16 12 22a12 12 0 0 1-24 0c0-6 4-12 12-22z"/>',
    tech:      '<rect x="14" y="12" width="20" height="24" rx="6"/><path d="M18 12l2-6h8l2 6M18 36l2 6h8l2-6"/>'
  };

  // ---------- Helpers ----------
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) =>
    ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  const escRegex = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

  const debounce = (fn, ms) => {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
  };

  const termsOf = (q) => q.trim().split(/\s+/).filter(Boolean).slice(0, 6);

  // Wraps the matched words in <mark>. Text is escaped piece by piece.
  function highlight(text, terms) {
    if (!terms.length) return esc(text);
    const re = new RegExp('(' + terms.map(escRegex).join('|') + ')', 'gi');
    return text.split(re).map((part, i) => (i % 2 ? '<mark>' + esc(part) + '</mark>' : esc(part))).join('');
  }

  async function fetchProducts(params, signal) {
    const res = await fetch(API + '?' + new URLSearchParams(params), { signal });
    if (!res.ok) throw new Error('Request failed');
    return res.json();
  }

  // ---------- Product cards ----------
  function cardHTML(p) {
    const onSale = p.sale_price !== null && p.sale_price < p.price;
    const discount = onSale ? Math.round((1 - p.sale_price / p.price) * 100) : 0;
    const icon = ICONS[p.category_slug] || ICONS.tech;

    let stock = '<span class="stock">In stock</span>';
    if (p.stock === 0) stock = '<span class="stock stock--low">Out of stock</span>';
    else if (p.stock <= 5) stock = '<span class="stock stock--low">Only ' + p.stock + ' left</span>';

    const price = onSale
      ? '<span class="price price--sale">' + money.format(p.sale_price) + '</span><span class="price__old">' + money.format(p.price) + '</span>'
      : '<span class="price">' + money.format(p.price) + '</span>';

    return '<article class="card">' +
      '<div class="card__art card__art--' + esc(p.category_slug) + '">' +
        (onSale ? '<span class="card__tag">-' + discount + '%</span>' : '') +
        '<svg viewBox="0 0 48 48" aria-hidden="true">' + icon + '</svg>' +
      '</div>' +
      '<div class="card__body">' +
        '<span class="card__brand">' + esc(p.brand) + ' &middot; ' + esc(p.category_name) + '</span>' +
        '<h3 class="card__name">' + esc(p.name) + '</h3>' +
        '<p class="card__desc">' + esc(p.description) + '</p>' +
        '<div class="card__foot"><div>' + price + '</div>' + stock + '</div>' +
      '</div>' +
    '</article>';
  }

  const stateHTML = (title, text, withButton) =>
    '<div class="state"><h3>' + esc(title) + '</h3><p>' + esc(text) + '</p>' +
    (withButton ? '<button type="button" class="btn" data-clear>Clear search</button>' : '') + '</div>';

  // ---------- Featured products ----------
  async function loadFeatured() {
    try {
      const data = await fetchProducts({ featured: 1, limit: 8 });
      featuredGrid.innerHTML = data.products.map(cardHTML).join('');
    } catch (err) {
      featuredGrid.innerHTML = stateHTML('Products are not available right now',
        'Check that MySQL is running and that database/schema.sql has been imported.', false);
    }
  }

  // ---------- Search results ----------
  let resultsController = null;

  async function runSearch() {
    closeSuggestions();
    syncUI();

    const hasSearch = state.q.trim() !== '' || state.category !== '';
    resultsSec.hidden = !hasSearch;
    featuredSec.hidden = hasSearch;
    updateURL();
    if (!hasSearch) return;

    resultsController?.abort();
    resultsController = new AbortController();

    const catName = state.category
      ? ($('.cat[data-category="' + state.category + '"]')?.textContent || state.category)
      : '';
    resultsTitle.textContent = state.q.trim() ? 'Results for \u201C' + state.q.trim() + '\u201D' : catName;
    resultsCount.textContent = 'Searching...';

    try {
      const data = await fetchProducts(
        { q: state.q, category: state.category, sort: state.sort, limit: 24 },
        resultsController.signal
      );
      const n = data.count;
      resultsCount.textContent = n === 1 ? '1 product' : n + ' products'
        + (state.q.trim() && state.category ? ' in ' + catName : '');

      resultsGrid.innerHTML = n
        ? data.products.map(cardHTML).join('')
        : stateHTML('No products found',
            'Try a shorter word, check the spelling, or pick a category above.', true);
    } catch (err) {
      if (err.name === 'AbortError') return;
      resultsCount.textContent = '';
      resultsGrid.innerHTML = stateHTML('Search is not working right now',
        'Check that MySQL is running and that database/schema.sql has been imported.', false);
    }
  }

  // Keep the category buttons and the sort box in line with the state.
  function syncUI() {
    $$('.cat').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.category === state.category)));
    sortSelect.value = state.sort;
  }

  function updateURL() {
    const p = new URLSearchParams();
    if (state.q.trim()) p.set('q', state.q.trim());
    if (state.category) p.set('category', state.category);
    if (state.sort !== 'relevance') p.set('sort', state.sort);
    const qs = p.toString();
    history.replaceState(null, '', qs ? '?' + qs : location.pathname);
  }

  // ---------- Live suggestions ----------
  let suggController = null;
  let activeIndex = -1;
  let suggItems = [];

  function closeSuggestions() {
    suggestions.hidden = true;
    suggestions.innerHTML = '';
    input.setAttribute('aria-expanded', 'false');
    input.removeAttribute('aria-activedescendant');
    activeIndex = -1;
    suggItems = [];
  }

  function setActive(i) {
    const items = suggestions.querySelectorAll('li[data-name]');
    items.forEach((li, idx) => li.setAttribute('aria-selected', String(idx === i)));
    activeIndex = i;
    if (i >= 0) input.setAttribute('aria-activedescendant', items[i].id);
    else input.removeAttribute('aria-activedescendant');
  }

  async function loadSuggestions() {
    const q = input.value.trim();
    if (q.length < 2) { closeSuggestions(); return; }

    suggController?.abort();
    suggController = new AbortController();

    try {
      const data = await fetchProducts({ q, limit: 6 }, suggController.signal);
      suggItems = data.products;
      const terms = termsOf(q);

      suggestions.innerHTML = suggItems.length
        ? suggItems.map((p, i) =>
            '<li role="option" id="sugg-' + i + '" aria-selected="false" data-name="' + esc(p.name) + '">' +
              '<span>' + highlight(p.name, terms) + '</span>' +
              '<span class="sugg__meta">' + esc(p.category_name) + ' &middot; ' + money.format(p.sale_price ?? p.price) + '</span>' +
            '</li>').join('')
        : '<li class="sugg__empty" role="option" aria-disabled="true">No matches for \u201C' + esc(q) + '\u201D</li>';

      suggestions.hidden = false;
      input.setAttribute('aria-expanded', 'true');
      activeIndex = -1;
    } catch (err) {
      if (err.name !== 'AbortError') closeSuggestions();
    }
  }

  // ---------- Events ----------
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    state.q = input.value;
    runSearch();
  });

  input.addEventListener('input', debounce(loadSuggestions, 200));

  input.addEventListener('keydown', (e) => {
    const count = suggestions.querySelectorAll('li[data-name]').length;
    if (e.key === 'ArrowDown' && count) { e.preventDefault(); setActive((activeIndex + 1) % count); }
    else if (e.key === 'ArrowUp' && count) { e.preventDefault(); setActive((activeIndex - 1 + count) % count); }
    else if (e.key === 'Escape') { closeSuggestions(); }
    else if (e.key === 'Enter' && activeIndex >= 0) {
      e.preventDefault();
      input.value = suggItems[activeIndex].name;
      state.q = input.value;
      runSearch();
    }
  });

  suggestions.addEventListener('click', (e) => {
    const li = e.target.closest('li[data-name]');
    if (!li) return;
    input.value = li.dataset.name;
    state.q = input.value;
    runSearch();
  });

  document.addEventListener('click', (e) => {
    if (!form.contains(e.target)) closeSuggestions();
  });

  // Category bar, hero course buttons and the "Clear search" button.
  document.addEventListener('click', (e) => {
    const catBtn = e.target.closest('[data-category]');
    if (catBtn) {
      state.category = catBtn.dataset.category;
      if (state.category === '') { state.q = ''; input.value = ''; }
      runSearch();
      if (state.category) resultsSec.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    if (e.target.closest('[data-clear]')) {
      state.q = ''; state.category = ''; input.value = '';
      runSearch();
      input.focus();
    }
  });

  sortSelect.addEventListener('change', () => {
    state.sort = sortSelect.value;
    runSearch();
  });

  // ---------- Start ----------
  const params = new URLSearchParams(location.search);
  state.q = params.get('q') || '';
  state.category = params.get('category') || '';
  state.sort = params.get('sort') || 'relevance';
  input.value = state.q;

  loadFeatured();
  runSearch();
})();
