<?php
/**
 * Shared frame for the Skills and Career Services search pages.
 * @var string $path @var string $heading @var string $sub @var string $crumb
 * @var array $spec @var array $active @var array $filters @var array $facets
 * @var array $result @var string $sortKey @var string $cards  (rendered HTML)
 * @var string $unit
 */
?>
<?php partial('page-hero', ['heading' => $heading, 'sub' => $sub, 'crumbs' => [$crumb => null]]); ?>

<section class="border-b border-line bg-white">
  <div class="shell py-4">
    <form method="get" action="<?= url($path) ?>" class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr,auto]">
      <?php foreach ($filters as $k => $v): if ($k === 'q') { continue; }
          foreach ((array) $v as $vv): ?>
        <input type="hidden" name="<?= e($k) ?><?= is_array($v) ? '[]' : '' ?>" value="<?= e($vv) ?>">
      <?php endforeach; endforeach; ?>
      <div class="relative">
        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-ink-faint"><?= icon('search', 'h-4 w-4') ?></span>
        <label class="sr-only" for="s-q"><?= e($searchLabel ?? 'Search') ?></label>
        <input id="s-q" name="q" type="search" value="<?= e($active['q'] ?? '') ?>"
               placeholder="<?= e($searchPlaceholder ?? 'Search') ?>" class="field !py-2.5 pl-9">
      </div>
      <button type="submit" class="btn-primary btn-lg">Search</button>
    </form>
  </div>
</section>

<section class="shell grid grid-cols-1 gap-6 py-6 lg:grid-cols-[260px,1fr]">
  <?php partial('filter-panel', ['path' => $path, 'spec' => $spec, 'active' => $active, 'filters' => $filters, 'facets' => $facets]); ?>

  <div class="min-w-0">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
      <p class="text-sm text-ink-soft">
        <strong class="text-ink"><?= number_format($result['total']) ?></strong>
        <?= e($result['total'] === 1 ? rtrim($unit, 's') : $unit) ?>
        <?php if (!empty($active['q'])): ?> for “<span class="font-medium text-ink"><?= e($active['q']) ?></span>”<?php endif; ?>
      </p>
      <form method="get" action="<?= url($path) ?>" class="flex items-center gap-2">
        <?php foreach ($filters as $k => $v): foreach ((array) $v as $vv): ?>
          <input type="hidden" name="<?= e($k) ?><?= is_array($v) ? '[]' : '' ?>" value="<?= e($vv) ?>">
        <?php endforeach; endforeach; ?>
        <label for="sort" class="text-xs font-semibold uppercase tracking-wider text-ink-faint">Sort</label>
        <select id="sort" name="sort" class="field !w-auto !py-1.5 text-sm" onchange="this.form.submit()">
          <?php foreach ($spec['sort']['options'] as $k => $label): ?>
            <option value="<?= e($k) ?>" <?= $sortKey === $k ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>

    <?php partial('active-filters', ['path' => $path, 'spec' => $spec, 'filters' => $filters]); ?>

    <?php if (!$result['rows']): ?>
      <?php partial('empty-state', [
        'icon' => 'search', 'title' => 'Nothing matches these filters',
        'message' => 'Try removing a filter or searching for something broader.',
        'action' => '<a href="' . url($path) . '" class="btn-primary btn-sm">Clear all filters</a>',
      ]); ?>
    <?php else: ?>
      <?= $cards ?>
      <?php partial('pagination', ['path' => $path, 'filters' => $filters, 'result' => $result]); ?>
    <?php endif; ?>
  </div>
</section>
