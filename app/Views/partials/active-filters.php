<?php
/** @var string $path @var array $spec @var array $filters */
use App\Core\Search;

if (!$filters) {
    return;
}
?>
<div class="mb-4 flex flex-wrap items-center gap-2">
  <span class="text-xs font-semibold uppercase tracking-wider text-ink-faint">Filtered by</span>
  <?php foreach ($filters as $key => $value):
      $def = $spec[$key] ?? null;
      if (!$def) { continue; }
      foreach ((array) $value as $v):
          $label = $def['options'][$v] ?? $v; ?>
        <a href="<?= e(Search::removeUrl($path, $filters, $key, is_array($value) ? (string) $v : null)) ?>"
           class="chip !border-brand-200 !bg-brand-50 !text-brand-700 hover:!bg-brand-100">
          <span class="text-ink-faint"><?= e($def['label']) ?>:</span> <?= e($label) ?>
          <?= icon('x', 'h-3 w-3') ?>
        </a>
  <?php endforeach; endforeach; ?>
  <a href="<?= url($path) ?>" class="text-xs font-semibold text-brand-500 hover:text-brand-700">Clear all</a>
</div>
