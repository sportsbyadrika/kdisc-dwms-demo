<?php /** @var string $icon @var string $title @var string $message @var string|null $action */ ?>
<div class="flex flex-col items-center justify-center rounded-card border border-dashed border-line bg-white px-6 py-12 text-center">
  <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-brand-300"><?= icon($icon ?? 'inbox', 'h-6 w-6') ?></span>
  <p class="mt-3 text-sm font-semibold text-ink"><?= e($title) ?></p>
  <p class="mt-1 max-w-sm text-sm text-ink-soft"><?= e($message) ?></p>
  <?php if (!empty($action)): ?><div class="mt-4"><?= $action ?></div><?php endif; ?>
</div>
