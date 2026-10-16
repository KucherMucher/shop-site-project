<?php
declare(strict_types=1);
require __DIR__ . '/config/db.php';

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

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
  <title>Transition | Triathlon gear for swim, bike and run</title>
  <meta name="description" content="Triathlon gear for all three disciplines: wetsuits, helmets, running shoes, nutrition and tech.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;800&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <a class="skip" href="#main">Skip to products</a>

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
    </div>

    <nav class="wrap cats" aria-label="Categories">
      <button type="button" class="cat" data-category="" aria-pressed="true">All gear</button>
      <?php foreach ($categories as $c): ?>
        <button type="button" class="cat" data-category="<?= e($c['slug']) ?>" aria-pressed="false"><?= e($c['name']) ?></button>
      <?php endforeach; ?>
    </nav>
  </header>

  <?php if ($dbProblem): ?>
    <div class="notice" role="alert">
      The database is not reachable. Start MySQL, import <code>database/schema.sql</code> and check <code>config/db.php</code>.
    </div>
  <?php endif; ?>

  <section class="hero">
    <div class="wrap">
      <h1>Swim. Bike. Run.</h1>
      <p class="hero__lead">Everything you need from the start line to the finish, with a quick change in between.</p>

      <div class="course" role="group" aria-label="Shop by discipline">
        <button type="button" class="course__leg course__leg--swim" data-category="swim">
          <span class="course__name">Swim</span>
          <span class="course__dist">1.5 km</span>
        </button>
        <span class="course__t" aria-hidden="true">T1</span>
        <button type="button" class="course__leg course__leg--bike" data-category="bike">
          <span class="course__name">Bike</span>
          <span class="course__dist">40 km</span>
        </button>
        <span class="course__t" aria-hidden="true">T2</span>
        <button type="button" class="course__leg course__leg--run" data-category="run">
          <span class="course__name">Run</span>
          <span class="course__dist">10 km</span>
        </button>
      </div>
    </div>
  </section>

  <main id="main" class="wrap">
    <section id="results" aria-live="polite" hidden>
      <div class="section-head">
        <div>
          <h2 id="results-title"></h2>
          <p id="results-count" class="muted"></p>
        </div>
        <div class="sort">
          <label for="sort-select">Sort by</label>
          <select id="sort-select">
            <option value="relevance">Best match</option>
            <option value="price_asc">Price: low to high</option>
            <option value="price_desc">Price: high to low</option>
            <option value="name">Name: A to Z</option>
          </select>
        </div>
      </div>
      <div id="results-grid" class="grid"></div>
    </section>

    <section id="featured">
      <div class="section-head">
        <h2>Race-day picks</h2>
      </div>
      <div id="featured-grid" class="grid"></div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="wrap">Transition is a school project. Products and prices are demo data.</div>
  </footer>

  <script src="assets/js/app.js"></script>
</body>
</html>
