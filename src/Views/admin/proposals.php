<?php
/** @var array $proposals */
?>
<div class="admin-page-head">
    <div>
        <span class="eyebrow"><?= icon('tag', '', 13) ?> <?= e(t('admin.proposals')) ?></span>
        <h1><?= e(t('admin.proposals')) ?></h1>
        <p><?= e(t('admin.proposals_subtitle')) ?></p>
    </div>
    <span class="admin-chip"><?= icon('clock', '', 14) ?> <?= count($proposals) ?> <?= e(t('company.proposal_pending')) ?></span>
</div>

<?php if (!$proposals): ?>
    <div class="admin-card"><div class="admin-empty admin-empty--lg"><?= icon('check-circle', '', 34) ?><p><?= e(t('admin.no_proposals')) ?></p></div></div>
<?php else: ?>
    <div class="proposal-list">
        <?php foreach ($proposals as $proposal): ?>
            <article class="proposal-card">
                <div class="proposal-card__media">
                    <img src="<?= asset('img/' . $proposal['image']) ?>" alt="" width="160" height="160" loading="lazy">
                    <?php if (discount_percent($proposal) > 0): ?><span class="badge badge--sale">-<?= discount_percent($proposal) ?>%</span><?php endif; ?>
                </div>
                <div class="proposal-card__body">
                    <div class="proposal-card__head">
                        <div>
                            <span class="eyebrow"><?= icon('building', '', 12) ?> <?= e($proposal['company_name'] ?? t('admin.na')) ?></span>
                            <h2><?= e(localized($proposal, 'name')) ?></h2>
                            <small><?= e($proposal['brand']) ?> · <?= e($proposal['category_name']) ?> · <?= e(format_date($proposal['created_at'], true)) ?></small>
                        </div>
                        <div class="proposal-card__price">
                            <strong><?= money(product_price($proposal)) ?></strong>
                            <?php if (discount_percent($proposal) > 0): ?><del><?= money((float) $proposal['base_price']) ?></del><?php endif; ?>
                        </div>
                    </div>

                    <p class="proposal-card__desc"><?= e(mb_strimwidth(localized($proposal, 'short'), 0, 240, '…')) ?></p>

                    <?php if (!empty($proposal['specs'])): ?>
                        <div class="proposal-card__specs">
                            <?php foreach (array_slice($proposal['specs'], 0, 4) as $spec): ?>
                                <span><strong><?= e($spec['label_pt'] ?? '') ?></strong><?= e($spec['value'] ?? '') ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <details class="proposal-card__details">
                        <summary><?= e(t('admin.proposal_details')) ?> <?= icon('chevron-down', '', 14) ?></summary>
                        <div class="proposal-card__full">
                            <?php if (!empty($proposal['description_pt'])): ?><p><?= nl2br(e($proposal['description_pt'])) ?></p><?php endif; ?>
                            <?php if (!empty($proposal['gallery'])): ?>
                                <div class="proposal-card__gallery">
                                    <?php foreach ($proposal['gallery'] as $image): ?>
                                        <img src="<?= asset('img/' . $image) ?>" alt="" width="80" height="80" loading="lazy">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </details>

                    <div class="proposal-card__actions">
                        <a class="btn btn--ghost btn--sm" href="<?= url('/produto/' . $proposal['slug']) ?>" target="_blank" rel="noopener"><?= icon('external', '', 14) ?> <?= e(t('company.view_catalog')) ?></a>
                        <form action="<?= url('/admin/propostas/' . $proposal['id'] . '/estado') ?>" method="post" class="proposal-card__forms">
                            <?= csrf_field() ?>
                            <input type="text" name="note" placeholder="<?= e(t('admin.reject_reason_placeholder')) ?>" aria-label="<?= e(t('admin.reject_reason')) ?>">
                            <button class="btn btn--primary btn--sm" type="submit" name="status" value="active" data-confirm="<?= e(t('admin.approve_confirm')) ?>"><?= icon('check', '', 15) ?> <?= e(t('admin.approve')) ?></button>
                            <button class="btn btn--ghost btn--sm btn--danger-ghost" type="submit" name="status" value="rejected" data-confirm="<?= e(t('admin.reject_confirm')) ?>"><?= icon('close', '', 15) ?> <?= e(t('admin.reject')) ?></button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
