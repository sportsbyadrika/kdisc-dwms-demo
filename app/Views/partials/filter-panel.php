<?php
/**
 * E-commerce style facet panel.
 * @var string $path @var array $spec @var array $active @var array $filters @var array $facets
 */
use App\Core\Search;

$groups = [];
foreach ($spec as $key => $def) {
    if (empty($def['options']) || $key === 'sort') {
        continue;
    }
    $groups[$key] = $def;
}
$activeCount = count($filters);
?>
<aside x-data="{ open: false }" class="lg:sticky lg:top-20 lg:self-start">

  <button type="button" @click="open = !open" :aria-expanded="open"
          class="mb-3 flex w-full items-center justify-between rounded-card bg-white px-4 py-3 text-sm font-semibold text-ink shadow-card lg:hidden">
    <span class="flex items-center gap-2"><?= icon('filter', 'h-4 w-4') ?>Filters
      <?php if ($activeCount): ?><span class="badge-blue"><?= $activeCount ?></span><?php endif; ?>
    </span>
    <span :class="open && 'rotate-180'" class="transition-transform"><?= icon('chevron-down', 'h-4 w-4') ?></span>
  </button>

  <div class="space-y-3 lg:!block" :class="open ? 'block' : 'hidden'">
    <div class="flex items-center justify-between rounded-card bg-white px-4 py-3 shadow-card">
      <p class="text-sm font-bold text-ink">Filters</p>
      <?php if ($activeCount): ?>
        <a href="<?= url($path) ?>" class="text-xs font-semibold text-brand-500 hover:text-brand-700">Clear all</a>
      <?php endif; ?>
    </div>

    <?php foreach ($groups as $key => $def):
        $counts   = $facets[$key] ?? null;
        $options  = $def['options'];
        $multiple = !empty($def['multiple']);
        // Hide options that would return nothing, unless they are selected.
        if ($counts !== null) {
            $options = array_filter($options, static fn($label, $value) => isset($counts[(string) $value]) || Search::isActive($active, $key, (string) $value), ARRAY_FILTER_USE_BOTH);
        }
        if (!$options) {
            continue;
        }
        $expanded = $activeCount === 0 ? count($groups) <= 4 : (bool) ($filters[$key] ?? false);
    ?>
      <div x-data="{ show: <?= $expanded ? 'true' : 'true' ?>, all: false }" class="overflow-hidden rounded-card bg-white shadow-card">
        <button type="button" @click="show = !show" :aria-expanded="show"
                class="flex w-full items-center justify-between px-4 py-3 text-left">
          <span class="text-xs font-bold uppercase tracking-wider text-ink-faint"><?= e($def['label']) ?></span>
          <span :class="!show && '-rotate-90'" class="text-ink-faint transition-transform"><?= icon('chevron-down', 'h-4 w-4') ?></span>
        </button>
        <ul x-show="show" class="space-y-0.5 border-t border-line px-2 py-2">
          <?php $i = 0; foreach ($options as $value => $label): $i++;
              $on = Search::isActive($active, $key, (string) $value);
              $n  = $counts[(string) $value] ?? null; ?>
            <li <?= $i > 6 ? 'x-show="all" x-cloak' : '' ?>>
              <a href="<?= e(Search::toggleUrl($path, $filters, $key, (string) $value, $multiple)) ?>"
                 class="flex items-center gap-2.5 rounded px-2 py-1.5 text-sm transition hover:bg-brand-50 <?= $on ? 'font-semibold text-brand-700' : 'text-ink-soft' ?>">
                <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded border <?= $on ? 'border-brand-500 bg-brand-500 text-white' : 'border-ink/30 bg-white' ?>">
                  <?= $on ? icon('check', 'h-3 w-3') : '' ?>
                </span>
                <span class="flex-1 truncate"><?= e($label) ?></span>
                <?php if ($n !== null): ?><span class="shrink-0 text-xs text-ink-faint"><?= (int) $n ?></span><?php endif; ?>
              </a>
            </li>
          <?php endforeach; ?>
          <?php if ($i > 6): ?>
            <li><button type="button" @click="all = !all" class="w-full px-2 py-1.5 text-left text-xs font-semibold text-brand-500 hover:text-brand-700"
                        x-text="all ? 'Show fewer' : 'Show all <?= $i ?>'">Show all <?= $i ?></button></li>
          <?php endif; ?>
        </ul>
      </div>
    <?php endforeach; ?>
  </div>
</aside>
