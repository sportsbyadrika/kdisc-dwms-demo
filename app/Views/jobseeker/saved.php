<?php /** @var array $jobs */ ?>
<?php partial('dash-header', [
  'title' => 'Saved jobs',
  'sub'   => 'Vacancies you bookmarked, plus anything you tried to apply to before signing in.',
  'actions' => '<a href="' . url('/jobs') . '" class="btn-outline">' . icon('search', 'h-4 w-4') . 'Search jobs</a>',
]); ?>

<?php if (!$jobs): ?>
  <?php partial('empty-state', [
    'icon' => 'bookmark', 'title' => 'Nothing saved yet',
    'message' => 'Save a vacancy from the search results or the job page and it will wait for you here.',
    'action' => '<a href="' . url('/jobs') . '" class="btn-primary btn-sm">Browse jobs</a>',
  ]); ?>
<?php else: ?>
  <div class="space-y-3">
    <?php foreach ($jobs as $j):
        $expired = $j['last_date'] && strtotime($j['last_date']) < strtotime('today');
        $open    = $j['status'] === 'published' && !$expired; ?>
      <article class="card card-pad flex flex-wrap items-start gap-4">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded bg-brand-50 text-brand-500">
          <?php if ($j['logo']): ?><img src="<?= e(upload_url($j['logo'])) ?>" alt="" class="h-full w-full object-cover">
          <?php else: ?><?= icon('building', 'h-5 w-5') ?><?php endif; ?>
        </span>
        <div class="min-w-0 flex-1">
          <div class="flex flex-wrap items-start justify-between gap-2">
            <div class="min-w-0">
              <a href="<?= url('/jobs/' . $j['id']) ?>" class="text-sm font-semibold text-ink hover:text-brand-700"><?= e($j['title']) ?></a>
              <p class="text-sm text-ink-soft"><?= e($j['company_name']) ?> · <?= e($j['job_location'] ?: 'Kerala') ?></p>
            </div>
            <?php if ($j['applied']): ?><span class="badge-green shrink-0"><?= icon('check', 'h-3 w-3') ?>Applied</span>
            <?php elseif (!$open): ?><span class="badge-gray shrink-0">Closed</span>
            <?php else: ?><span class="badge-blue shrink-0">Open</span><?php endif; ?>
          </div>

          <p class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-ink-faint">
            <span><?= e(salary_range($j['salary_min'], $j['salary_max'])) ?></span>
            <?php if ($j['last_date']): ?><span><?= $expired ? 'Closed on ' : 'Apply by ' ?><?= e(fdate($j['last_date'])) ?></span><?php endif; ?>
            <span><?= e(ucwords(str_replace('_', ' ', $j['employment_type']))) ?></span>
          </p>

          <div class="mt-4 flex flex-wrap gap-2">
            <?php if ($open && !$j['applied']): ?>
              <a href="<?= url('/jobs/' . $j['id']) ?>" class="btn-primary btn-sm"><?= icon('send', 'h-3.5 w-3.5') ?>View &amp; apply</a>
            <?php else: ?>
              <a href="<?= url('/jobs/' . $j['id']) ?>" class="btn-outline btn-sm"><?= icon('eye', 'h-3.5 w-3.5') ?>View job</a>
            <?php endif; ?>
            <form method="post" action="<?= url('/dashboard/saved/' . $j['id'] . '/remove') ?>">
              <?= csrf_field() ?>
              <button type="submit" class="btn-ghost btn-sm">Remove</button>
            </form>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
