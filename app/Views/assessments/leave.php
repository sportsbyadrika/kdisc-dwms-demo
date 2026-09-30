<?php
/** @var string $slug @var array $partner @var string $host */
?>
<?php partial('page-hero', [
  'heading' => 'You are leaving DWMS 2.0',
  'sub'     => 'The assessment is hosted by our partner, so this link takes you off this website.',
  'crumbs'  => ['Assessment Test' => '/assessments', $partner['name'] => null],
]); ?>

<section class="shell py-8 sm:py-10">
  <div class="mx-auto max-w-2xl">
    <div class="overflow-hidden rounded-card bg-white shadow-card">
      <!-- the warning itself -->
      <div class="flex items-start gap-3 border-b border-warning/25 bg-warning/5 p-5 sm:p-6">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-warning/15 text-warning">
          <?= icon('alert', 'h-5 w-5') ?>
        </span>
        <div class="min-w-0">
          <h2 class="text-base font-semibold text-ink">You are about to visit an external website</h2>
          <p class="mt-1 text-sm leading-relaxed text-ink-soft">
            <strong class="font-semibold text-ink"><?= e($partner['name']) ?></strong> is an independent
            organisation. DWMS 2.0 does not control its content, and once you continue you are covered by
            that site’s own terms of use and privacy policy rather than ours.
          </p>
        </div>
      </div>

      <div class="p-5 sm:p-6">
        <dl class="space-y-3 text-sm">
          <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
            <dt class="w-28 shrink-0 text-xs font-semibold uppercase tracking-wide text-ink-faint">Partner</dt>
            <dd class="font-semibold text-ink"><?= e($partner['name']) ?></dd>
          </div>
          <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
            <dt class="w-28 shrink-0 text-xs font-semibold uppercase tracking-wide text-ink-faint">Assessment</dt>
            <dd class="text-ink-soft"><?= e($partner['tagline']) ?></dd>
          </div>
          <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
            <dt class="w-28 shrink-0 text-xs font-semibold uppercase tracking-wide text-ink-faint">Destination</dt>
            <dd class="min-w-0 break-all font-mono text-xs text-ink"><?= e($host) ?></dd>
          </div>
        </dl>

        <p class="mt-4 text-sm leading-relaxed text-ink-soft"><?= e($partner['summary']) ?></p>

        <p class="mt-4 flex items-start gap-2 rounded-card bg-canvas p-3 text-xs leading-relaxed text-ink-soft">
          <?= icon('shield', 'h-4 w-4 shrink-0 text-brand-500') ?>
          <span>Check the address bar says <span class="font-mono font-semibold text-ink"><?= e($host) ?></span>
          before you type any personal detail or make a payment. DWMS never asks for a fee for an assessment.</span>
        </p>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row-reverse sm:items-center">
          <a href="<?= e($partner['url']) ?>" target="_blank" rel="noopener noreferrer external"
             class="btn-primary btn-lg justify-center sm:w-auto">
            Continue to <?= e($partner['name']) ?><?= icon('external', 'h-4 w-4') ?>
          </a>
          <a href="<?= url('/assessments') ?>" class="btn-outline btn-lg justify-center sm:w-auto">
            <?= icon('arrow-left', 'h-4 w-4') ?>Stay on DWMS
          </a>
        </div>
        <p class="mt-3 text-center text-xs text-ink-faint sm:text-right">Opens in a new tab, so DWMS stays open behind it.</p>
      </div>
    </div>
  </div>
</section>
