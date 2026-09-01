<?php
/** @var array $jobs @var array $counts @var int $total @var string|null $active */
$tone = ['published' => 'badge-green', 'draft' => 'badge-amber', 'closed' => 'badge-gray', 'archived' => 'badge-gray'];
?>
<?php partial('dash-header', [
  'title' => 'Job titles',
  'sub'   => 'Every vacancy you have created, with its application funnel.',
  'actions' => '<a href="' . url('/employer/jobs/create') . '" class="btn-primary">' . icon('plus', 'h-4 w-4') . 'Publish a job title</a>',
]); ?>

<div class="scroll-x -mx-1 mb-4">
  <div class="flex gap-2 px-1 pb-1">
    <a href="<?= url('/employer/jobs') ?>" class="chip whitespace-nowrap <?= !$active ? '!border-brand-500 !bg-brand-50 !text-brand-700' : '' ?>">All <span class="font-bold"><?= (int) $total ?></span></a>
    <?php foreach (['published' => 'Published', 'draft' => 'Drafts', 'closed' => 'Closed'] as $k => $label): if (empty($counts[$k])) { continue; } ?>
      <a href="<?= url('/employer/jobs', ['status' => $k]) ?>" class="chip whitespace-nowrap <?= $active === $k ? '!border-brand-500 !bg-brand-50 !text-brand-700' : '' ?>">
        <?= e($label) ?> <span class="font-bold"><?= (int) $counts[$k] ?></span></a>
    <?php endforeach; ?>
  </div>
</div>

<?php if (!$jobs): ?>
  <?php partial('empty-state', [
    'icon' => 'briefcase',
    'title' => $active ? 'No job titles with this status' : 'No job titles yet',
    'message' => 'Publish your first vacancy as a curation sheet — the wizard walks you through the role, eligibility, terms and selection process.',
    'action' => '<a href="' . url('/employer/jobs/create') . '" class="btn-primary btn-sm">Publish a job title</a>',
  ]); ?>
<?php else: ?>
  <div class="space-y-3">
    <?php foreach ($jobs as $j):
        $expired = $j['last_date'] && strtotime($j['last_date']) < strtotime('today'); ?>
      <article class="card card-pad">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <h2 class="text-base font-semibold text-ink"><?= e($j['title']) ?></h2>
              <span class="<?= $tone[$j['status']] ?? 'badge-gray' ?>"><?= e(ucfirst($j['status'])) ?></span>
              <?php if ($expired && $j['status'] === 'published'): ?><span class="badge-red">Past last date</span><?php endif; ?>
            </div>
            <p class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-ink-faint">
              <span><?= e($j['code']) ?></span>
              <?php if ($j['category_name']): ?><span><?= e($j['category_name']) ?></span><?php endif; ?>
              <span><?= e($j['job_location'] ?: 'Location not set') ?></span>
              <span><?= (int) $j['vacancies'] ?> vacancy(s)</span>
              <?php if ($j['last_date']): ?><span>Closes <?= e(fdate($j['last_date'])) ?></span><?php endif; ?>
            </p>
          </div>

          <div class="flex shrink-0 flex-wrap gap-2">
            <a href="<?= url('/employer/jobs/' . $j['id'] . '/applicants') ?>" class="btn-outline btn-sm">
              <?= icon('users', 'h-3.5 w-3.5') ?>Applicants (<?= (int) $j['applications'] ?>)
            </a>
            <a href="<?= url('/employer/jobs/' . $j['id'] . '/edit') ?>" class="btn-ghost btn-sm"><?= icon('edit', 'h-3.5 w-3.5') ?>Edit</a>
            <?php if ($j['status'] === 'published'): ?>
              <a href="<?= url('/jobs/' . $j['id']) ?>" target="_blank" rel="noopener" class="btn-ghost btn-sm"><?= icon('external', 'h-3.5 w-3.5') ?>View live</a>
            <?php endif; ?>
          </div>
        </div>

        <!-- funnel -->
        <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-line pt-4 sm:grid-cols-4">
          <?php foreach ([
            ['Views', (int) $j['views'], 'eye'],
            ['Applications', (int) $j['applications'], 'inbox'],
            ['Shortlisted', (int) $j['shortlisted'], 'target'],
            ['Vacancies', (int) $j['vacancies'], 'users'],
          ] as [$label, $n, $ic]): ?>
            <div class="flex items-center gap-2">
              <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-500"><?= icon($ic, 'h-4 w-4') ?></span>
              <div>
                <dd class="text-base font-bold leading-none text-ink"><?= $n ?></dd>
                <dt class="text-[11px] font-medium uppercase tracking-wide text-ink-faint"><?= e($label) ?></dt>
              </div>
            </div>
          <?php endforeach; ?>
        </dl>

        <div class="mt-4 flex flex-wrap gap-2 border-t border-line pt-4">
          <?php if ($j['status'] === 'draft'): ?>
            <form method="post" action="<?= url('/employer/jobs/' . $j['id'] . '/publish') ?>">
              <?= csrf_field() ?>
              <button type="submit" class="btn-primary btn-sm"><?= icon('send', 'h-3.5 w-3.5') ?>Publish</button>
            </form>
          <?php elseif ($j['status'] === 'published'): ?>
            <form method="post" action="<?= url('/employer/jobs/' . $j['id'] . '/close') ?>" data-confirm="Close this vacancy? Candidates will no longer be able to apply.">
              <?= csrf_field() ?>
              <button type="submit" class="btn-outline btn-sm">Close vacancy</button>
            </form>
          <?php elseif ($j['status'] === 'closed'): ?>
            <form method="post" action="<?= url('/employer/jobs/' . $j['id'] . '/reopen') ?>">
              <?= csrf_field() ?>
              <button type="submit" class="btn-outline btn-sm">Reopen</button>
            </form>
          <?php endif; ?>
          <?php if ((int) $j['applications'] === 0): ?>
            <form method="post" action="<?= url('/employer/jobs/' . $j['id'] . '/delete') ?>" data-confirm="Delete this job title? This cannot be undone.">
              <?= csrf_field() ?>
              <button type="submit" class="btn-ghost btn-sm text-danger hover:bg-danger/5"><?= icon('trash', 'h-3.5 w-3.5') ?>Delete</button>
            </form>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
