<?php
/** @var string $content */
use App\Core\Auth;

$pageTitle = $pageTitle ?? null;
$siteName  = setting('site_title', config('app.name'));
$title     = $pageTitle ? $pageTitle . ' · ' . $siteName : $siteName . ' — ' . setting('site_tagline', config('app.tagline'));
$bodyClass = $bodyClass ?? '';
?><!DOCTYPE html>
<html lang="en" class="h-full">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="<?= e($metaDescription ?? setting('about_short', 'Digital Workforce Management System')) ?>">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<meta name="dwms-base" content="<?= e(base_url()) ?>">
<meta name="theme-color" content="#5b4fc7">
<title><?= e($title) ?></title>
<link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<!-- app.js registers the Alpine stores and components, so it must run BEFORE
     Alpine boots and fires alpine:init. Both are deferred, so they execute in
     document order. -->
<script defer src="<?= asset('js/app.js') ?>"></script>
<script defer src="<?= asset('js/alpine.min.js') ?>"></script>
</head>
<body class="flex min-h-full flex-col <?= e($bodyClass) ?>">
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-brand-700 focus:shadow-pop">Skip to content</a>

<?php partial('nav'); ?>

<main id="main" class="flex-1">
    <?php partial('flash'); ?>
    <?= $content ?>
</main>

<?php partial('footer'); ?>
<?php partial('login-modal'); ?>
<?php partial('toasts'); ?>
</body>
</html>
