<?php
/** @var array $job @var array $applicants @var array $counts @var int $total @var string|null $active @var array $statuses */
$tone = [
    'applied' => 'badge-blue', 'shortlisted' => 'badge-amber', 'interview' => 'badge-amber',
    'selected' => 'badge-green', 'rejected' => 'badge-red', 'withdrawn' => 'badge-gray',
];
?>
<?php partial('dash-header', [
  'title' => 'Applicants',
  'sub'   => $job['title'] . ' · ' . $job['code'] . ' · ' . ($job['job_location'] ?: 'Kerala'),
  'actions' => '<a href="' . url('/employer/jobs') . '" class="btn-ghost">' . icon('arrow-left', 'h-4 w-4') . 'All job titles</a>'
             . '<a href="' . url('/employer/jobs/' . $job['id'] . '/edit') . '" class="btn-outline">' . icon('edit', 'h-4 w-4') . 'Edit job</a>',
]); ?>

<div class="scroll-x -mx-1 mb-4">
  <div class="flex gap-2 px-1 pb-1">
    <a href="<?= url('/employer/jobs/' . $job['id'] . '/applicants') ?>"
       class="chip whitespace-nowrap <?= !$active ? '!border-brand-500 !bg-brand-50 !text-brand-700' : '' ?>">All <span class="font-bold"><?= (int) $total ?></span></a>
    <?php foreach ($statuses as $k => $label): if (empty($counts[$k])) { continue; } ?>
      <a href="<?= url('/employer/jobs/' . $job['id'] . '/applicants', ['status' => $k]) ?>"
         class="chip whitespace-nowrap <?= $active === $k ? '!border-brand-500 !bg-brand-50 !text-brand-700' : '' ?>">
        <?= e($label) ?> <span class="font-bold"><?= (int) $counts[$k] ?></span></a>
    <?php endforeach; ?>
  </div>
</div>

<?php if (!$applicants): ?>
  <?php partial('empty-state', [
    'icon' => 'inbox',
    'title' => $active ? 'No applicants with this status' : 'No applications yet',
    'message' => $active ? 'Try a different status filter.' : 'Applications will appear here as candidates apply. Sharing the vacancy link helps it reach more candidates.',
  ]); ?>
