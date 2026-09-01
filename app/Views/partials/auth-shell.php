<?php
/**
 * Two-column shell shared by every sign-in / registration screen.
 * @var string $heading @var string $sub @var string $content @var array|null $points
 */
$points = $points ?? [
    ['shield-check', 'One verified profile', 'Complete e-KYC once and reuse it for every application.'],
    ['briefcase',    'Apply in a click',      'No re-typing your details for each vacancy.'],
    ['sparkles',     'Skills and guidance',   'Enrol in skilling programmes and book career services.'],
];
$tone = $tone ?? 'brand';
?>
<section class="shell grid gap-8 py-8 lg:grid-cols-[1.05fr,0.95fr] lg:py-14">
  <div class="order-2 lg:order-1">
    <?= $slot ?>
  </div>

  <aside class="order-1 lg:order-2">
    <div class="sticky top-20 overflow-hidden rounded-card bg-gradient-to-br <?= $tone === 'ink' ? 'from-ink to-black' : 'from-brand-700 to-brand-500' ?> p-8 text-white shadow-card">
      <h2 class="text-xl font-semibold"><?= e($asideTitle ?? 'Why register?') ?></h2>
      <p class="mt-2 text-sm text-white/80"><?= e($asideSub ?? 'DWMS 2.0 keeps one record per citizen so your details never have to be entered twice.') ?></p>
      <ul class="mt-6 space-y-5">
        <?php foreach ($points as [$ic, $t, $d]): ?>
          <li class="flex gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/15"><?= icon($ic, 'h-4 w-4') ?></span>
            <span>
              <span class="block text-sm font-semibold"><?= e($t) ?></span>
              <span class="block text-sm text-white/75"><?= e($d) ?></span>
            </span>
          </li>
        <?php endforeach; ?>
      </ul>
      <p class="mt-8 border-t border-white/20 pt-5 text-xs text-white/70">
        Need help? <a href="<?= url('/contact') ?>" class="font-semibold text-white underline">Contact support</a>
        or read the <a href="<?= url('/faq') ?>" class="font-semibold text-white underline">FAQ</a>.
      </p>
    </div>
  </aside>
</section>
