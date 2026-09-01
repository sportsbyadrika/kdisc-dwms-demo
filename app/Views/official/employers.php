<?php
/** @var array $employers @var array $counts @var int $total @var string $active @var array $ownership */
$tone = ['verified' => 'badge-green', 'pending' => 'badge-amber', 'rejected' => 'badge-red', 'suspended' => 'badge-red'];
?>
<?php partial('dash-header', [
  'title' => 'Employer verification',
  'sub'   => 'Review the statutory details an organisation submitted before marking it verified.',
]); ?>

<div class="scroll-x -mx-1 mb-4">
  <div class="flex gap-2 px-1 pb-1">
    <?php foreach (['pending' => 'Awaiting review', 'verified' => 'Verified', 'rejected' => 'Rejected', 'suspended' => 'Suspended'] as $k => $label): ?>
      <a href="<?= url('/official/employers', ['status' => $k]) ?>"
         class="chip whitespace-nowrap <?= $active === $k ? '!border-brand-500 !bg-brand-50 !text-brand-700' : '' ?>">
        <?= e($label) ?> <span class="font-bold"><?= (int) ($counts[$k] ?? 0) ?></span></a>
    <?php endforeach; ?>
    <a href="<?= url('/official/employers', ['status' => 'all']) ?>"
       class="chip whitespace-nowrap <?= $active === 'all' ? '!border-brand-500 !bg-brand-50 !text-brand-700' : '' ?>">All <span class="font-bold"><?= (int) $total ?></span></a>
  </div>
</div>

<?php if (!$employers): ?>
  <?php partial('empty-state', ['icon' => 'building', 'title' => 'Nothing to review', 'message' => 'No organisations with this status right now.']); ?>
<?php else: ?>
  <div class="space-y-3">
    <?php foreach ($employers as $emp): ?>
      <article class="card card-pad">
        <div class="flex flex-wrap items-start gap-4">
          <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded bg-brand-50 text-brand-500">
            <?php if ($emp['logo']): ?><img src="<?= e(upload_url($emp['logo'])) ?>" alt="" class="h-full w-full object-cover">
            <?php else: ?><?= icon('building') ?><?php endif; ?>
          </span>
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-start justify-between gap-2">
              <div class="min-w-0">
                <h2 class="flex flex-wrap items-center gap-2 text-sm font-semibold text-ink">
                  <a href="<?= url('/official/employers/' . $emp['id']) ?>" class="hover:text-brand-700 hover:underline"><?= e($emp['company_name']) ?></a>
                  <span class="<?= $tone[$emp['status']] ?? 'badge-gray' ?>"><?= e(ucfirst($emp['status'])) ?></span>
                  <?php if (!$emp['profile_completed']): ?><span class="badge-gray">Profile incomplete</span><?php endif; ?>
                </h2>
                <p class="text-sm text-ink-soft"><?= e($emp['industry'] ?: '—') ?><?= $emp['ownership_type'] ? ' · ' . e($ownership[$emp['ownership_type']]) : '' ?></p>
              </div>
              <a href="<?= url('/official/employers/' . $emp['id']) ?>" class="btn-outline btn-sm shrink-0"><?= icon('eye', 'h-3.5 w-3.5') ?>Review</a>
            </div>
            <dl class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-ink-faint">
              <div><?= e($emp['email']) ?></div>
              <?php if ($emp['pan']): ?><div>PAN <?= e($emp['pan']) ?></div><?php endif; ?>
              <?php if ($emp['district']): ?><div><?= e($emp['district']) ?></div><?php endif; ?>
              <div><?= (int) $emp['jobs'] ?> job title(s)</div>
              <div><?= (int) $emp['documents'] ?> document(s)</div>
              <div>Registered <?= e(fdate($emp['created_at'])) ?></div>
            </dl>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
