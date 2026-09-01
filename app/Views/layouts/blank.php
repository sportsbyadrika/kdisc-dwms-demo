<?php $title = ($pageTitle ?? 'DWMS') . ' · ' . config('app.name'); ?><!DOCTYPE html>
<html lang="en" class="h-full">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<meta name="dwms-base" content="<?= e(base_url()) ?>">
<meta name="robots" content="noindex">
<title><?= e($title) ?></title>
<link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<script defer src="<?= asset('js/alpine.min.js') ?>"></script>
<script defer src="<?= asset('js/app.js') ?>"></script>
</head>
<body class="min-h-full bg-canvas">
<?php partial('flash'); ?>
<?= $content ?>
<?php partial('toasts'); ?>
</body>
</html>
