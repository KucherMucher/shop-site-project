<?php
$pageTitle = $title ?? config('app.name');
$metaDescription = $meta_description ?? t('brand.about');
$bodyClass = $bodyClass ?? '';
$pageStyles = $pageStyles ?? [];
$pageScripts = $pageScripts ?? [];
?>
<!DOCTYPE html>
<html lang="<?= e(locale()) ?>" data-theme="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta name="theme-color" content="#062a4d">
    <title><?= e($pageTitle) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('img/logo.svg') ?>">
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('trivex-theme');
                var system = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', stored || system);
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <?php foreach ($pageStyles as $style): ?>
        <link rel="stylesheet" href="<?= asset('css/' . $style) ?>">
    <?php endforeach; ?>
</head>
<body class="<?= e($bodyClass) ?>" data-locale="<?= e(locale()) ?>">
<a class="skip-link" href="#main"><?= e(t('common.continue')) ?></a>
