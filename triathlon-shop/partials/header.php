<?php
declare(strict_types=1);
/**
 * Shared page top: <head>, header with search + cart link, category bar.
 * Set these variables before including this file:
 *   $page            'home' | 'product' | 'cart'
 *   $title           page title
 *   $description     (optional) meta description
 *   $activeCategory  (optional) category slug to highlight
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

$description    = $description ?? 'Triathlon gear for all three disciplines: wetsuits, helmets, running shoes, nutrition and tech.';
$activeCategory = $activeCategory ?? '';

$categories = [];
$dbProblem  = false;
try {
    $categories = db()->query('SELECT slug, name FROM categories ORDER BY sort_order')->fetchAll();
} catch (PDOException $ex) {
    $dbProblem = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?></title>
  <meta name="description" content="<?= e($description) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;800&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-page="<?= e($page) ?>">
  <a class="skip" href="#main">Skip to content</a>

  <header class="site-header">
    <div class="wrap header-row">
      <a class="logo" href="index.php" aria-label="Transition, home">
        <span class="logo__mark" aria-hidden="true"><i></i><i></i><i></i></span>
        Transition
      </a>

      <form id="search-form" class="search" action="index.php" method="get" role="search">
        <label class="sr-only" for="search-input">Search products</label>
        <input id="search-input" name="q" type="search" autocomplete="off"
               placeholder="Search wetsuits, helmets, gels, shoes..."
               role="combobox" aria-expanded="false" aria-controls="suggestions" aria-autocomplete="list">
        <button type="submit" class="search__btn">Search</button>
        <ul id="suggestions" class="suggestions" role="listbox" hidden></ul>
      </form>

      <a class="cart-link" href="cart.php" aria-label="Cart">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.4 11h10.2L20 7H6.2"/><circle cx="9" cy="19.5" r="1.4"/><circle cx="17" cy="19.5" r="1.4"/></svg>
        <span class="cart-link__text">Cart</span>
        <span class="cart-count" data-cart-count hidden>0</span>
      </a>
    </div>

    <nav class="wrap cats" aria-label="Categories">
      <a class="cat" href="index.php" data-category="" <?= $activeCategory === '' && $page === 'home' ? 'aria-current="true"' : '' ?>>All gear</a>
      <?php foreach ($categories as $c): ?>
        <a class="cat" href="index.php?category=<?= e($c['slug']) ?>" data-category="<?= e($c['slug']) ?>"
           <?= $activeCategory === $c['slug'] ? 'aria-current="true"' : '' ?>><?= e($c['name']) ?></a>
      <?php endforeach; ?>
    </nav>
  </header>

  <?php if ($dbProblem): ?>
    <div class="notice" role="alert">
      The database is not reachable. Start MySQL, import <code>database/schema.sql</code> and check <code>config/db.php</code>.
    </div>
  <?php endif; ?>
