<?php
/** @var array $seeker @var array $summary @var array $stats @var array $applications @var array $saved @var array $recommended */
use App\Core\Lookup;

$statusTone = [
    'applied' => 'badge-blue', 'shortlisted' => 'badge-amber', 'interview' => 'badge-amber',
    'selected' => 'badge-green', 'rejected' => 'badge-red', 'withdrawn' => 'badge-gray',
];
$kycTone = [
    'verified'    => ['badge-green', 'shield-check', 'Verified'],
    'pending'     => ['badge-amber', 'clock', 'Verification in progress'],
    'failed'      => ['badge-red', 'x-circle', 'Verification failed'],
    'not_started' => ['badge-gray', 'shield', 'Not started'],
];
[$kycBadge, $kycIcon, $kycLabel] = $kycTone[$seeker['kyc_status']] ?? $kycTone['not_started'];
?>
<?php partial('dash-header', [
  'title' => 'Welcome back, ' . explode(' ', $seeker['name'])[0],
  'sub'   => 'Here is where your profile and applications stand today.',
  'actions' => '<a href="' . url('/jobs') . '" class="btn-primary">' . icon('search', 'h-4 w-4') . 'Find jobs</a>',
]); ?>

<!-- identity + verification -->
<div class="grid gap-4 sm:grid-cols-3">
  <div class="card card-pad">
    <div class="flex items-start justify-between gap-2">
      <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-50 text-brand-500"><?= icon('mail', 'h-4 w-4') ?></span>
      <?php if ($seeker['email_verified']): ?><span class="badge-green"><?= icon('check', 'h-3 w-3') ?>Verified</span>
      <?php else: ?><span class="badge-amber"><?= icon('alert', 'h-3 w-3') ?>Unverified</span><?php endif; ?>
    </div>
    <p class="mt-3 text-xs font-medium uppercase tracking-wide text-ink-faint">E-mail address</p>
    <p class="truncate text-sm font-semibold text-ink" title="<?= e($seeker['email']) ?>"><?= e($seeker['email']) ?></p>
  </div>

  <div class="card card-pad">
    <div class="flex items-start justify-between gap-2">
      <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-50 text-brand-500"><?= icon('phone', 'h-4 w-4') ?></span>
      <?php if ($seeker['mobile_verified']): ?><span class="badge-green"><?= icon('check', 'h-3 w-3') ?>Verified</span>
      <?php else: ?><span class="badge-gray">Not verified</span><?php endif; ?>
    </div>
    <p class="mt-3 text-xs font-medium uppercase tracking-wide text-ink-faint">Mobile number</p>
    <p class="text-sm font-semibold text-ink"><?= $seeker['mobile'] ? '+91 ' . e($seeker['mobile']) : '—' ?></p>
  </div>

  <div class="card card-pad">
    <div class="flex items-start justify-between gap-2">
      <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-50 text-brand-500"><?= icon('fingerprint', 'h-4 w-4') ?></span>
      <span class="<?= $kycBadge ?>"><?= icon($kycIcon, 'h-3 w-3') ?><?= e($kycLabel) ?></span>
    </div>
    <p class="mt-3 text-xs font-medium uppercase tracking-wide text-ink-faint">e-KYC</p>
    <?php if ($seeker['kyc_status'] === 'verified'): ?>
      <p class="text-sm font-semibold text-ink"><?= e(Lookup::label(Lookup::KYC_METHODS, $seeker['kyc_method'], 'Verified')) ?></p>
      <p class="text-xs text-ink-faint"><?= e($seeker['kyc_ref']) ?> · <?= e(fdate($seeker['kyc_verified_at'])) ?></p>
    <?php else: ?>
      <a href="<?= url('/dashboard/kyc') ?>" class="text-sm font-semibold text-brand-500 hover:text-brand-700">Complete e-KYC →</a>
    <?php endif; ?>
  </div>
</div>

<!-- counters -->
<div class="mt-4 grid gap-4 sm:grid-cols-4">
  <?php foreach ([
    ['Applications', $stats['applications'], 'send', '/dashboard/applications'],
    ['In progress', $stats['shortlisted'], 'target', '/dashboard/applications'],
    ['Saved jobs', $stats['saved'], 'bookmark', '/dashboard/saved'],
    ['Enrolments', $stats['enrolments'], 'graduation', '/skills'],
  ] as [$label, $n, $ic, $path]): ?>
    <a href="<?= url($path) ?>" class="card card-pad flex items-center gap-3 transition hover:shadow-pop">
      <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-brand-500"><?= icon($ic, 'h-5 w-5') ?></span>
      <span>
        <span class="block text-xl font-bold text-ink"><?= (int) $n ?></span>
        <span class="block text-xs font-medium uppercase tracking-wide text-ink-faint"><?= e($label) ?></span>
      </span>
    </a>
  <?php endforeach; ?>
</div>