<?php else: ?>
  <div class="space-y-3">
    <?php foreach ($applicants as $a):
        $age = $a['dob'] ? (int) (new DateTime($a['dob']))->diff(new DateTime())->y : null; ?>
      <article class="card" x-data="{ open: false }">
        <div class="card-pad">
          <div class="flex flex-wrap items-start gap-4">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-100 text-sm font-bold text-brand-700">
              <?php if ($a['photo']): ?><img src="<?= e(upload_url($a['photo'])) ?>" alt="" class="h-full w-full object-cover">
              <?php else: ?><?= e(initials($a['name'])) ?><?php endif; ?>
            </span>

            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="min-w-0">
                  <h2 class="flex flex-wrap items-center gap-2 text-sm font-semibold text-ink">
                    <?= e($a['name']) ?>
                    <?php if ($a['kyc_status'] === 'verified'): ?>
                      <span class="text-success" title="e-KYC verified"><?= icon('shield-check', 'h-4 w-4') ?></span>
                    <?php endif; ?>
                  </h2>
                  <?php if ($a['headline']): ?><p class="text-sm text-ink-soft"><?= e($a['headline']) ?></p><?php endif; ?>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                  <?php if ($a['match_score'] !== null): ?>
                    <span class="badge-gray" title="Criteria met at the time of applying"><?= (int) $a['match_score'] ?>% match</span>
                  <?php endif; ?>
                  <span class="<?= $tone[$a['status']] ?? 'badge-gray' ?>"><?= e($statuses[$a['status']] ?? $a['status']) ?></span>
                </div>
              </div>

              <dl class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-ink-soft">
                <div class="flex items-center gap-1.5"><?= icon('mail', 'h-3.5 w-3.5 text-ink-faint') ?><?= e($a['email']) ?></div>
                <?php if ($a['mobile']): ?><div class="flex items-center gap-1.5"><?= icon('phone', 'h-3.5 w-3.5 text-ink-faint') ?>+91 <?= e($a['mobile']) ?></div><?php endif; ?>
                <?php if ($age): ?><div class="flex items-center gap-1.5"><?= icon('user', 'h-3.5 w-3.5 text-ink-faint') ?><?= $age ?> years</div><?php endif; ?>
                <div class="flex items-center gap-1.5"><?= icon('clock', 'h-3.5 w-3.5 text-ink-faint') ?>Applied <?= e(fdate($a['applied_at'])) ?></div>
                <div class="flex items-center gap-1.5"><?= icon('chart', 'h-3.5 w-3.5 text-ink-faint') ?>Profile <?= (int) $a['profile_score'] ?>%</div>
              </dl>

              <?php if ($a['qualifications']): ?>
                <p class="mt-2 text-xs text-ink-soft"><span class="font-semibold text-ink">Qualifications:</span> <?= e(str_excerpt($a['qualifications'], 140)) ?></p>
              <?php endif; ?>
              <?php if ($a['skills']): ?>
                <div class="mt-2 flex flex-wrap gap-1.5">
                  <?php foreach (array_slice(array_map('trim', explode(',', $a['skills'])), 0, 8) as $s): ?>
                    <span class="chip"><?= e($s) ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <div class="mt-4 flex flex-wrap gap-2">
                <?php if ($a['resume_path']): ?>
                  <a href="<?= e(upload_url($a['resume_path'])) ?>" target="_blank" rel="noopener" class="btn-outline btn-sm">
                    <?= icon('document', 'h-3.5 w-3.5') ?>Resume
                  </a>
                <?php else: ?>
                  <span class="chip">No resume attached</span>
                <?php endif; ?>
                <?php if ($a['cover_note']): ?>
                  <button type="button" @click="open = !open" class="btn-ghost btn-sm">
                    <?= icon('mail', 'h-3.5 w-3.5') ?><span x-text="open ? 'Hide message' : 'Read message'">Read message</span>
                  </button>
                <?php endif; ?>
                <?php if ($a['status'] !== 'withdrawn'): ?>
                  <button type="button" @click="open = !open" class="btn-primary btn-sm"><?= icon('edit', 'h-3.5 w-3.5') ?>Update status</button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <div x-show="open" x-cloak x-transition class="border-t border-line bg-canvas px-5 py-4 sm:px-6">
          <?php if ($a['cover_note']): ?>
            <div class="mb-4">
              <p class="text-xs font-bold uppercase tracking-wider text-ink-faint">Message from the candidate</p>
              <p class="mt-1 whitespace-pre-line text-sm text-ink-soft"><?= e($a['cover_note']) ?></p>
            </div>
          <?php endif; ?>

          <?php if ($a['status'] !== 'withdrawn'): ?>
            <form method="post" action="<?= url('/employer/applications/' . $a['id'] . '/status') ?>" class="grid gap-3 sm:grid-cols-[200px,1fr,auto] sm:items-end">
              <?= csrf_field() ?>
              <div>
                <label class="label" for="st-<?= (int) $a['id'] ?>">Status</label>
                <select id="st-<?= (int) $a['id'] ?>" name="status" class="field">
                  <?php foreach (['applied', 'shortlisted', 'interview', 'selected', 'rejected'] as $k): ?>
                    <option value="<?= $k ?>" <?= $a['status'] === $k ? 'selected' : '' ?>><?= e($statuses[$k]) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label class="label" for="rm-<?= (int) $a['id'] ?>">Note to the candidate</label>
                <input id="rm-<?= (int) $a['id'] ?>" name="remarks" maxlength="255" class="field"
                       value="<?= e($a['employer_remarks']) ?>" placeholder="Optional — shown on their application">
              </div>
              <button type="submit" class="btn-primary">Save</button>
            </form>
          <?php else: ?>
            <p class="text-sm text-ink-soft">This candidate withdrew their application on <?= e(fdate($a['updated_at'])) ?>.</p>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
