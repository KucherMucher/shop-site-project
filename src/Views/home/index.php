<?php
$heroProduct = $featured[0] ?? null;
$heroDiscount = $heroProduct ? discount_percent($heroProduct) : 30;
?>
<section class="hero">
    <div class="container">
        <div class="hero__panel">
            <div class="hero__bg" aria-hidden="true">
                <svg viewBox="0 0 1440 700" preserveAspectRatio="xMidYMid slice">
                    <defs>
                        <linearGradient id="lane1" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#4fe0ff" stop-opacity=".25"/><stop offset="1" stop-color="#4fe0ff" stop-opacity="0"/></linearGradient>
                        <linearGradient id="lane2" x1="1" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ff5a3c" stop-opacity=".18"/><stop offset="1" stop-color="#ff5a3c" stop-opacity="0"/></linearGradient>
                    </defs>
                    <path d="M-100 620 Q300 380 720 520 T1540 360" fill="none" stroke="url(#lane1)" stroke-width="90"/>
                    <path d="M-60 690 Q320 460 760 590 T1500 440" fill="none" stroke="url(#lane2)" stroke-width="40"/>
                    <g stroke="#fff" stroke-opacity=".06" stroke-width="1.5" fill="none">
                        <path d="M0 200h1440M0 300h1440M0 400h1440M0 500h1440"/>
                        <path d="M200 0v700M400 0v700M600 0v700M800 0v700M1000 0v700M1200 0v700"/>
                    </g>
                    <circle cx="1240" cy="120" r="260" fill="#0b6bff" opacity=".18"/>
                    <circle cx="180" cy="620" r="200" fill="#4fe0ff" opacity=".08"/>
                </svg>
            </div>

            <div class="hero__content">
                <span class="eyebrow eyebrow--light hero__eyebrow"><span class="pulse-dot"></span> <?= e(t('home.hero_eyebrow')) ?></span>
                <h1><?= t('home.hero_title') ?></h1>
                <p><?= e(t('home.hero_subtitle')) ?></p>
                <div class="hero__actions">
                    <a class="btn btn--accent btn--lg" href="<?= url('/loja') ?>"><?= e(t('home.hero_cta')) ?> <?= icon('arrow-right', '', 18) ?></a>
                    <a class="btn btn--glass btn--lg" href="#categories"><?= icon('grid', '', 17) ?> <?= e(t('home.hero_cta_secondary')) ?></a>
                </div>
                <div class="hero__stats">
                    <div><strong><?= (int) $stats['products'] ?>+</strong><span><?= e(t('home.hero_stat_1')) ?></span></div>
                    <div><strong><?= number_format((int) $stats['athletes'], 0, ',', '.') ?></strong><span><?= e(t('home.hero_stat_2')) ?></span></div>
                    <div><strong><?= number_format((float) $stats['rating'], 1, locale() === 'en' ? '.' : ',', '') ?> <svg viewBox="0 0 24 24" width="16" height="16" fill="#ffd166"><path d="M12 2.6l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.4l-5.8 3.1 1.1-6.5L2.6 9.4l6.5-.9L12 2.6z"/></svg></strong><span><?= e(t('home.hero_stat_3')) ?></span></div>
                </div>
            </div>

            <div class="hero__visual">
                <div class="hero-card">
                    <span class="hero-card__tag"><?= e(t('home.hero_eyebrow')) ?></span>
                    <a href="<?= $heroProduct ? url('/produto/' . $heroProduct['slug']) : url('/loja') ?>">
                        <img src="<?= asset('img/' . ($heroProduct['image'] ?? 'products/bike-road.svg')) ?>" alt="<?= e($heroProduct ? localized($heroProduct, 'name') : 'TRIVEX') ?>">
                    </a>
                    <div class="hero-card__caption">
                        <small><?= e($heroProduct['brand'] ?? 'Canyon') ?></small>
                        <strong><?= e($heroProduct ? localized($heroProduct, 'name') : 'Aero CF 8') ?></strong>
                        <span><?= $heroProduct ? money(product_price($heroProduct)) : money(2599) ?></span>
                    </div>
                </div>
                <div class="hero-chip hero-chip--sale">
                    <span class="hero-chip__icon"><?= icon('tag', '', 18) ?></span>
                    <span><small><?= e(t('promo.deal')) ?></small><strong>−<?= $heroDiscount ?>%</strong></span>
                </div>
                <div class="hero-chip hero-chip--rating">
                    <span class="hero-chip__icon"><?= icon('shield-check', '', 18) ?></span>
                    <span><small><?= e(t('home.hero_stat_3')) ?></small><strong>4.9 / 5</strong></span>
                </div>
                <div class="hero__float hero__float--one"></div>
                <div class="hero__float hero__float--two"></div>
            </div>

            <div class="hero__disciplines" aria-hidden="true">
                <span><b>01</b> SWIM</span><span><b>02</b> BIKE</span><span><b>03</b> RUN</span>
            </div>
        </div>
    </div>
</section>

<section class="trust-bar">
    <div class="container trust-bar__grid">
        <div class="trust-item">
            <span class="trust-item__icon"><?= icon('truck', '', 22) ?></span>
            <div><strong><?= e(t('home.trust_1_title')) ?></strong><p><?= e(t('home.trust_1_text')) ?></p></div>
        </div>
        <div class="trust-item">
            <span class="trust-item__icon"><?= icon('headset', '', 22) ?></span>
            <div><strong><?= e(t('home.trust_2_title')) ?></strong><p><?= e(t('home.trust_2_text')) ?></p></div>
        </div>
        <div class="trust-item">
            <span class="trust-item__icon"><?= icon('refresh', '', 22) ?></span>
            <div><strong><?= e(t('home.trust_3_title')) ?></strong><p><?= e(t('home.trust_3_text')) ?></p></div>
        </div>
        <div class="trust-item">
            <span class="trust-item__icon"><?= icon('shield-check', '', 22) ?></span>
            <div><strong><?= e(t('home.trust_4_title')) ?></strong><p><?= e(t('home.trust_4_text')) ?></p></div>
        </div>
    </div>
