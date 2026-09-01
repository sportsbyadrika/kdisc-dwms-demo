<?php
/** @var array $spec @var array $active @var array $filters @var array $facets @var array $result @var string $sortKey */
ob_start(); ?>
<ul class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
  <?php foreach ($result['rows'] as $p):
      $duration = $p['duration_value'] ? $p['duration_value'] . ' ' . $p['duration_unit'] : null; ?>
    <li>
      <article class="flex h-full flex-col overflow-hidden rounded-card bg-white shadow-card transition hover:shadow-pop">
        <div class="relative h-28 bg-gradient-to-br from-brand-700 to-brand-500">
          <?php if ($p['image']): ?>
            <img src="<?= e(upload_url($p['image'])) ?>" alt="" class="h-full w-full object-cover">
          <?php else: ?>
            <span class="absolute inset-0 flex items-center justify-center text-white/30"><?= icon('graduation', 'h-12 w-12') ?></span>
          <?php endif; ?>
          <div class="absolute left-3 top-3 flex flex-wrap gap-1.5">
            <?php if ($p['is_free']): ?><span class="badge bg-white/95 text-success">Free</span>
            <?php else: ?><span class="badge bg-white/95 text-ink"><?= e(money((float) $p['fee'])) ?></span><?php endif; ?>
            <?php if ($p['is_certified']): ?><span class="badge bg-white/95 text-brand-700"><?= icon('shield-check', 'h-3 w-3') ?>Certified</span><?php endif; ?>
          </div>
        </div>

        <div class="flex flex-1 flex-col p-5">
          <?php if ($p['category_name']): ?>
            <p class="text-[11px] font-bold uppercase tracking-wider text-brand-500"><?= e($p['category_name']) ?></p>
          <?php endif; ?>
          <h2 class="mt-1 text-sm font-semibold text-ink">
            <a href="<?= url('/skills/' . $p['id']) ?>" class="hover:text-brand-700 hover:underline"><?= e($p['title']) ?></a>
          </h2>
          <p class="mt-0.5 truncate text-xs text-ink-soft"><?= e($p['provider']) ?></p>
          <p class="mt-2 flex-1 text-sm leading-relaxed text-ink-soft"><?= e(str_excerpt($p['description'], 110)) ?></p>

          <dl class="mt-3 flex flex-wrap gap-x-3 gap-y-1.5 text-xs text-ink-faint">
            <?php if ($duration): ?><div class="flex items-center gap-1"><?= icon('clock', 'h-3.5 w-3.5') ?><?= e($duration) ?></div><?php endif; ?>
            <div class="flex items-center gap-1"><?= icon('globe', 'h-3.5 w-3.5') ?><?= e(ucfirst($p['mode'])) ?></div>
            <?php if ($p['district']): ?><div class="flex items-center gap-1"><?= icon('map-pin', 'h-3.5 w-3.5') ?><?= e($p['district']) ?></div><?php endif; ?>
            <?php if ($p['start_date']): ?><div class="flex items-center gap-1"><?= icon('calendar', 'h-3.5 w-3.5') ?>From <?= e(fdate($p['start_date'], 'd M')) ?></div><?php endif; ?>
          </dl>

          <div class="mt-4 flex items-center justify-between gap-2 border-t border-line pt-4">
            <span class="chip"><?= e(ucfirst($p['level'])) ?></span>
            <a href="<?= url('/skills/' . $p['id']) ?>" class="btn-primary btn-sm">View &amp; enrol</a>
          </div>
        </div>
      </article>
    </li>
  <?php endforeach; ?>
</ul>
<?php
$cards = ob_get_clean();
partial('search-shell', [
    'path' => '/skills', 'heading' => 'Skilling programmes',
    'sub' => 'Government-backed and accredited training with certification, stipend support and placement linkage.',
    'crumb' => 'Skills', 'unit' => 'programmes',
    'searchLabel' => 'Search programmes', 'searchPlaceholder' => 'Search programmes, providers or skills',
    'spec' => $spec, 'active' => $active, 'filters' => $filters, 'facets' => $facets,
    'result' => $result, 'sortKey' => $sortKey, 'cards' => $cards,
]);
