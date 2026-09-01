<?php
/** @var array $employer @var array $stats @var array $funnel @var array $recent @var array $series @var int $progress */
$max = max(1, max($series));
$tone = ['applied' => 'badge-blue', 'shortlisted' => 'badge-amber', 'interview' => 'badge-amber',
         'selected' => 'badge-green', 'rejected' => 'badge-red', 'withdrawn' => 'badge-gray'];
?>
<?php partial('dash-header', [
  'title' => $employer['company_name'],
  'sub'   => 'How your vacancies and applications are performing.',
  'actions' => '<a href="' . url('/employer/jobs/create') . '" class="btn-primary">' . icon('plus', 'h-4 w-4') . 'Publish a job title</a>',
]); ?>

<?php if (!$employer['profile_completed']): ?>
  <div class="mb-4 flex flex-wrap items-center gap-4 rounded-card border border-warning/30 bg-warning/5 p-5">
    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-warning/15 text-warning"><?= icon('alert') ?></span>
    <div class="min-w-0 flex-1">
      <p class="text-sm font-semibold text-ink">Complete your organisation profile</p>
      <p class="text-sm text-ink-soft">Your profile is <?= (int) $progress ?>% complete. Job titles can be drafted now, but verification needs a complete profile.</p>
    </div>
    <a href="<?= url('/employer/profile') ?>" class="btn-primary btn-sm shrink-0">Continue</a>
  </div>
<?php elseif ($employer['status'] === 'pending'): ?>
  <div class="mb-4 flex flex-wrap items-center gap-4 rounded-card border border-brand-200 bg-brand-50 p-5">
    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-700"><?= icon('clock') ?></span>
    <div class="min-w-0 flex-1">
      <p class="text-sm font-semibold text-ink">Verification in progress</p>
      <p class="text-sm text-ink-soft">The verification desk usually responds within three working days. You can publish job titles in the meantime.</p>
    </div>
  </div>
<?php elseif ($employer['status'] === 'rejected'): ?>
  <div class="mb-4 flex flex-wrap items-center gap-4 rounded-card border border-danger/30 bg-danger/5 p-5">
    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-danger/15 text-danger"><?= icon('x-circle') ?></span>
    <div class="min-w-0 flex-1">
      <p class="text-sm font-semibold text-ink">Verification was not approved</p>
      <p class="text-sm text-ink-soft"><?= e($employer['remarks'] ?: 'Please review your details and resubmit.') ?></p>
    </div>
    <a href="<?= url('/employer/profile') ?>" class="btn-primary btn-sm shrink-0">Review profile</a>
  </div>
<?php endif; ?>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
  <?php foreach ([
    ['Published vacancies', $stats['published'], 'briefcase', '/employer/jobs?status=published'],
    ['Open positions', $stats['vacancies'], 'users', '/employer/jobs'],
    ['Drafts', $stats['drafts'], 'document', '/employer/jobs?status=draft'],
    ['Applications', $stats['applications'], 'inbox', '/employer/applications'],
    ['In the pipeline', $stats['shortlisted'], 'target', '/employer/applications'],
    ['Job views', $stats['views'], 'eye', '/employer/jobs'],
  ] as [$label, $n, $ic, $path]): ?>
    <a href="<?= url($path) ?>" class="card card-pad flex items-center gap-4 transition hover:shadow-pop">
      <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-500"><?= icon($ic) ?></span>
      <div>
        <p class="text-2xl font-bold leading-none text-ink"><?= number_format((int) $n) ?></p>
        <p class="mt-1 text-xs font-medium uppercase tracking-wide text-ink-faint"><?= e($label) ?></p>
      </div>
    </a>
  <?php endforeach; ?>
</div>

