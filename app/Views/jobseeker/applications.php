<?php
/** @var array $applications @var array $counts @var int $total @var string|null $active */
use App\Core\Lookup;

$tone = [
    'applied' => 'badge-blue', 'shortlisted' => 'badge-amber', 'interview' => 'badge-amber',
    'selected' => 'badge-green', 'rejected' => 'badge-red', 'withdrawn' => 'badge-gray',
];
$steps = ['applied', 'shortlisted', 'interview', 'selected'];
?>
<?php partial('dash-header', [
  'title' => 'My applications',
  'sub'   => 'Every vacancy you have applied to, and where each one stands.',
  'actions' => '<a href="' . url('/jobs') . '" class="btn-outline">' . icon('search', 'h-4 w-4') . 'Find more jobs</a>',
]); ?>

<!-- status filter -->
<div class="scroll-x -mx-1 mb-4">
  <div class="flex gap-2 px-1 pb-1">
    <a href="<?= url('/dashboard/applications') ?>"
       class="chip whitespace-nowrap <?= !$active ? '!border-brand-500 !bg-brand-50 !text-brand-700' : '' ?>">All <span class="font-bold"><?= (int) $total ?></span></a>
    <?php foreach (Lookup::APPLICATION_STATUS as $k => $label): if (empty($counts[$k])) { continue; } ?>
      <a href="<?= url('/dashboard/applications', ['status' => $k]) ?>"
         class="chip whitespace-nowrap <?= $active === $k ? '!border-brand-500 !bg-brand-50 !text-brand-700' : '' ?>">
        <?= e($label) ?> <span class="font-bold"><?= (int) $counts[$k] ?></span></a>
    <?php endforeach; ?>
  </div>
</div>

<?php if (!$applications): ?>
  <?php partial('empty-state', [
    'icon' => 'send',
    'title' => $active ? 'No applications with this status' : 'You have not applied to anything yet',
    'message' => $active ? 'Try a different status filter.' : 'Search for vacancies that match your profile and apply — each one will appear here with its current status.',
    'action' => '<a href="' . url('/jobs') . '" class="btn-primary btn-sm">Search jobs</a>',
  ]); ?>
<?php else: ?>
  <div class="space-y-3">
    <?php foreach ($applications as $a):
        $stepIndex = array_search($a['status'], $steps, true);
        $closed = in_array($a['status'], ['rejected', 'withdrawn'], true); ?>
      <article class="card card-pad">
        <div class="flex flex-wrap items-start gap-4">
          <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded bg-brand-50 text-brand-500">
            <?php if ($a['logo']): ?><img src="<?= e(upload_url($a['logo'])) ?>" alt="" class="h-full w-full object-cover">
            <?php else: ?><?= icon('building', 'h-5 w-5') ?><?php endif; ?>
          </span>
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-start justify-between gap-2">
              <div class="min-w-0">
                <a href="<?= url('/jobs/' . $a['job_id']) ?>" class="text-sm font-semibold text-ink hover:text-brand-700"><?= e($a['title']) ?></a>
                <p class="text-sm text-ink-soft"><?= e($a['company_name']) ?> · <?= e($a['job_location'] ?: 'Kerala') ?></p>
              </div>
              <span class="<?= $tone[$a['status']] ?? 'badge-gray' ?> shrink-0"><?= e(Lookup::label(Lookup::APPLICATION_STATUS, $a['status'])) ?></span>
            </div>

            <p class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-ink-faint">
              <span>Job code <?= e($a['code']) ?></span>
              <span>Applied <?= e(fdate($a['applied_at'], 'd M Y')) ?></span>
              <?php if ($a['last_date']): ?><span>Closes <?= e(fdate($a['last_date'])) ?></span><?php endif; ?>
            </p>

            <?php if (!$closed): ?>
              <!-- progress track -->
              <ol class="mt-4 flex items-center gap-1" aria-label="Application progress">
                <?php foreach ($steps as $i => $s):
                    $done = $stepIndex !== false && $i <= $stepIndex; ?>
                  <li class="flex flex-1 items-center gap-1">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[10px] font-bold <?= $done ? 'bg-brand-500 text-white' : 'bg-black/[0.06] text-ink-faint' ?>">
                      <?= $done ? '✓' : $i + 1 ?>
                    </span>
                    <span class="hidden text-[11px] font-medium sm:block <?= $done ? 'text-ink' : 'text-ink-faint' ?>"><?= e(Lookup::label(Lookup::APPLICATION_STATUS, $s)) ?></span>
                    <?php if ($i < count($steps) - 1): ?>
                      <span class="h-0.5 flex-1 rounded-full <?= $stepIndex !== false && $i < $stepIndex ? 'bg-brand-500' : 'bg-line' ?>"></span>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ol>
            <?php endif; ?>

            <?php if ($a['employer_remarks']): ?>
              <p class="mt-3 rounded-card bg-canvas px-3 py-2 text-sm text-ink-soft">
                <span class="font-semibold text-ink">Employer note:</span> <?= e($a['employer_remarks']) ?>
              </p>
            <?php endif; ?>

            <div class="mt-4 flex flex-wrap gap-2">
              <a href="<?= url('/jobs/' . $a['job_id']) ?>" class="btn-outline btn-sm"><?= icon('eye', 'h-3.5 w-3.5') ?>View job</a>
              <?php if (!in_array($a['status'], ['selected', 'rejected', 'withdrawn'], true)): ?>
                <form method="post" action="<?= url('/dashboard/applications/' . $a['id'] . '/withdraw') ?>"
                      data-confirm="Withdraw this application? You will not be able to re-apply to this vacancy.">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn-ghost btn-sm text-danger hover:bg-danger/5">Withdraw</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
