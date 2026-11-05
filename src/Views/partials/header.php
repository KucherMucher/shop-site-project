<?php
use App\Core\Auth;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Page;

$navCategories = Category::allActive();
$authUser = Auth::user();
$cartCount = Cart::count();
$currentLocale = locale();
$isStaff = $authUser && in_array($authUser['role_slug'], ['admin', 'moderator'], true);
$isCompany = $authUser && $authUser['role_slug'] === 'company';
$pagesNav = Page::navPages();
$promoPage = Page::findBySlug('promocoes');
$promoActive = !$promoPage || (bool) $promoPage['active'];
?>
<div class="utility-bar">
    <div class="container utility-bar__inner">
        <p class="utility-bar__shipping"><?= icon('truck', '', 16) ?> <?= e(t('common.free_shipping', ['amount' => money(60)])) ?></p>
        <div class="utility-bar__links">
            <a href="<?= url('/suporte') ?>"><?= icon('headset', '', 15) ?> <?= e(t('nav.support')) ?></a>
            <?php foreach (array_slice($pagesNav, 0, 2) as $navPage): ?>
                <a href="<?= url('/pagina/' . $navPage['slug']) ?>"><?= e(localized($navPage, 'title')) ?></a>
            <?php endforeach; ?>
            <div class="locale-switch" aria-label="<?= e(t('nav.language')) ?>">
                <a href="<?= url('/idioma/pt') ?>" class="<?= $currentLocale === 'pt' ? 'is-active' : '' ?>" hreflang="pt">PT</a>
                <span></span>
                <a href="<?= url('/idioma/en') ?>" class="<?= $currentLocale === 'en' ? 'is-active' : '' ?>" hreflang="en">EN</a>
            </div>
            <button class="theme-switch" type="button" data-theme-toggle aria-label="<?= e(t('nav.theme')) ?>" title="<?= e(t('nav.theme')) ?>">
                <span class="theme-switch__sun"><?= icon('sun', '', 15) ?></span>
                <span class="theme-switch__moon"><?= icon('moon', '', 15) ?></span>
                <span class="theme-switch__track"><span class="theme-switch__thumb"></span></span>
            </button>
        </div>
    </div>
</div>