<div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
  <!-- applications trend -->
  <div class="card lg:col-span-2">
    <div class="card-head">
      <h2 class="card-title">Applications — last 14 days</h2>
      <span class="badge-gray"><?= array_sum($series) ?> total</span>
    </div>
    <div class="card-pad">
      <?php if (array_sum($series) === 0): ?>
        <p class="py-8 text-center text-sm text-ink-faint">No applications in the last 14 days.</p>
      <?php else: ?>
        <div class="flex h-40 items-end gap-1.5">
          <?php foreach ($series as $day => $n): ?>
            <div class="group relative flex flex-1 flex-col items-center gap-1">
              <span class="pointer-events-none absolute -top-6 hidden rounded bg-ink px-1.5 py-0.5 text-[10px] font-semibold text-white group-hover:block">
                <?= (int) $n ?>
              </span>
              <div class="w-full rounded-t bg-brand-500/80 transition-all group-hover:bg-brand-500"
                   style="height: <?= max(2, (int) round($n / $max * 130)) ?>px" title="<?= e(fdate($day)) ?>: <?= (int) $n ?>"></div>
              <span class="text-[9px] text-ink-faint"><?= e(date('d', strtotime($day))) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- recent applicants -->
  <div class="card">
    <div class="card-head">
      <h2 class="card-title">Latest applicants</h2>
      <a href="<?= url('/employer/applications') ?>" class="text-sm font-semibold text-brand-500 hover:text-brand-700">All</a>
    </div>
    <?php if ($recent): ?>
      <ul class="divide-y divide-line">
        <?php foreach ($recent as $r): ?>
          <li class="flex items-center gap-3 px-5 py-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-100 text-[11px] font-bold text-brand-700">
              <?php if ($r['photo']): ?><img src="<?= e(upload_url($r['photo'])) ?>" alt="" class="h-full w-full object-cover">
              <?php else: ?><?= e(initials($r['name'])) ?><?php endif; ?>
            </span>
            <div class="min-w-0 flex-1">
              <p class="flex items-center gap-1.5 truncate text-sm font-medium text-ink">
                <?= e($r['name']) ?>
                <?php if ($r['kyc_status'] === 'verified'): ?><span class="text-success"><?= icon('shield-check', 'h-3.5 w-3.5') ?></span><?php endif; ?>
              </p>
              <a href="<?= url('/employer/jobs/' . $r['job_id'] . '/applicants') ?>" class="block truncate text-xs text-ink-faint hover:text-brand-700"><?= e($r['title']) ?></a>
            </div>
            <span class="<?= $tone[$r['status']] ?? 'badge-gray' ?> shrink-0"><?= e(ucfirst($r['status'])) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <div class="p-5"><?php partial('empty-state', [
        'icon' => 'inbox', 'title' => 'No applications yet',
        'message' => 'Publish a job title and applications will appear here.',
      ]); ?></div>
    <?php endif; ?>
  </div>
</div>

<!-- per-job funnel -->
<?php if ($funnel): ?>
  <div class="card mt-4">
    <div class="card-head">
      <h2 class="card-title">Job title performance</h2>
      <a href="<?= url('/employer/jobs') ?>" class="text-sm font-semibold text-brand-500 hover:text-brand-700">All job titles</a>
    </div>
    <div class="scroll-x">
      <table class="table">
        <thead>
          <tr>
            <th>Job title</th><th>Status</th><th class="text-right">Views</th>
            <th class="text-right">Applied</th><th class="text-right">Shortlisted</th>
            <th class="text-right">Interview</th><th class="text-right">Selected</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($funnel as $f): ?>
            <tr>
              <td>
                <span class="block font-medium text-ink"><?= e($f['title']) ?></span>
                <span class="text-xs text-ink-faint"><?= e($f['code']) ?> · <?= (int) $f['vacancies'] ?> vacancy(s)</span>
              </td>
              <td><span class="<?= $f['status'] === 'published' ? 'badge-green' : ($f['status'] === 'draft' ? 'badge-amber' : 'badge-gray') ?>"><?= e(ucfirst($f['status'])) ?></span></td>
              <td class="text-right"><?= (int) $f['views'] ?></td>
              <td class="text-right font-semibold"><?= (int) $f['applications'] ?></td>
              <td class="text-right"><?= (int) $f['shortlisted'] ?></td>
              <td class="text-right"><?= (int) $f['interview'] ?></td>
              <td class="text-right"><?= (int) $f['selected'] ?></td>
              <td class="text-right">
                <a href="<?= url('/employer/jobs/' . $f['id'] . '/applicants') ?>" class="text-sm font-semibold text-brand-500 hover:text-brand-700">Open</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
