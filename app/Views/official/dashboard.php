<?php
/** @var array $stats @var array $series @var array $pendingList @var array $byDistrict
 *  @var array $activity @var array $topApplications @var array $me */
use App\Core\Auth;
$max = max(1, max($series));
$maxDistrict = max(1, max(array_map(static fn($d) => (int) $d['n'], $byDistrict ?: [['n' => 1]])));
?>
<?php partial('dash-header', [
  'title' => 'Administration',
  'sub'   => 'Platform-wide activity across job seekers, employers and content.',
]); ?>

<?php if ($stats['pending'] > 0 && Auth::can('employers.verify')): ?>
  <div class="mb-4 flex flex-wrap items-center gap-4 rounded-card border border-warning/30 bg-warning/5 p-5">
    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-warning/15 text-warning"><?= icon('clock') ?></span>
    <div class="min-w-0 flex-1">
      <p class="text-sm font-semibold text-ink"><?= (int) $stats['pending'] ?> organisation(s) awaiting verification</p>
      <p class="text-sm text-ink-soft">Employers cannot be shown as verified to job seekers until the desk reviews them.</p>
    </div>
    <a href="<?= url('/official/employers') ?>" class="btn-primary btn-sm shrink-0">Review now</a>
  </div>
<?php endif; ?>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
  <?php foreach ([
    ['Job seekers', $stats['seekers'], 'users', '/official/seekers'],
    ['e-KYC verified', $stats['verified'], 'shield-check', '/official/seekers?kyc=verified'],
    ['Employers', $stats['employers'], 'building', '/official/employers?status=all'],
    ['Live vacancies', $stats['jobs'], 'briefcase', '/official/jobs?status=published'],
    ['Applications', $stats['apps'], 'inbox', '/official/jobs'],
    ['Awaiting verification', $stats['pending'], 'clock', '/official/employers'],
    ['Skilling programmes', $stats['skills'], 'graduation', '/official/skills'],
    ['Career services', $stats['services'], 'compass', '/official/careers'],
  ] as [$label, $n, $ic, $path]): ?>
    <a href="<?= url($path) ?>" class="card card-pad flex items-center gap-3 transition hover:shadow-pop">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-500"><?= icon($ic, 'h-5 w-5') ?></span>
      <div class="min-w-0">
        <p class="text-xl font-bold leading-none text-ink"><?= number_format((int) $n) ?></p>
        <p class="mt-1 text-[11px] font-medium uppercase leading-tight tracking-wide text-ink-faint"><?= e($label) ?></p>
      </div>
    </a>
  <?php endforeach; ?>
</div>

<div class="mt-4 grid gap-4 lg:grid-cols-3">
  <div class="card lg:col-span-2">
    <div class="card-head">
      <h2 class="card-title">Job seeker registrations — last 14 days</h2>
      <span class="badge-gray"><?= array_sum($series) ?> total</span>
    </div>
    <div class="card-pad">
      <?php if (array_sum($series) === 0): ?>
        <p class="py-8 text-center text-sm text-ink-faint">No registrations in the last 14 days.</p>
      <?php else: ?>
        <div class="flex h-40 items-end gap-1.5">
          <?php foreach ($series as $day => $n): ?>
            <div class="group relative flex flex-1 flex-col items-center gap-1">
              <span class="pointer-events-none absolute -top-6 hidden rounded bg-ink px-1.5 py-0.5 text-[10px] font-semibold text-white group-hover:block"><?= (int) $n ?></span>
              <div class="w-full rounded-t bg-brand-500/80 transition-all group-hover:bg-brand-500"
                   style="height: <?= max(2, (int) round($n / $max * 130)) ?>px" title="<?= e(fdate($day)) ?>: <?= (int) $n ?>"></div>
              <span class="text-[9px] text-ink-faint"><?= e(date('d', strtotime($day))) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-head"><h2 class="card-title">Awaiting verification</h2></div>
    <?php if ($pendingList): ?>
      <ul class="divide-y divide-line">
        <?php foreach ($pendingList as $p): ?>
          <li class="flex items-center gap-3 px-5 py-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded bg-brand-50 text-brand-500"><?= icon('building', 'h-4 w-4') ?></span>
            <div class="min-w-0 flex-1">
              <a href="<?= url('/official/employers/' . $p['id']) ?>" class="block truncate text-sm font-medium text-ink hover:text-brand-700"><?= e($p['company_name']) ?></a>
              <p class="truncate text-xs text-ink-faint"><?= e($p['district'] ?: '—') ?> · joined <?= e(fdate($p['created_at'])) ?></p>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="p-5 text-sm text-ink-faint">Nothing waiting — the queue is clear.</p>
    <?php endif; ?>
  </div>
</div>

<div class="mt-4 grid items-start gap-4 lg:grid-cols-3">
  <div class="card">
    <div class="card-head"><h2 class="card-title">Live vacancies by district</h2></div>
    <?php if ($byDistrict): ?>
      <ul class="space-y-2.5 p-5">
        <?php foreach ($byDistrict as $d): ?>
          <li>
            <div class="flex items-center justify-between text-xs">
              <span class="font-medium text-ink"><?= e($d['district']) ?></span>
              <span class="text-ink-faint"><?= (int) $d['n'] ?></span>
            </div>
            <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-black/[0.06]">
              <div class="h-full rounded-full bg-brand-500" style="width: <?= (int) round($d['n'] / $maxDistrict * 100) ?>%"></div>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?><p class="p-5 text-sm text-ink-faint">No published vacancies yet.</p><?php endif; ?>
  </div>

  <div class="card">
    <div class="card-head"><h2 class="card-title">Most applied vacancies</h2></div>
    <?php if ($topApplications): ?>
      <ul class="divide-y divide-line">
        <?php foreach ($topApplications as $t): ?>
          <li class="flex items-center gap-3 px-5 py-3">
            <div class="min-w-0 flex-1">
              <p class="truncate text-sm font-medium text-ink"><?= e($t['title']) ?></p>
              <p class="truncate text-xs text-ink-faint"><?= e($t['company_name']) ?></p>
            </div>
            <span class="badge-blue shrink-0"><?= (int) $t['n'] ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?><p class="p-5 text-sm text-ink-faint">No applications yet.</p><?php endif; ?>
  </div>

  <div class="card">
    <div class="card-head"><h2 class="card-title">Recent activity</h2></div>
    <?php if ($activity): ?>
      <ul class="divide-y divide-line">
        <?php foreach ($activity as $a): ?>
          <li class="px-5 py-2.5">
            <p class="truncate text-sm text-ink"><?= e($a['description'] ?: $a['action']) ?></p>
            <p class="text-xs text-ink-faint"><?= e(ucfirst($a['actor_type'])) ?> · <?= e(fdate($a['created_at'], 'd M, g:i a')) ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?><p class="p-5 text-sm text-ink-faint">No activity recorded yet.</p><?php endif; ?>
  </div>
</div>