<header class="site-header" data-header>
    <div class="container header-main">
        <button class="icon-btn menu-toggle" type="button" data-menu-open aria-label="<?= e(t('nav.menu')) ?>" aria-expanded="false" aria-controls="mobile-menu">
            <?= icon('menu', '', 24) ?>
        </button>

        <a class="logo" href="<?= url('/') ?>" aria-label="TRIVEX — <?= e(t('nav.home')) ?>">
            <span class="logo__mark">
                <svg viewBox="0 0 40 40" width="38" height="38" aria-hidden="true">
                    <path d="M20 3 36 34H20Z" fill="#0b6bff"/>
                    <path d="M20 3 4 34h16Z" fill="#062a4d"/>
                    <path d="M12 34h16l-8-14Z" fill="#4fe0ff"/>
                    <path d="M20 10v17" stroke="#fff" stroke-opacity=".28" stroke-width="1.5"/>
                </svg>
            </span>
            <span class="logo__text">
                <strong>TRIVEX</strong>
                <small>swim · bike · run</small>
            </span>
        </a>

        <form class="header-search" action="<?= url('/pesquisa') ?>" method="get" role="search" data-search-form>
            <?= icon('search', 'header-search__icon', 20) ?>
            <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="<?= e(t('nav.search_placeholder')) ?>" autocomplete="off" aria-label="<?= e(t('nav.search')) ?>" data-search-input>
            <button type="submit" class="btn btn--primary btn--sm"><?= e(t('common.search')) ?></button>
            <div class="search-suggestions" data-search-results hidden>
                <div class="search-suggestions__body" data-search-body></div>
                <a class="search-suggestions__all" href="<?= url('/pesquisa') ?>" data-search-all><?= e(t('shop.view_all_results')) ?> <?= icon('arrow-right', '', 15) ?></a>
            </div>
        </form>

        <div class="header-actions">
            <div class="account-menu">
                <?php if ($authUser): ?>
                    <button class="account-trigger" type="button" data-account-toggle aria-expanded="false">
                        <span class="avatar avatar--sm"><?= e(initials($authUser['name'])) ?></span>
                        <span class="account-trigger__text">
                            <small><?= e(t('account.hello', ['name' => explode(' ', $authUser['name'])[0]])) ?></small>
                            <strong><?= e($authUser['role_slug'] === 'company' ? t('nav.company') : ($isStaff ? t('admin.title') : t('nav.account'))) ?></strong>
                        </span>
                        <?= icon('chevron-down', '', 15) ?>
                    </button>
                    <div class="account-dropdown" data-account-dropdown>
                        <div class="account-dropdown__head">
                            <strong><?= e($authUser['name']) ?></strong>
                            <span><?= e($authUser['email']) ?></span>
                        </div>
                        <?php if ($isStaff): ?>
                            <a href="<?= url('/admin') ?>"><?= icon('dashboard', '', 17) ?> <?= e(t('nav.admin')) ?></a>
                        <?php endif; ?>
                        <?php if ($isCompany): ?>
                            <a href="<?= url('/empresa/painel') ?>"><?= icon('building', '', 17) ?> <?= e(t('nav.company')) ?></a>
                        <?php endif; ?>
                        <a href="<?= url('/conta') ?>"><?= icon('user', '', 17) ?> <?= e(t('nav.account')) ?></a>
                        <a href="<?= url('/conta/encomendas') ?>"><?= icon('package', '', 17) ?> <?= e(t('account.nav_orders')) ?></a>
                        <a href="<?= url('/suporte') ?>"><?= icon('chat', '', 17) ?> <?= e(t('nav.support')) ?></a>
                        <form action="<?= url('/logout') ?>" method="post">
                            <?= csrf_field() ?>
                            <button type="submit" class="account-dropdown__logout"><?= icon('logout', '', 17) ?> <?= e(t('nav.logout')) ?></button>
                        </form>
                    </div>
                <?php else: ?>
                    <a class="account-trigger account-trigger--guest" href="<?= url('/login') ?>">
                        <?= icon('user', '', 22) ?>
                        <span class="account-trigger__text">
                            <small><?= e(t('nav.hello')) ?></small>
                            <strong><?= e(t('nav.login')) ?></strong>
                        </span>
                    </a>
                <?php endif; ?>
            </div>

            <a class="cart-trigger" href="<?= url('/carrinho') ?>" aria-label="<?= e(t('nav.cart')) ?>">
                <?= icon('cart', '', 24) ?>
                <span class="cart-trigger__count <?= $cartCount === 0 ? 'is-empty' : '' ?>" data-cart-count><?= $cartCount ?></span>
            </a>
        </div>
    </div>

    <nav class="main-nav" aria-label="<?= e(t('nav.shop')) ?>">
        <div class="container main-nav__inner">
            <div class="shop-menu" data-shop-menu>
                <button class="shop-menu__trigger" type="button" data-shop-toggle aria-expanded="false">
                    <?= icon('grid', '', 17) ?> <?= e(t('nav.shop')) ?> <?= icon('chevron-down', '', 14) ?>
                </button>
                <div class="mega-menu" data-shop-panel>
                    <div class="mega-menu__head">
                        <span><?= e(t('home.categories_title')) ?></span>
                        <a href="<?= url('/loja') ?>"><?= e(t('nav.all')) ?> <?= icon('arrow-right', '', 14) ?></a>
                    </div>
                    <div class="mega-menu__grid">
                        <?php foreach ($navCategories as $cat): ?>
                            <a class="mega-menu__item" href="<?= url('/categoria/' . $cat['slug']) ?>">
                                <span class="mega-menu__thumb"><img src="<?= asset('img/' . $cat['image']) ?>" alt="" loading="lazy"></span>
                                <span>
                                    <strong><?= e(localized($cat, 'name')) ?></strong>
                                    <small><?= (int) $cat['products_count'] ?> <?= e(t('common.results')) ?></small>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php foreach ($navCategories as $cat): ?>
                <a class="main-nav__link <?= is_route('/categoria/' . $cat['slug']) ? 'is-active' : '' ?>" href="<?= url('/categoria/' . $cat['slug']) ?>">
                    <?= e(localized($cat, 'name')) ?>
                </a>
            <?php endforeach; ?>

            <span class="main-nav__spacer"></span>
            <?php if ($promoActive): ?>
                <a class="main-nav__link main-nav__link--spooky" href="<?= url('/promocoes') ?>">
                    <?= icon('ghost', '', 16) ?> <?= e(t('nav.promotions')) ?>
                    <span class="nav-dot"></span>
                </a>
            <?php endif; ?>
            <a class="main-nav__link main-nav__link--support" href="<?= url('/suporte') ?>"><?= icon('headset', '', 16) ?> <?= e(t('nav.support')) ?></a>
        </div>
    </nav>
