<?php /** @var string $title @var string|null $sub @var string|null $actions */ ?>
<div class="mb-5 flex flex-wrap items-start justify-between gap-3">
  <div class="min-w-0">
    <h1 class="text-xl font-bold text-ink sm:text-2xl"><?= e($title) ?></h1>
    <?php if (!empty($sub)): ?><p class="mt-1 text-sm text-ink-soft"><?= e($sub) ?></p><?php endif; ?>
  </div>
  <?php if (!empty($actions)): ?><div class="flex shrink-0 flex-wrap gap-2"><?= $actions ?></div><?php endif; ?>
</div>
