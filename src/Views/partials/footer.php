<?php
use App\Core\Auth;
use App\Models\Category;
use App\Models\Page;

$footerCategories = Category::allActive();
$footerPages = Page::navPages();
$footerPromo = Page::findBySlug('promocoes');
$footerPromoActive = !$footerPromo || (bool) $footerPromo['active'];
$footerAuth = Auth::user();
?>
<section class="newsletter-band">
    <div class="container newsletter-band__inner">
        <div class="newsletter-band__copy">
            <span class="eyebrow eyebrow--light"><?= icon('sparkles', '', 14) ?> TRIVEX COMMUNITY</span>
            <h2><?= e(t('home.newsletter_title')) ?></h2>
            <p><?= e(t('home.newsletter_text')) ?></p>
        </div>
        <form class="newsletter-form" action="<?= url('/newsletter') ?>" method="post">
            <?= csrf_field() ?>
            <label class="sr-only" for="newsletter-email"><?= e(t('common.email')) ?></label>
            <input id="newsletter-email" type="email" name="email" required placeholder="<?= e(t('home.newsletter_placeholder')) ?>">
            <button type="submit" class="btn btn--accent"><?= e(t('home.newsletter_cta')) ?> <?= icon('arrow-right', '', 16) ?></button>
        </form>
        <p class="newsletter-band__note"><?= e(t('home.newsletter_note')) ?></p>
    </div>
</section>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a class="logo logo--light" href="<?= url('/') ?>">
                    <span class="logo__mark">
                        <svg viewBox="0 0 40 40" width="38" height="38" aria-hidden="true"><path d="M20 3 36 34H20Z" fill="#4fe0ff"/><path d="M20 3 4 34h16Z" fill="#ffffff"/><path d="M12 34h16l-8-14Z" fill="#0b6bff"/></svg>
                    </span>
                    <span class="logo__text"><strong>TRIVEX</strong><small>swim · bike · run</small></span>
                </a>
                <p><?= e(t('brand.about')) ?></p>
                <div class="social-links">
                    <a href="#" aria-label="Instagram" rel="nofollow"><?= icon('instagram', '', 18) ?></a>
                    <a href="#" aria-label="Facebook" rel="nofollow"><?= icon('facebook', '', 18) ?></a>
                    <a href="#" aria-label="YouTube" rel="nofollow"><?= icon('youtube', '', 18) ?></a>
                </div>
                <p class="footer-tagline"><?= e(t('footer.made')) ?></p>
            </div>

            <div class="footer-col">
                <h3><?= e(t('footer.shop')) ?></h3>
                <ul>
                    <?php foreach ($footerCategories as $cat): ?>
                        <li><a href="<?= url('/categoria/' . $cat['slug']) ?>"><?= e(localized($cat, 'name')) ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="<?= url('/loja') ?>"><?= e(t('nav.all')) ?></a></li>
                    <?php if ($footerPromoActive): ?><li><a href="<?= url('/promocoes') ?>"><?= e(t('nav.promotions')) ?></a></li><?php endif; ?>
                </ul>
            </div>

            <div class="footer-col">
                <h3><?= e(t('footer.help')) ?></h3>
                <ul>
                    <li><a href="<?= url('/suporte') ?>"><?= e(t('nav.support')) ?></a></li>
                    <li><a href="<?= url('/pagina/guia-transicao') ?>"><?= e(t('footer.transition_guide')) ?></a></li>
                    <?php foreach ($footerPages as $page): ?>
                        <li><a href="<?= url('/pagina/' . $page['slug']) ?>"><?= e(localized($page, 'title')) ?></a></li>
                    <?php endforeach; ?>
                    <?php if ($footerAuth): ?><li><a href="<?= url('/conta/encomendas') ?>"><?= e(t('account.nav_orders')) ?></a></li><?php endif; ?>
                </ul>
            </div>

            <div class="footer-col footer-col--contact">
                <h3><?= e(t('footer.contact')) ?></h3>
                <ul>
                    <li><?= icon('mail', '', 16) ?> <a href="mailto:hello@trivex.pt">hello@trivex.pt</a></li>
                    <li><?= icon('phone', '', 16) ?> <a href="tel:+351210000000">+351 210 000 000</a></li>
                    <li><?= icon('map-pin', '', 16) ?> Av. do Triatlo 1, 1200-000 Lisboa</li>
                    <li><?= icon('clock', '', 16) ?> <?= e(t('footer.hours')) ?></li>
                </ul>
                <div class="footer-payments">
                    <span class="pay-badge">VISA</span>
                    <span class="pay-badge">MC</span>
                    <span class="pay-badge">MB WAY</span>
                    <span class="pay-badge">MB</span>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© <?= date('Y') ?> TRIVEX. <?= e(t('footer.rights')) ?> <span class="footer-demo"><?= e(t('footer.demo')) ?></span></p>
            <div class="footer-bottom__actions">
                <div class="locale-switch locale-switch--footer">
                    <a class="<?= locale() === 'pt' ? 'is-active' : '' ?>" href="<?= url('/idioma/pt') ?>">PT</a><span></span><a class="<?= locale() === 'en' ? 'is-active' : '' ?>" href="<?= url('/idioma/en') ?>">EN</a>
                </div>
                <button class="footer-theme" type="button" data-theme-toggle><?= icon('sun', 'theme-sun', 15) ?><?= icon('moon', 'theme-moon', 15) ?><span><?= e(t('nav.theme')) ?></span></button>
            </div>
        </div>
    </div>
</footer>
