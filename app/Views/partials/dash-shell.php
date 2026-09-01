<?php
/**
 * Sidebar + content shell shared by the seeker, employer and official areas.
 * @var string $slot  @var array $nav  @var array $identity
 */
$current = current_path();
?>
<div class="shell grid gap-6 py-6 lg:grid-cols-[260px,1fr] lg:py-8">

  <aside class="lg:sticky lg:top-20 lg:self-start" x-data="{ open: false }">
    <!-- identity card -->
    <div class="overflow-hidden rounded-card bg-white shadow-card">
      <div class="h-16 bg-gradient-to-r from-brand-700 to-brand-500"></div>
      <div class="px-4 pb-4">
        <span class="-mt-8 flex h-16 w-16 items-center justify-center overflow-hidden rounded-full border-4 border-white bg-brand-100 text-lg font-bold text-brand-700">
          <?php if (!empty($identity['photo'])): ?>
            <img src="<?= e(upload_url($identity['photo'])) ?>" alt="" class="h-full w-full object-cover">
          <?php else: ?><?= e(initials($identity['name'] ?? 'U')) ?><?php endif; ?>
        </span>
        <p class="mt-2 truncate text-sm font-semibold text-ink"><?= e($identity['name'] ?? '') ?></p>
        <p class="truncate text-xs text-ink-faint"><?= e($identity['subtitle'] ?? '') ?></p>
        <?php if (!empty($identity['badges'])): ?>
          <div class="mt-2 flex flex-wrap gap-1"><?php foreach ($identity['badges'] as $b) { echo $b; } ?></div>
        <?php endif; ?>
        <?php if (isset($identity['score'])): ?>
          <div class="mt-3">
            <div class="flex items-center justify-between text-xs">
              <span class="font-medium text-ink-soft">Profile strength</span>
              <span class="font-bold text-brand-700"><?= (int) $identity['score'] ?>%</span>
            </div>
            <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-black/10">
              <div class="h-full rounded-full bg-brand-500 transition-all" style="width: <?= (int) $identity['score'] ?>%"></div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- mobile toggle -->
    <button type="button" @click="open = !open" :aria-expanded="open"
            class="mt-3 flex w-full items-center justify-between rounded-card bg-white px-4 py-3 text-sm font-semibold text-ink shadow-card lg:hidden">
      <span class="flex items-center gap-2"><?= icon('menu', 'h-4 w-4') ?>Menu</span>
      <span :class="open && 'rotate-180'" class="transition-transform"><?= icon('chevron-down', 'h-4 w-4') ?></span>
    </button>

    <nav class="mt-3 space-y-4 lg:!block" :class="open ? 'block' : 'hidden'" aria-label="Dashboard">
      <?php foreach ($nav as $group => $links): ?>
        <div class="overflow-hidden rounded-card bg-white shadow-card">
          <?php if (!is_int($group)): ?>
            <p class="border-b border-line px-4 py-2.5 text-[11px] font-bold uppercase tracking-wider text-ink-faint"><?= e($group) ?></p>
          <?php endif; ?>
          <ul class="py-1">
            <?php foreach ($links as $l):
                $active = $current === rtrim($l['path'], '/') || (!empty($l['match']) && strpos($current, $l['match']) === 0); ?>
              <li>
                <a href="<?= url($l['path']) ?>"
                   class="flex items-center gap-3 border-l-[3px] px-4 py-2.5 text-sm transition <?= $active
                        ? 'border-brand-500 bg-brand-50 font-semibold text-brand-700'
                        : 'border-transparent text-ink-soft hover:bg-black/[0.03] hover:text-ink' ?>">
                  <?= icon($l['icon'], 'h-4 w-4 shrink-0') ?>
                  <span class="flex-1 truncate"><?= e($l['label']) ?></span>
                  <?php if (!empty($l['count'])): ?>
                    <span class="rounded-full bg-brand-100 px-2 py-0.5 text-[11px] font-bold text-brand-700"><?= (int) $l['count'] ?></span>
                  <?php elseif (!empty($l['flag'])): ?>
                    <span class="h-2 w-2 rounded-full bg-danger" title="Needs attention"></span>
                  <?php endif; ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>

      <form method="post" action="<?= url('/logout') ?>" class="overflow-hidden rounded-card bg-white shadow-card">
        <?= csrf_field() ?>
        <button type="submit" class="flex w-full items-center gap-3 px-4 py-3 text-sm font-medium text-ink-soft transition hover:bg-danger/5 hover:text-danger">
          <?= icon('logout', 'h-4 w-4') ?>Sign out
        </button>
      </form>
    </nav>
  </aside>

  <div class="min-w-0"><?= $slot ?></div>
</div>
