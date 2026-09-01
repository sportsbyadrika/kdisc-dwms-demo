<?php $pageTitle = $title ?? 'Error'; ?>
<section class="shell flex min-h-[60vh] flex-col items-center justify-center py-16 text-center">
  <p class="text-7xl font-black tracking-tighter text-brand-100"><?= e($code) ?></p>
  <h1 class="mt-2 text-2xl font-semibold text-ink sm:text-3xl"><?= e($title) ?></h1>
  <p class="mt-3 max-w-md text-sm text-ink-soft">
    <?= e($message ?: 'The page you were looking for is not available. It may have been moved or removed.') ?>
  </p>
  <div class="mt-7 flex flex-wrap items-center justify-center gap-3">
    <a href="<?= url('/') ?>" class="btn-primary"><?= icon('home', 'h-4 w-4') ?>Back to home</a>
    <a href="<?= url('/jobs') ?>" class="btn-outline"><?= icon('search', 'h-4 w-4') ?>Search jobs</a>
    <a href="<?= url('/contact') ?>" class="btn-ghost">Contact support</a>
  </div>
</section>
