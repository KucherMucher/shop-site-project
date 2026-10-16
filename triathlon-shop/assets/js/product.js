/* Product page: photo gallery, color/size choice, stock, quantity, add to cart. */
(() => {
  'use strict';

  const { esc, getJSON, cardHTML, Cart, MAX_QTY, API_SEARCH } = window.Shop;
  const $ = (sel) => document.querySelector(sel);

  const { product, variants } = JSON.parse($('#product-data').textContent);

  // ---------- Photo gallery ----------
  const mainImg = $('#gallery-main');
  const thumbs = document.querySelectorAll('.thumb');
  thumbs.forEach((btn) => btn.addEventListener('click', () => {
    mainImg.src = btn.dataset.src;
    mainImg.alt = btn.dataset.alt;
    thumbs.forEach((t) => t.setAttribute('aria-pressed', String(t === btn)));
  }));

  // ---------- Options ----------
  // Unique colors and sizes, in the order they appear in the database.
  const colors = [];
  variants.forEach((v) => { if (v.color && !colors.some((c) => c.name === v.color)) colors.push({ name: v.color, hex: v.hex }); });
  const sizes = [...new Set(variants.map((v) => v.size).filter(Boolean))];

  const needColor = colors.length > 0;
  const needSize = sizes.length > 0;

  // What the shopper has chosen. If there is only one choice, it is chosen already.
  const sel = {
    color: colors.length === 1 ? colors[0].name : null,
    size: sizes.length === 1 ? sizes[0] : null
  };

  const colorBox = $('#opt-color'), sizeBox = $('#opt-size');
  const swatchBox = colorBox.querySelector('.swatches'), sizeBtnBox = sizeBox.querySelector('.sizes');
  const colorName = $('#color-name'), stockLine = $('#stock-line');
  const qtyInput = $('#qty'), addBtn = $('#add-btn'), msg = $('#buy-msg');

  // The variant for the current choice, or null if something is still missing.
  function chosenVariant() {
    if ((needColor && !sel.color) || (needSize && !sel.size)) return null;
    return variants.find((v) => (v.color || null) === (sel.color || null) && (v.size || null) === (sel.size || null)) || null;
  }

  const colorAvailable = (c) => variants.some((v) => v.color === c && v.stock > 0 && (!sel.size || v.size === sel.size));
  const sizeAvailable = (s) => variants.some((v) => v.size === s && v.stock > 0 && (!sel.color || v.color === sel.color));
  const totalStock = variants.reduce((n, v) => n + v.stock, 0);

  function describe(v) {
    return [v.color, v.size && 'size ' + v.size].filter(Boolean).join(', ');
  }

  function render() {
    // A size that is not available in the chosen color is dropped.
    if (sel.size && !sizeAvailable(sel.size)) sel.size = null;
    if (sel.color && !colorAvailable(sel.color)) sel.color = null;

    colorBox.hidden = !needColor;
    sizeBox.hidden = !needSize;

    if (needColor) {
      colorName.textContent = sel.color || 'choose one';
      swatchBox.innerHTML = colors.map((c) => {
        const ok = colorAvailable(c.name);
        return '<button type="button" class="swatch" style="--sw:' + esc(c.hex || '#999') + '" data-color="' + esc(c.name) + '"' +
          ' aria-pressed="' + (sel.color === c.name) + '" aria-label="' + esc(c.name) + (ok ? '' : ' (sold out)') + '"' +
          ' title="' + esc(c.name) + (ok ? '' : ' (sold out)') + '"' + (ok ? '' : ' disabled') + '></button>';
      }).join('');
    }

    if (needSize) {
      sizeBtnBox.innerHTML = sizes.map((s) => {
        const ok = sizeAvailable(s);
        return '<button type="button" class="size" data-size="' + esc(s) + '" aria-pressed="' + (sel.size === s) + '"' +
          (ok ? '' : ' disabled title="Sold out"') + '>' + esc(s) + '</button>';
      }).join('');
    }

    const v = chosenVariant();
    const max = v ? Math.min(v.stock, MAX_QTY) : MAX_QTY;
    qtyInput.max = String(max);
    if (Number(qtyInput.value) > max) qtyInput.value = String(Math.max(max, 1));

    // Stock line and button state
    stockLine.className = 'stock stock--line';
    addBtn.disabled = false;
    addBtn.textContent = 'Add to cart';

    if (totalStock === 0) {
      stockLine.textContent = 'Out of stock';
      stockLine.classList.add('stock--low');
      addBtn.disabled = true;
      addBtn.textContent = 'Out of stock';
    } else if (v) {
      if (v.stock === 0) {
        stockLine.textContent = 'Out of stock';
        stockLine.classList.add('stock--low');
        addBtn.disabled = true;
        addBtn.textContent = 'Out of stock';
      } else if (v.stock <= 5) {
        stockLine.textContent = 'Only ' + v.stock + ' left';
        stockLine.classList.add('stock--low');
      } else {
        stockLine.textContent = 'In stock';
        stockLine.classList.add('stock--ok');
      }
    } else {
      const missing = [needColor && !sel.color && 'a color', needSize && !sel.size && 'a size'].filter(Boolean);
      stockLine.textContent = 'Choose ' + missing.join(' and ') + ' to see availability';
    }
  }

  swatchBox.addEventListener('click', (e) => {
    const b = e.target.closest('[data-color]');
    if (!b) return;
    sel.color = b.dataset.color;
    msg.textContent = '';
    render();
  });

  sizeBtnBox.addEventListener('click', (e) => {
    const b = e.target.closest('[data-size]');
    if (!b) return;
    sel.size = b.dataset.size;
    msg.textContent = '';
    render();
  });

  // ---------- Quantity ----------
  const clampQty = () => {
    const max = Number(qtyInput.max) || MAX_QTY;
    const n = Math.floor(Number(qtyInput.value));
    qtyInput.value = String(Number.isFinite(n) ? Math.min(Math.max(n, 1), max) : 1);
  };
  $('#qty-minus').addEventListener('click', () => { qtyInput.value = String(Number(qtyInput.value) - 1); clampQty(); });
  $('#qty-plus').addEventListener('click', () => { qtyInput.value = String(Number(qtyInput.value) + 1); clampQty(); });
  qtyInput.addEventListener('change', clampQty);

  // ---------- Add to cart ----------
  function show(text, kind, html) {
    msg.className = 'msg msg--' + kind;
    if (html) msg.innerHTML = text; else msg.textContent = text;
  }

  addBtn.addEventListener('click', () => {
    const v = chosenVariant();
    if (!v) {
      const missing = [needColor && !sel.color && 'a color', needSize && !sel.size && 'a size'].filter(Boolean);
      show('Please choose ' + missing.join(' and ') + ' first.', 'error');
      return;
    }

    clampQty();
    const wanted = Number(qtyInput.value);
    const room = Math.min(v.stock, MAX_QTY) - Cart.qtyOf(v.id);   // how many more can be added

    if (room <= 0) {
      show('You already have the most available (' + Math.min(v.stock, MAX_QTY) + ') of this item in your cart. <a href="cart.php">View cart</a>', 'error', true);
      return;
    }

    const added = Math.min(wanted, room);
    Cart.add(v.id, added);

    const label = esc(product.name) + (describe(v) ? ' (' + esc(describe(v)) + ')' : '');
    let text = 'Added ' + added + ' &times; ' + label + ' to your cart. <a href="cart.php">View cart</a>';
    if (added < wanted) text = 'Only ' + added + ' more could be added (limit reached). ' + text;
    show(text, 'ok', true);
  });

  // ---------- More products from the same category ----------
  async function loadRelated() {
    try {
      const data = await getJSON(API_SEARCH + '?' + new URLSearchParams({ category: product.category_slug, limit: 5 }));
      const others = data.products.filter((p) => p.id !== product.id).slice(0, 4);
      if (!others.length) return;
      $('#related-grid').innerHTML = others.map(cardHTML).join('');
      $('#related').hidden = false;
    } catch (err) { /* related products are optional, ignore errors */ }
  }

  render();
  loadRelated();
})();
