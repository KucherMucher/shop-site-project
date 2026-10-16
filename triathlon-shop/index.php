<?php
declare(strict_types=1);

$page  = 'home';
$title = 'Transition | Triathlon gear for swim, bike and run';
require __DIR__ . '/partials/header.php';
?>

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

<?php
$scripts = ['assets/js/app.js'];
require __DIR__ . '/partials/footer.php';
