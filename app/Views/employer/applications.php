<?php /** @var array $applications @var array $statuses */
$tone = ['applied' => 'badge-blue', 'shortlisted' => 'badge-amber', 'interview' => 'badge-amber',
         'selected' => 'badge-green', 'rejected' => 'badge-red', 'withdrawn' => 'badge-gray']; ?>
<?php partial('dash-header', [
  'title' => 'All applications',
  'sub'   => 'Every application across your job titles, most recent first.',
]); ?>

<?php if (!$applications): ?>
  <?php partial('empty-state', [
    'icon' => 'inbox', 'title' => 'No applications yet',
    'message' => 'Once candidates start applying to your published vacancies, they will all be listed here.',
    'action' => '<a href="' . url('/employer/jobs') . '" class="btn-primary btn-sm">View job titles</a>',
  ]); ?>
<?php else: ?>
  <div class="card">
    <div class="scroll-x">
      <table class="table">
        <thead><tr><th>Candidate</th><th>Job title</th><th>Applied</th><th>Match</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($applications as $a): ?>
            <tr>
              <td>
                <div class="flex items-center gap-2.5">
                  <span class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-100 text-[11px] font-bold text-brand-700">
                    <?php if ($a['photo']): ?><img src="<?= e(upload_url($a['photo'])) ?>" alt="" class="h-full w-full object-cover">
                    <?php else: ?><?= e(initials($a['name'])) ?><?php endif; ?>
                  </span>
                  <span class="min-w-0">
                    <span class="flex items-center gap-1.5 font-medium text-ink">
                      <?= e($a['name']) ?>
                      <?php if ($a['kyc_status'] === 'verified'): ?><span class="text-success" title="e-KYC verified"><?= icon('shield-check', 'h-3.5 w-3.5') ?></span><?php endif; ?>
                    </span>
                    <?php if ($a['headline']): ?><span class="block truncate text-xs text-ink-faint"><?= e(str_excerpt($a['headline'], 50)) ?></span><?php endif; ?>
                  </span>
                </div>
              </td>
              <td><span class="block text-sm"><?= e($a['title']) ?></span><span class="text-xs text-ink-faint"><?= e($a['code']) ?></span></td>
              <td class="whitespace-nowrap text-sm text-ink-soft"><?= e(fdate($a['applied_at'])) ?></td>
              <td class="text-sm"><?= $a['match_score'] !== null ? (int) $a['match_score'] . '%' : '—' ?></td>
              <td><span class="<?= $tone[$a['status']] ?? 'badge-gray' ?>"><?= e($statuses[$a['status']] ?? $a['status']) ?></span></td>
              <td class="text-right">
                <a href="<?= url('/employer/jobs/' . $a['job_id'] . '/applicants') ?>" class="text-sm font-semibold text-brand-500 hover:text-brand-700">Open</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
