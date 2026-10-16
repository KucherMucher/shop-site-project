/* Home page: featured products, search results, category filter, sorting. */
(() => {
  'use strict';

  const { esc, getJSON, cardHTML, API_SEARCH } = window.Shop;

  const $  = (sel) => document.querySelector(sel);
  const $$ = (sel) => document.querySelectorAll(sel);

  const input        = $('#search-input');
  const sortSelect   = $('#sort-select');
  const resultsSec   = $('#results');
  const resultsGrid  = $('#results-grid');
  const resultsTitle = $('#results-title');
  const resultsCount = $('#results-count');
  const featuredSec  = $('#featured');
  const featuredGrid = $('#featured-grid');

  // What the page is currently showing.
  const state = { q: '', category: '', sort: 'relevance' };

  const stateHTML = (title, text, withButton) =>
    '<div class="state"><h3>' + esc(title) + '</h3><p>' + esc(text) + '</p>' +
    (withButton ? '<button type="button" class="btn" data-clear>Clear search</button>' : '') + '</div>';

  // ---------- Featured products ----------
  async function loadFeatured() {
    try {
      const data = await getJSON(API_SEARCH + '?' + new URLSearchParams({ featured: 1, limit: 8 }));
      featuredGrid.innerHTML = data.products.map(cardHTML).join('');
    } catch (err) {
      featuredGrid.innerHTML = stateHTML('Products are not available right now',
        'Check that MySQL is running and that database/schema.sql has been imported.', false);
    }
  }

  // ---------- Search results ----------
  let controller = null;

  async function runSearch() {
    syncUI();

    const hasSearch = state.q.trim() !== '' || state.category !== '';
    resultsSec.hidden = !hasSearch;
    featuredSec.hidden = hasSearch;
    updateURL();
    if (!hasSearch) return;

    controller?.abort();
    controller = new AbortController();

    const catName = state.category
      ? ($('.cat[data-category="' + state.category + '"]')?.textContent || state.category)
      : '';
    resultsTitle.textContent = state.q.trim() ? 'Results for \u201C' + state.q.trim() + '\u201D' : catName;
    resultsCount.textContent = 'Searching...';

    try {
      const data = await getJSON(
        API_SEARCH + '?' + new URLSearchParams({ q: state.q, category: state.category, sort: state.sort, limit: 24 }),
        controller.signal
      );
      const n = data.count;
      resultsCount.textContent = (n === 1 ? '1 product' : n + ' products')
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

  // Keep the category bar and the sort box in line with the state.
  function syncUI() {
    $$('.cat').forEach((a) => {
      if (a.dataset.category === state.category) a.setAttribute('aria-current', 'true');
      else a.removeAttribute('aria-current');
    });
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

  // ---------- Events ----------
  // The search form (handled in common.js) sends this event.
  document.addEventListener('shop:search', (e) => {
    state.q = e.detail.q;
    runSearch();
  });

  // Category links, the Swim/Bike/Run buttons and the "Clear search" button.
  document.addEventListener('click', (e) => {
    const catEl = e.target.closest('[data-category]');
    if (catEl) {
      e.preventDefault();
      state.category = catEl.dataset.category;
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
