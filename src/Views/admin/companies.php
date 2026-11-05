<?php
/** @var array $companies */
$groups = ['pending' => [], 'approved' => [], 'rejected' => []];
foreach ($companies as $company) {
    $groups[$company['status']][] = $company;
}
$statusClass = fn (string $status) => $status === 'approved' ? 'status-green' : ($status === 'pending' ? 'status-amber' : 'status-red');
?>
<div class="admin-page-head">
    <div>
        <span class="eyebrow"><?= icon('building', '', 13) ?> <?= e(t('admin.companies')) ?></span>
        <h1><?= e(t('admin.companies')) ?></h1>
        <p><?= e(t('admin.companies_subtitle')) ?></p>
    </div>
    <div class="admin-page-head__actions">
        <span class="status-pill status-amber"><?= e(t('admin.pending')) ?>: <?= count($groups['pending']) ?></span>
        <a class="btn btn--ghost btn--sm" href="<?= url('/admin/propostas') ?>"><?= icon('tag', '', 15) ?> <?= e(t('admin.proposals')) ?></a>
    </div>
</div>

<?php if (!$companies): ?>
    <div class="admin-card"><div class="admin-empty admin-empty--lg"><?= icon('building', '', 34) ?><p><?= e(t('common.no_results')) ?></p></div></div>
<?php else: ?>
    <div class="company-grid">
        <?php foreach ($companies as $company): ?>
            <article class="company-card">
                <div class="company-card__head">
                    <span class="company-card__logo"><?= e(initials($company['name'])) ?></span>
                    <div>
                        <strong><?= e($company['name']) ?></strong>
                        <small><?= e($company['vat'] ?: t('admin.na')) ?> · <?= e($company['city'] ?: t('admin.na')) ?></small>
                    </div>
                    <span class="status-pill <?= $statusClass($company['status']) ?>"><?= e(t('company.status_' . $company['status'])) ?></span>
                </div>

                <div class="company-card__body">
                    <div class="company-card__row">
                        <span class="company-card__label"><?= e(t('admin.contact')) ?></span>
                        <span class="company-card__value"><strong><?= e($company['owner_name'] ?? $company['contact_name']) ?></strong><small><?= e($company['owner_email'] ?? $company['email']) ?></small></span>
                    </div>
                    <div class="company-card__row">
                        <span class="company-card__label"><?= e(t('common.phone')) ?></span>
                        <span class="company-card__value"><strong><?= e($company['phone'] ?: '—') ?></strong></span>
                    </div>
                    <div class="company-card__row">
                        <span class="company-card__label"><?= e(t('admin.products')) ?></span>
                        <span class="company-card__value"><strong><?= (int) $company['products_count'] ?></strong><?php if ((int) $company['pending_products'] > 0): ?><small class="status-pill status-amber"><?= (int) $company['pending_products'] ?> <?= e(t('company.proposal_pending')) ?></small><?php endif; ?></span>
                    </div>
                    <div class="company-card__row">
                        <span class="company-card__label"><?= e(t('admin.submitted')) ?></span>
                        <span class="company-card__value"><strong><?= e(format_date($company['created_at'])) ?></strong></span>
                    </div>
                </div>

                <?php if (!empty($company['review_note'])): ?>
                    <p class="company-card__note"><?= icon('info', '', 14) ?> <?= e($company['review_note']) ?></p>
                <?php endif; ?>

                <details class="company-card__actions">
                    <summary><?= icon('settings', '', 15) ?> <?= e(t('admin.company_details')) ?> <?= icon('chevron-down', '', 14) ?></summary>
                    <form action="<?= url('/admin/empresas/' . $company['id'] . '/estado') ?>" method="post" class="admin-form">
                        <?= csrf_field() ?>
                        <label class="field">
                            <span><?= e(t('admin.review_note')) ?></span>
                            <textarea name="note" rows="2" placeholder="<?= e(t('admin.reject_reason_placeholder')) ?>"></textarea>
                        </label>
                        <div class="company-card__buttons">
                            <button class="btn btn--primary btn--sm" type="submit" name="status" value="approved" data-confirm="<?= e(t('admin.approve_confirm')) ?>"><?= icon('check', '', 15) ?> <?= e(t('admin.approve')) ?></button>
                            <button class="btn btn--ghost btn--sm btn--danger-ghost" type="submit" name="status" value="rejected" data-confirm="<?= e(t('admin.reject_confirm')) ?>"><?= icon('close', '', 15) ?> <?= e(t('admin.reject')) ?></button>
                        </div>
                    </form>
                </details>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
