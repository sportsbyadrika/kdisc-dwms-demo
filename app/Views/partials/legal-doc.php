<?php /** @var string $heading @var string $sub @var array $sections @var string $updated */ ?>
<?php partial('page-hero', ['heading' => $heading, 'sub' => $sub, 'crumbs' => [$heading => null]]); ?>

<section class="shell grid grid-cols-1 gap-6 py-10 lg:grid-cols-[220px,1fr]">
  <nav class="hidden lg:block" aria-label="On this page">
    <div class="sticky top-20 card card-pad">
      <p class="text-xs font-bold uppercase tracking-wider text-ink-faint">On this page</p>
      <ul class="mt-3 space-y-2">
        <?php foreach ($sections as $i => [$t, $_]): ?>
          <li><a href="#s<?= $i ?>" class="text-sm text-ink-soft hover:text-brand-700"><?= e($t) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </nav>

  <div class="card card-pad">
    <p class="badge-gray"><?= icon('clock', 'h-3.5 w-3.5') ?>Last updated <?= e($updated) ?></p>
    <div class="mt-6 space-y-8">
      <?php foreach ($sections as $i => [$t, $paras]): ?>
        <section id="s<?= $i ?>" class="scroll-mt-20">
          <h2 class="text-lg font-semibold text-ink"><?= e($t) ?></h2>
          <div class="prose-dwms mt-2">
            <?php foreach ((array) $paras as $p): ?>
              <?php if (is_array($p)): ?>
                <ul class="mb-3 ml-1 space-y-1.5">
                  <?php foreach ($p as $li): ?>
                    <li class="flex gap-2"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-300"></span><span><?= e($li) ?></span></li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <p><?= e($p) ?></p>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endforeach; ?>
    </div>
  </div>
</section>
