<?php /** @var string $heading @var string $sub @var array|null $crumbs */ ?>
<section class="border-b border-line bg-white">
  <div class="shell py-8 sm:py-10">
    <?php if (!empty($crumbs)): ?>
      <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="flex flex-wrap items-center gap-1.5 text-xs text-ink-faint">
          <li><a href="<?= url('/') ?>" class="hover:text-brand-700">Home</a></li>
          <?php foreach ($crumbs as $label => $href): ?>
            <li aria-hidden="true"><?= icon('chevron-right', 'h-3 w-3') ?></li>
            <li>
              <?php if ($href): ?><a href="<?= url($href) ?>" class="hover:text-brand-700"><?= e($label) ?></a>
              <?php else: ?><span class="font-medium text-ink-soft"><?= e($label) ?></span><?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ol>
      </nav>
    <?php endif; ?>
    <h1 class="text-2xl font-bold text-ink sm:text-3xl"><?= e($heading) ?></h1>
    <?php if (!empty($sub)): ?><p class="mt-2 max-w-3xl text-sm leading-relaxed text-ink-soft"><?= e($sub) ?></p><?php endif; ?>
  </div>
</section>
