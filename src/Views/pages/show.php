<?php
/** @var array $page */
/** @var string $content */
$paragraphs = preg_split('/\R{2,}/', trim($content)) ?: [];
?>
<section class="page-head page-head--content">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="<?= url('/') ?>"><?= e(t('product.breadcrumb_home')) ?></a>
            <?= icon('chevron-right', '', 13) ?>
            <span><?= e(localized($page, 'title')) ?></span>
        </nav>
        <div class="content-hero">
            <span class="eyebrow"><?= icon($page['is_system'] ? 'file' : 'leaf', '', 13) ?> <?= e($page['is_system'] ? t('admin.system_page') : t('admin.custom_page')) ?></span>
            <h1><?= e(localized($page, 'title')) ?></h1>
            <p><?= e(t('pages.updated', ['date' => format_date($page['updated_at'])])) ?></p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="content-layout">
            <article class="content-card content-card--page">
                <?php if ($paragraphs): ?>
                    <div class="prose prose--lg">
                        <?php foreach ($paragraphs as $paragraph): ?>
                            <p><?= nl2br(e(trim($paragraph))) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="muted"><?= e(t('common.no_results')) ?></p>
                <?php endif; ?>

                <?php if ($page['slug'] === 'guia-transicao'): ?>
                    <div class="transition-steps">
                        <div class="transition-step"><b>01</b><div><strong>Swim → Bike</strong><p>Fato para baixo, capacete e óculos num só movimento. Deixa os sapatos presos aos pedais.</p></div></div>
                        <div class="transition-step"><b>02</b><div><strong>Bike → Run</strong><p>Tira os pés dos sapatos antes da entrada, corre com a bike na mão e usa cordões elásticos.</p></div></div>
                        <div class="transition-step"><b>03</b><div><strong>Checklist</strong><p>Gel, água, dorsal, toalha e uma ideia clara do percurso. Ensina a transição antes da prova.</p></div></div>
                    </div>
                <?php endif; ?>

                <div class="content-page__footer">
                    <a class="btn btn--ghost" href="<?= url('/') ?>"><?= icon('arrow-left', '', 16) ?> <?= e(t('common.back')) ?></a>
                    <a class="btn btn--primary" href="<?= url('/suporte') ?>"><?= icon('headset', '', 16) ?> <?= e(t('pages.need_help')) ?></a>
                </div>
            </article>

            <aside class="content-aside">
                <div class="side-card">
                    <span class="eyebrow"><?= icon('target', '', 13) ?> TRIVEX</span>
                    <h3><?= e(t('pages.help_title')) ?></h3>
                    <p><?= e(t('pages.help_text')) ?></p>
                    <a class="btn btn--primary btn--sm" href="<?= url('/suporte') ?>"><?= e(t('nav.support')) ?> <?= icon('arrow-right', '', 15) ?></a>
                </div>
                <div class="side-card">
                    <h3><?= e(t('footer.shop')) ?></h3>
                    <ul class="side-links">
                        <li><a href="<?= url('/loja') ?>"><?= e(t('nav.shop')) ?> <?= icon('chevron-right', '', 14) ?></a></li>
                        <li><a href="<?= url('/promocoes') ?>"><?= e(t('nav.promotions')) ?> <?= icon('chevron-right', '', 14) ?></a></li>
                        <li><a href="<?= url('/pagina/sobre-nos') ?>"><?= e(t('footer.about')) ?> <?= icon('chevron-right', '', 14) ?></a></li>
                        <li><a href="<?= url('/pagina/devolucoes') ?>"><?= e(t('footer.returns')) ?> <?= icon('chevron-right', '', 14) ?></a></li>
                    </ul>
                </div>
                <div class="side-card side-card--spooky">
                    <span class="eyebrow eyebrow--light"><?= icon('ghost', '', 13) ?> <?= e(t('home.promo_eyebrow')) ?></span>
                    <h3><?= e(t('home.promo_title')) ?></h3>
                    <a class="btn btn--accent btn--sm" href="<?= url('/promocoes') ?>"><?= e(t('promo.cta')) ?></a>
                </div>
            </aside>
        </div>
    </div>
</section>