</section>

<section class="section categories-section" id="categories">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow"><?= icon('grid', '', 13) ?> <?= e(t('home.categories_eyebrow')) ?></span>
                <h2><?= e(t('home.categories_title')) ?></h2>
                <p><?= e(t('home.categories_subtitle')) ?></p>
            </div>
            <a class="link-arrow" href="<?= url('/loja') ?>"><?= e(t('nav.all')) ?> <?= icon('arrow-right', '', 16) ?></a>
        </div>
        <div class="category-grid">
            <?php foreach ($categories as $index => $category): ?>
                <a class="category-card <?= $index === 0 ? 'category-card--wide' : '' ?>" href="<?= url('/categoria/' . $category['slug']) ?>" data-reveal>
                    <img src="<?= asset('img/' . $category['image']) ?>" alt="" loading="lazy">
                    <span class="category-card__shade"></span>
                    <span class="category-card__content">
                        <span class="category-card__count"><?= (int) $category['products_count'] ?> <?= e(t('common.results')) ?></span>
                        <strong><?= e(localized($category, 'name')) ?></strong>
                        <small><?= e(mb_strimwidth(localized($category, 'description'), 0, 70, '…')) ?></small>
                        <span class="category-card__cta"><?= e(t('nav.all')) ?> <?= icon('arrow-right', '', 14) ?></span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--tint">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow"><?= icon('heart', '', 13) ?> <?= e(t('home.featured_eyebrow')) ?></span>
                <h2><?= e(t('home.featured_title')) ?></h2>
                <p><?= e(t('home.featured_subtitle')) ?></p>
            </div>
            <a class="link-arrow" href="<?= url('/pesquisa?sort=rating') ?>"><?= e(t('nav.all')) ?> <?= icon('arrow-right', '', 16) ?></a>
        </div>
        <div class="product-grid product-grid--4">
            <?php foreach ($featured as $product): ?>
                <?php require BASE_PATH . '/src/Views/partials/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="promo-banner" data-reveal>
            <div class="promo-banner__art" aria-hidden="true">
                <span class="promo-moon"></span>
                <span class="promo-ghost promo-ghost--1"><?= icon('ghost', '', 120) ?></span>
                <span class="promo-ghost promo-ghost--2"><?= icon('ghost', '', 64) ?></span>
                <span class="promo-bat">🦇</span>
                <span class="promo-bat promo-bat--2">🦇</span>
                <span class="promo-pumpkin">🎃</span>
            </div>
            <div class="promo-banner__copy">
                <span class="eyebrow eyebrow--light"><?= icon('ghost', '', 14) ?> <?= e(t('home.promo_eyebrow')) ?></span>
                <h2><?= e(t('home.promo_title')) ?></h2>
                <p><?= e(t('home.promo_text')) ?></p>
                <a class="btn btn--accent btn--lg" href="<?= url('/promocoes') ?>"><?= icon('ghost', '', 17) ?> <?= e(t('home.promo_cta')) ?></a>
            </div>
            <div class="promo-banner__deals">
                <?php foreach (array_slice($promotions, 0, 2) as $promoProduct): ?>
                    <a class="promo-mini" href="<?= url('/produto/' . $promoProduct['slug']) ?>">
                        <img src="<?= asset('img/' . $promoProduct['image']) ?>" alt="" loading="lazy">
                        <span><small><?= e($promoProduct['brand']) ?></small><strong><?= e(localized($promoProduct, 'name')) ?></strong><em>−<?= discount_percent($promoProduct) ?>%</em></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow"><?= icon('sparkles', '', 13) ?> <?= e(t('home.new_eyebrow')) ?></span>
                <h2><?= e(t('home.new_title')) ?></h2>
                <p><?= e(t('home.new_subtitle')) ?></p>
            </div>
            <a class="link-arrow" href="<?= url('/pesquisa?sort=newest') ?>"><?= e(t('nav.all')) ?> <?= icon('arrow-right', '', 16) ?></a>
        </div>
        <div class="product-grid product-grid--4">
            <?php foreach (array_slice($newest, 0, 4) as $product): ?>
                <?php require BASE_PATH . '/src/Views/partials/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="editorial">
            <div class="editorial__media" data-reveal>
                <img src="<?= asset('img/products/running-shoes-3.svg') ?>" alt="" loading="lazy">
                <div class="editorial__badge">
                    <strong>04</strong>
                    <span><?= e(t('home.editorial_eyebrow')) ?></span>
                </div>
            </div>
            <div class="editorial__copy" data-reveal>
                <span class="eyebrow"><?= icon('target', '', 13) ?> <?= e(t('home.editorial_eyebrow')) ?></span>
                <h2><?= e(t('home.editorial_title')) ?></h2>
                <p><?= e(t('home.editorial_text')) ?></p>
                <ol class="editorial__steps">
                    <li><b>01</b><span><strong>Swim</strong><?= e(t('home.trust_1_text')) ?></span></li>
                    <li><b>02</b><span><strong>Bike</strong><?= e(t('home.trust_2_text')) ?></span></li>
                    <li><b>03</b><span><strong>Run</strong><?= e(t('home.trust_3_text')) ?></span></li>
                </ol>
                <a class="btn btn--primary" href="<?= url('/pagina/guia-transicao') ?>"><?= e(t('home.editorial_cta')) ?> <?= icon('arrow-right', '', 16) ?></a>
            </div>
        </div>
    </div>
</section>
