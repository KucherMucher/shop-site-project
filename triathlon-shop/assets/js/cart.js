/* Cart page: shows the cart saved in localStorage, with current prices and stock from the server. */
(() => {
  'use strict';

  const { esc, money, getJSON, Cart, MAX_QTY } = window.Shop;

  // Delivery rule used in this demo.
  const FREE_DELIVERY_FROM = 60;
  const DELIVERY_FEE = 4.9;

  const root = document.getElementById('cart-root');

  let details = new Map();      // variant id -> details from api/cart.php
  let notes = [];               // messages about changes made to the cart
  let loading = false;

  const maxFor = (d) => Math.min(d.stock, MAX_QTY);

  // ---------- Load details from the server and fix the cart if needed ----------
  async function load() {
    const items = Cart.items();
    if (!items.length) { details = new Map(); render(); return; }

    loading = true;
    try {
      const data = await getJSON('api/cart.php?' + new URLSearchParams({ ids: items.map((i) => i.v).join(',') }));
      details = new Map(data.items.map((d) => [d.variant_id, d]));

      for (const item of items) {
        const d = details.get(item.v);
        if (!d) {
          Cart.remove(item.v);
          notes.push('An item in your cart is no longer sold and was removed.');
        } else if (d.stock === 0) {
          Cart.remove(item.v);
          notes.push(d.name + ' is out of stock and was removed from your cart.');
        } else if (item.q > maxFor(d)) {
          Cart.setQty(item.v, maxFor(d));
          notes.push('Only ' + maxFor(d) + ' of ' + d.name + ' are available, so the quantity was reduced.');
        }
      }
    } catch (err) {
      loading = false;
      root.innerHTML = '<div class="state"><h3>We could not load your cart</h3>' +
        '<p>Check that MySQL is running and that database/schema.sql has been imported. Your items are still saved.</p></div>';
      return;
    }
    loading = false;
    render();
  }

  // ---------- Draw the cart ----------
  function optionsText(d) {
    return [d.color && 'Color: ' + d.color, d.size && 'Size: ' + d.size].filter(Boolean).join(' \u00B7 ');
  }

  function render() {
    const items = Cart.items();

    // A line without details (for example added in another tab) needs a fresh load.
    if (items.some((i) => !details.has(i.v))) { if (!loading) load(); return; }

    const notesHTML = notes.length
      ? '<div class="msg msg--error" role="alert">' + notes.map(esc).join('<br>') + '</div>'
      : '';

    if (!items.length) {
      root.innerHTML = notesHTML +
        '<div class="state"><h3>Your cart is empty</h3><p>Find something for your next race.</p>' +
        '<a class="btn" href="index.php">Browse gear</a></div>';
      return;
    }

    let subtotal = 0, units = 0;
    const rows = items.map((item) => {
      const d = details.get(item.v);
      const line = d.price * item.q;
      subtotal += line;
      units += item.q;
      const max = maxFor(d);

      return '<li class="line" data-v="' + d.variant_id + '">' +
        '<a class="line__img" href="product.php?id=' + d.product_id + '">' +
          (d.image ? '<img src="' + esc(d.image) + '" alt="" width="160" height="120">' : '') + '</a>' +
        '<div class="line__info">' +
          '<a class="line__name" href="product.php?id=' + d.product_id + '">' + esc(d.name) + '</a>' +
          '<p class="line__meta">' + esc(d.brand) + (optionsText(d) ? ' &middot; ' + esc(optionsText(d)) : '') + '</p>' +
          '<div class="line__ctrl">' +
            '<div class="qty qty--small">' +
              '<button type="button" data-act="minus" aria-label="Decrease quantity of ' + esc(d.name) + '">&minus;</button>' +
              '<input type="number" min="1" max="' + max + '" value="' + item.q + '" inputmode="numeric" data-act="set" aria-label="Quantity of ' + esc(d.name) + '">' +
              '<button type="button" data-act="plus" aria-label="Increase quantity of ' + esc(d.name) + '"' + (item.q >= max ? ' disabled' : '') + '>+</button>' +
            '</div>' +
            '<button type="button" class="link-btn" data-act="remove">Remove</button>' +
          '</div>' +
          (item.q >= max ? '<p class="line__limit">Maximum available: ' + max + '</p>' : '') +
        '</div>' +
        '<div class="line__price"><strong>' + money.format(line) + '</strong>' +
          (item.q > 1 ? '<span class="muted">' + money.format(d.price) + ' each</span>' : '') +
          (d.old_price ? '<span class="price__old">' + money.format(d.old_price) + '</span>' : '') +
        '</div>' +
      '</li>';
    }).join('');

    const delivery = subtotal >= FREE_DELIVERY_FROM ? 0 : DELIVERY_FEE;
    const hint = delivery > 0
      ? '<p class="summary__hint">Add ' + money.format(FREE_DELIVERY_FROM - subtotal) + ' more for free delivery.</p>'
      : '<p class="summary__hint summary__hint--ok">You get free delivery.</p>';

    root.innerHTML = notesHTML +
      '<p class="muted cart__count">' + units + (units === 1 ? ' item' : ' items') + '</p>' +
      '<div class="cart__layout">' +
        '<ul class="lines">' + rows + '</ul>' +
        '<aside class="summary" aria-label="Order summary">' +
          '<h2>Summary</h2>' +
          '<dl>' +
            '<div><dt>Subtotal</dt><dd>' + money.format(subtotal) + '</dd></div>' +
            '<div><dt>Delivery</dt><dd>' + (delivery ? money.format(delivery) : 'Free') + '</dd></div>' +
            '<div class="summary__total"><dt>Total</dt><dd>' + money.format(subtotal + delivery) + '</dd></div>' +
          '</dl>' +
          hint +
          '<button type="button" class="link-btn" data-act="clear">Empty cart</button>' +
        '</aside>' +
      '</div>';
  }

  // ---------- Events ----------
  root.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-act]');
    if (!btn) return;
    const act = btn.dataset.act;

    if (act === 'clear') {
      if (window.confirm('Remove everything from your cart?')) { notes = []; Cart.clear(); }
      return;
    }

    const li = btn.closest('.line');
    if (!li) return;
    const v = Number(li.dataset.v);
    const d = details.get(v);
    if (!d) return;
    notes = [];

    if (act === 'remove') Cart.remove(v);
    else if (act === 'minus') Cart.setQty(v, Cart.qtyOf(v) - 1);
    else if (act === 'plus') Cart.setQty(v, Math.min(Cart.qtyOf(v) + 1, maxFor(d)));
  });

  root.addEventListener('change', (e) => {
    if (e.target.dataset.act !== 'set') return;
    const v = Number(e.target.closest('.line').dataset.v);
    const d = details.get(v);
    if (!d) return;
    const n = Math.floor(Number(e.target.value));
    notes = [];
    Cart.setQty(v, Number.isFinite(n) ? Math.min(n, maxFor(d)) : Cart.qtyOf(v));   // 0 or less removes the line
    render();                                                                         // redraw even if nothing changed
  });

  // The cart changed (here or in another tab).
  document.addEventListener('cart:change', () => { if (!loading) render(); });

  load();
})();
