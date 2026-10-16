<?php
declare(strict_types=1);

$page  = 'cart';
$title = 'Your cart | Transition';
require __DIR__ . '/partials/header.php';
?>

  <main id="main" class="wrap cart">
    <h1 class="cart__title">Your cart</h1>
    <noscript><p class="msg msg--error">JavaScript is needed to show the cart.</p></noscript>
    <div id="cart-root" aria-live="polite"><p class="muted">Loading your cart...</p></div>
  </main>

<?php
$scripts = ['assets/js/cart.js'];
require __DIR__ . '/partials/footer.php';
