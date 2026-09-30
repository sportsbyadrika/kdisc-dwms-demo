<?php
/** @var array $partners */
?>
<?php partial('page-hero', [
  'heading' => 'Assessment tests',
  'sub'     => 'Measure where you stand before you apply. DWMS 2.0 partners with established assessment platforms so your score travels with you — each test is taken on the partner’s own site.',
  'crumbs'  => ['Assessment Test' => null],
]); ?>

<section class="shell py-8 sm:py-10" aria-labelledby="partners-heading">
  <h2 id="partners-heading" class="sr-only">Assessment partners</h2>

  <ul class="grid grid-cols-1 gap-5 md:grid-cols-3">
    <?php foreach ($partners as $slug => $p): ?>
      <li>
        <a href="<?= url('/assessments/' . $slug) ?>"
           class="group relative flex h-full flex-col overflow-hidden rounded-card bg-white p-6 shadow-card transition hover:-translate-y-1 hover:shadow-pop focus-visible:-translate-y-1">
          <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r <?= $p['tone'] ?>"></span>

          <span class="flex items-center justify-between gap-3">
            <span class="min-w-0">
              <h3 class="text-lg font-semibold text-ink group-hover:text-brand-700"><?= e($p['name']) ?></h3>
              <p class="mt-0.5 text-xs font-semibold uppercase tracking-wide text-ink-faint"><?= e($p['tagline']) ?></p>
            </span>
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-card bg-gradient-to-br <?= $p['tone'] ?> text-white shadow-sm">
              <?= icon($p['icon'], 'h-6 w-6') ?>
            </span>
          </span>

          <p class="mt-3 text-sm leading-relaxed text-ink-soft"><?= e($p['summary']) ?></p>

          <!-- items-start as well as content-start: the list grows to fill the card,
               so without both a single row of chips stretches into tall ovals. -->
          <ul class="mt-4 flex flex-1 flex-wrap content-start items-start gap-1.5">
            <?php foreach ($p['covers'] as $c): ?>
              <li class="rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-semibold text-brand-700"><?= e($c) ?></li>
            <?php endforeach; ?>
          </ul>

          <span class="mt-5 flex items-center justify-between border-t border-line pt-4">
            <span class="flex items-center gap-1.5 text-xs text-ink-faint">
              <?= icon('external', 'h-3.5 w-3.5') ?>Partner site
            </span>
            <span class="inline-flex items-center gap-1 text-sm font-semibold text-brand-500 group-hover:text-brand-700">
              Start the test<?= icon('arrow-right', 'h-4 w-4 transition-transform group-hover:translate-x-0.5') ?>
            </span>
          </span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>

  <!-- what DWMS does and does not do with these tests -->
  <div class="mt-8 rounded-card border border-line bg-white p-5 sm:p-6">
    <h2 class="flex items-center gap-2 text-sm font-semibold text-ink"><?= icon('info', 'h-4 w-4 text-brand-500') ?>Before you begin</h2>
    <ul class="mt-3 grid grid-cols-1 gap-2.5 text-sm leading-relaxed text-ink-soft sm:grid-cols-2">
      <li class="flex gap-2"><?= icon('check-circle', 'h-4 w-4 shrink-0 text-success') ?>Each test runs on the partner’s own platform, under their terms and privacy policy.</li>
      <li class="flex gap-2"><?= icon('check-circle', 'h-4 w-4 shrink-0 text-success') ?>You may need to create an account with the partner before you can start.</li>
      <li class="flex gap-2"><?= icon('check-circle', 'h-4 w-4 shrink-0 text-success') ?>Fees, scheduling and certificates are set by the partner, not by DWMS.</li>
      <li class="flex gap-2"><?= icon('check-circle', 'h-4 w-4 shrink-0 text-success') ?>Scores are not written back to your DWMS profile — attach the certificate yourself.</li>
    </ul>
  </div>

  <div class="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-card bg-brand-50 p-5">
    <p class="text-sm text-ink-soft">Not sure which test fits your plan? A counsellor can talk it through with you.</p>
    <a href="<?= url('/career-services') ?>" class="btn-outline btn-sm shrink-0">
      <?= icon('compass', 'h-4 w-4') ?>Browse career services
    </a>
  </div>
</section>