<div class="mt-4 grid gap-4 lg:grid-cols-3">
  <!-- profile completeness -->
  <div class="card lg:col-span-1">
    <div class="card-head"><h2 class="card-title">Complete your profile</h2><span class="badge-blue"><?= (int) $summary['score'] ?>%</span></div>
    <div class="card-pad">
      <div class="h-2 w-full overflow-hidden rounded-full bg-black/10">
        <div class="h-full rounded-full bg-brand-500 transition-all" style="width: <?= (int) $summary['score'] ?>%"></div>
      </div>
      <p class="mt-2 text-xs text-ink-soft">Employers shortlist complete profiles first.</p>
      <ul class="mt-4 space-y-2">
        <?php foreach ($summary['items'] as $item): ?>
          <li>
            <a href="<?= url($item['path']) ?>" class="flex items-start gap-2.5 rounded px-2 py-1.5 text-sm transition hover:bg-brand-50/60">
              <span class="mt-0.5 shrink-0 <?= $item['done'] ? 'text-success' : 'text-ink-faint' ?>">
                <?= icon($item['done'] ? 'check-circle' : 'plus', 'h-4 w-4') ?>
              </span>
              <span class="flex-1 <?= $item['done'] ? 'text-ink-faint line-through' : 'text-ink-soft' ?>"><?= e($item['label']) ?></span>
              <span class="shrink-0 text-xs font-semibold text-ink-faint">+<?= (int) $item['weight'] ?></span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <div class="space-y-4 lg:col-span-2">
    <!-- recent applications -->
    <div class="card">
      <div class="card-head">
        <h2 class="card-title">Recent applications</h2>
        <a href="<?= url('/dashboard/applications') ?>" class="text-sm font-semibold text-brand-500 hover:text-brand-700">View all</a>
      </div>
      <?php if ($applications): ?>
        <ul class="divide-y divide-line">
          <?php foreach ($applications as $a): ?>
            <li class="flex items-center gap-3 px-5 py-3.5">
              <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded bg-brand-50 text-brand-500"><?= icon('briefcase', 'h-4 w-4') ?></span>
              <div class="min-w-0 flex-1">
                <a href="<?= url('/jobs/' . $a['job_id']) ?>" class="block truncate text-sm font-semibold text-ink hover:text-brand-700"><?= e($a['title']) ?></a>
                <p class="truncate text-xs text-ink-soft"><?= e($a['company_name']) ?> · applied <?= e(fdate($a['applied_at'])) ?></p>
              </div>
              <span class="<?= $statusTone[$a['status']] ?? 'badge-gray' ?> shrink-0"><?= e(Lookup::label(Lookup::APPLICATION_STATUS, $a['status'])) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <div class="p-5"><?php partial('empty-state', [
          'icon' => 'send', 'title' => 'No applications yet',
          'message' => 'Search for a vacancy that matches your profile and apply — your applications will be tracked here.',
          'action' => '<a href="' . url('/jobs') . '" class="btn-primary btn-sm">Search jobs</a>',
        ]); ?></div>
      <?php endif; ?>
    </div>

    <!-- recommended -->
    <?php if ($recommended): ?>
      <div class="card">
        <div class="card-head">
          <h2 class="card-title">Recommended for you</h2>
          <a href="<?= url('/jobs') ?>" class="text-sm font-semibold text-brand-500 hover:text-brand-700">More jobs</a>
        </div>
        <ul class="divide-y divide-line">
          <?php foreach ($recommended as $r): ?>
            <li class="flex items-center gap-3 px-5 py-3.5">
              <div class="min-w-0 flex-1">
                <a href="<?= url('/jobs/' . $r['id']) ?>" class="block truncate text-sm font-semibold text-ink hover:text-brand-700"><?= e($r['title']) ?></a>
                <p class="truncate text-xs text-ink-soft"><?= e($r['company_name']) ?> · <?= e($r['job_location'] ?: 'Kerala') ?> · <?= e(salary_range($r['salary_min'], $r['salary_max'])) ?></p>
              </div>
              <a href="<?= url('/jobs/' . $r['id']) ?>" class="btn-outline btn-sm shrink-0">View</a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- saved -->
    <?php if ($saved): ?>
      <div class="card">
        <div class="card-head">
          <h2 class="card-title">Saved for later</h2>
          <a href="<?= url('/dashboard/saved') ?>" class="text-sm font-semibold text-brand-500 hover:text-brand-700">View all</a>
        </div>
        <ul class="divide-y divide-line">
          <?php foreach ($saved as $s): ?>
            <li class="flex items-center gap-3 px-5 py-3">
              <span class="shrink-0 text-brand-300"><?= icon('bookmark', 'h-4 w-4') ?></span>
              <div class="min-w-0 flex-1">
                <a href="<?= url('/jobs/' . $s['job_id']) ?>" class="block truncate text-sm font-medium text-ink hover:text-brand-700"><?= e($s['title']) ?></a>
                <p class="truncate text-xs text-ink-faint"><?= e($s['company_name']) ?> · closes <?= e(fdate($s['last_date'])) ?></p>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
  </div>
</div>
