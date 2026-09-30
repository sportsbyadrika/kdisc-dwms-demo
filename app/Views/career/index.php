<?php
/** @var array $spec @var array $active @var array $filters @var array $facets @var array $result @var string $sortKey */
ob_start(); ?>
<ul class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
  <?php foreach ($result['rows'] as $s): ?>
    <li>
      <article class="flex h-full flex-col rounded-card bg-white p-5 shadow-card transition hover:shadow-pop">
        <div class="flex items-start justify-between gap-3">
          <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-card bg-warning/10 text-warning"><?= icon($s['icon'] ?: 'compass') ?></span>
          <?php if ($s['is_free']): ?><span class="badge-green">No fee</span>
          <?php else: ?><span class="badge-gray"><?= e(money((float) $s['fee'])) ?></span><?php endif; ?>
        </div>

        <?php if ($s['category_name']): ?>
          <p class="mt-3 text-[11px] font-bold uppercase tracking-wider text-brand-500"><?= e($s['category_name']) ?></p>
        <?php endif; ?>
        <h2 class="mt-0.5 text-sm font-semibold text-ink">
          <a href="<?= url('/career-services/' . $s['id']) ?>" class="hover:text-brand-700 hover:underline"><?= e($s['title']) ?></a>
        </h2>
        <p class="mt-1.5 flex-1 text-sm leading-relaxed text-ink-soft"><?= e(str_excerpt($s['summary'] ?: $s['description'], 120)) ?></p>

        <dl class="mt-3 flex flex-wrap gap-x-3 gap-y-1.5 text-xs text-ink-faint">
          <div class="flex items-center gap-1"><?= icon('globe', 'h-3.5 w-3.5') ?><?= e(ucfirst($s['service_mode'])) ?></div>
          <?php if ($s['district']): ?><div class="flex items-center gap-1"><?= icon('map-pin', 'h-3.5 w-3.5') ?><?= e($s['district']) ?></div><?php endif; ?>
          <?php if ($s['provider']): ?><div class="flex items-center gap-1 truncate"><?= icon('building', 'h-3.5 w-3.5') ?><?= e($s['provider']) ?></div><?php endif; ?>
        </dl>

        <div class="mt-4 flex items-center justify-between gap-2 border-t border-line pt-4">
          <?php if ($s['schedule_note']): ?><span class="truncate text-xs text-ink-faint"><?= e($s['schedule_note']) ?></span><?php else: ?><span></span><?php endif; ?>
          <a href="<?= url('/career-services/' . $s['id']) ?>" class="btn-primary btn-sm shrink-0">View &amp; book</a>
        </div>
      </article>
    </li>
  <?php endforeach; ?>
</ul>
<?php
$cards = ob_get_clean();

// Assessment tests are not rows in career_services — they live on partner
// platforms — so they ride above the results rather than inside the grid.
ob_start(); ?>
<a href="<?= url('/assessments') ?>"
   class="group mb-4 flex flex-col gap-4 rounded-card border border-brand-100 bg-brand-50 p-4 transition hover:border-brand-500 hover:shadow-card sm:flex-row sm:items-center sm:gap-5">
  <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-card bg-gradient-to-br from-sky-500 to-sky-700 text-white shadow-sm">
    <?= icon('target', 'h-5 w-5') ?>
  </span>
  <span class="min-w-0 flex-1">
    <span class="flex flex-wrap items-center gap-2">
      <h2 class="text-sm font-semibold text-ink group-hover:text-brand-700">Assessment Test</h2>
      <span class="badge-blue">3 partners</span>
    </span>
    <p class="mt-1 text-sm leading-relaxed text-ink-soft">
      Check your aptitude, job-ready skills and English with TCS iON, foundit or EnglishScore before you book a counselling session.
    </p>
  </span>
  <span class="inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-brand-500 group-hover:text-brand-700">
    Take a test<?= icon('arrow-right', 'h-4 w-4 transition-transform group-hover:translate-x-0.5') ?>
  </span>
</a>
<?php
$promo = ob_get_clean();

partial('search-shell', [
    'path' => '/career-services', 'heading' => 'Career services',
    'sub' => 'Counselling, resume clinics, mock interviews, migration guidance and self-employment support from accredited desks.',
    'crumb' => 'Career Services', 'unit' => 'services',
    'searchLabel' => 'Search services', 'searchPlaceholder' => 'Search services or providers',
    'spec' => $spec, 'active' => $active, 'filters' => $filters, 'facets' => $facets,
    'result' => $result, 'sortKey' => $sortKey, 'cards' => $cards, 'promo' => $promo,
]);