</header>

<div class="mobile-menu" id="mobile-menu" data-mobile-menu aria-hidden="true">
    <div class="mobile-menu__backdrop" data-menu-close></div>
    <div class="mobile-menu__panel">
        <div class="mobile-menu__head">
            <a class="logo" href="<?= url('/') ?>">
                <span class="logo__mark"><svg viewBox="0 0 40 40" width="34" height="34"><path d="M20 3 36 34H20Z" fill="#0b6bff"/><path d="M20 3 4 34h16Z" fill="#062a4d"/><path d="M12 34h16l-8-14Z" fill="#4fe0ff"/></svg></span>
                <span class="logo__text"><strong>TRIVEX</strong><small>swim · bike · run</small></span>
            </a>
            <button class="icon-btn" type="button" data-menu-close aria-label="<?= e(t('nav.close')) ?>"><?= icon('close', '', 22) ?></button>
        </div>
        <div class="mobile-menu__account">
            <?php if ($authUser): ?>
                <span class="avatar"><?= e(initials($authUser['name'])) ?></span>
                <span><small><?= e(t('account.hello', ['name' => explode(' ', $authUser['name'])[0]])) ?></small><strong><?= e($authUser['name']) ?></strong></span>
            <?php else: ?>
                <span class="avatar avatar--ghost"><?= icon('user', '', 20) ?></span>
                <span><small><?= e(t('nav.hello')) ?></small><strong><?= e(t('nav.login')) ?></strong></span>
            <?php endif; ?>
        </div>
        <nav class="mobile-menu__nav">
            <span class="mobile-menu__label"><?= e(t('nav.shop')) ?></span>
            <?php foreach ($navCategories as $cat): ?>
                <a href="<?= url('/categoria/' . $cat['slug']) ?>"><?= e(localized($cat, 'name')) ?> <?= icon('chevron-right', '', 16) ?></a>
            <?php endforeach; ?>
            <span class="mobile-menu__label"><?= e(t('nav.account')) ?></span>
            <?php if ($authUser): ?>
                <a href="<?= url('/conta') ?>"><?= icon('user', '', 18) ?> <?= e(t('nav.account')) ?></a>
                <a href="<?= url('/conta/encomendas') ?>"><?= icon('package', '', 18) ?> <?= e(t('account.nav_orders')) ?></a>
                <?php if ($isStaff): ?><a href="<?= url('/admin') ?>"><?= icon('dashboard', '', 18) ?> <?= e(t('nav.admin')) ?></a><?php endif; ?>
                <?php if ($isCompany): ?><a href="<?= url('/empresa/painel') ?>"><?= icon('building', '', 18) ?> <?= e(t('nav.company')) ?></a><?php endif; ?>
            <?php else: ?>
                <a href="<?= url('/login') ?>"><?= icon('user', '', 18) ?> <?= e(t('nav.login')) ?></a>
                <a href="<?= url('/registo') ?>"><?= icon('sparkles', '', 18) ?> <?= e(t('nav.register')) ?></a>
            <?php endif; ?>
            <a href="<?= url('/suporte') ?>"><?= icon('headset', '', 18) ?> <?= e(t('nav.support')) ?></a>
            <?php if ($promoActive): ?><a href="<?= url('/promocoes') ?>" class="is-spooky"><?= icon('ghost', '', 18) ?> <?= e(t('nav.promotions')) ?></a><?php endif; ?>
        </nav>
        <div class="mobile-menu__footer">
            <div class="locale-switch">
                <a class="<?= $currentLocale === 'pt' ? 'is-active' : '' ?>" href="<?= url('/idioma/pt') ?>">PT</a><span></span><a class="<?= $currentLocale === 'en' ? 'is-active' : '' ?>" href="<?= url('/idioma/en') ?>">EN</a>
            </div>
            <button class="btn btn--ghost btn--sm" type="button" data-theme-toggle><?= icon('sun', 'theme-sun', 16) ?><?= icon('moon', 'theme-moon', 16) ?> <?= e(t('nav.theme')) ?></button>
        </div>
    </div>
</div>
