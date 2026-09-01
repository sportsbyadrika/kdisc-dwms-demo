<?php
/** @var array $employer @var array $documents @var array $jobs @var array $ownership */
$e = $employer;
$tone = ['verified' => 'badge-green', 'pending' => 'badge-amber', 'rejected' => 'badge-red', 'suspended' => 'badge-red'];
?>
<?php partial('dash-header', [
  'title' => $e['company_name'],
  'sub'   => 'Registered ' . fdate($e['created_at']) . ' · ' . $e['email'],
  'actions' => '<a href="' . url('/official/employers') . '" class="btn-ghost">' . icon('arrow-left', 'h-4 w-4') . 'Back to list</a>',
]); ?>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr,320px]">
  <div class="space-y-4">
    <div class="card">
      <div class="card-head">
        <h2 class="card-title">Submitted details</h2>
        <span class="<?= $tone[$e['status']] ?? 'badge-gray' ?>"><?= e(ucfirst($e['status'])) ?></span>
      </div>
      <dl class="divide-y divide-line">
        <?php foreach ([
          'Registered name'    => $e['company_name'],
          'Industry'           => $e['industry'],
          'Ownership'          => $ownership[$e['ownership_type']] ?? null,
          'Employees'          => $e['employee_range'],
          'Established'        => $e['established_year'],
          'Website'            => $e['website'],
          'PAN'                => $e['pan'],
          'GSTIN'              => $e['gstin'],
          'CIN'                => $e['cin'],
          'Registration number'=> $e['registration_no'],
          'Labour licence'     => $e['labour_licence_no'],
          'Address'            => trim(implode(', ', array_filter([$e['address_line1'], $e['address_line2'], $e['city'], $e['district'], $e['state'], $e['pincode']]))),
          'Contact person'     => trim(($e['contact_person'] ?: '') . ($e['contact_designation'] ? ', ' . $e['contact_designation'] : '')),
          'Contact mobile'     => $e['contact_mobile'] ? '+91 ' . $e['contact_mobile'] : null,
          'Contact e-mail'     => $e['contact_email'],
          'About'              => $e['about'],
        ] as $label => $value): ?>
          <div class="flex flex-col gap-0.5 px-5 py-3 sm:flex-row sm:gap-4">
            <dt class="w-48 shrink-0 text-xs font-medium uppercase tracking-wide text-ink-faint"><?= e($label) ?></dt>
            <dd class="text-sm <?= $value ? 'text-ink' : 'text-ink-faint' ?>"><?= $value ? e($value) : 'Not provided' ?></dd>
          </div>
        <?php endforeach; ?>
      </dl>
    </div>

    <div class="card">
      <div class="card-head"><h2 class="card-title">Documents</h2><span class="badge-gray"><?= count($documents) ?></span></div>
      <?php if ($documents): ?>
        <ul class="divide-y divide-line">
          <?php foreach ($documents as $d): ?>
            <li class="flex items-center gap-3 px-5 py-3">
              <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded bg-brand-50 text-brand-500"><?= icon('document', 'h-4 w-4') ?></span>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-ink"><?= e(ucfirst($d['doc_type'])) ?></p>
                <p class="truncate text-xs text-ink-faint"><?= e($d['label'] ?: basename($d['file_path'])) ?> · <?= e(fdate($d['created_at'])) ?></p>
              </div>
              <a href="<?= e(upload_url($d['file_path'])) ?>" target="_blank" rel="noopener" class="btn-outline btn-sm shrink-0"><?= icon('eye', 'h-3.5 w-3.5') ?>Open</a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="p-5 text-sm text-ink-faint">No documents uploaded. You can still verify from the details above, or reject with a note asking for them.</p>
      <?php endif; ?>
    </div>

    <?php if ($jobs): ?>
      <div class="card">
        <div class="card-head"><h2 class="card-title">Job titles</h2><span class="badge-gray"><?= count($jobs) ?></span></div>
        <div class="scroll-x">
          <table class="table">
            <thead><tr><th>Code</th><th>Title</th><th>Status</th><th class="text-right">Vacancies</th><th>Published</th></tr></thead>
            <tbody>
              <?php foreach ($jobs as $j): ?>
                <tr>
                  <td class="font-mono text-xs"><?= e($j['code']) ?></td>
                  <td><a href="<?= url('/jobs/' . $j['id']) ?>" target="_blank" rel="noopener" class="link"><?= e($j['title']) ?></a></td>
                  <td><span class="<?= $j['status'] === 'published' ? 'badge-green' : 'badge-gray' ?>"><?= e(ucfirst($j['status'])) ?></span></td>
                  <td class="text-right"><?= (int) $j['vacancies'] ?></td>
                  <td class="whitespace-nowrap text-sm text-ink-soft"><?= e(fdate($j['published_at'])) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <aside class="space-y-4 lg:sticky lg:top-20 lg:self-start">
    <div class="card card-pad">
      <h2 class="card-title">Decision</h2>
      <?php if ($e['verified_at']): ?>
        <p class="mt-1 text-xs text-ink-faint">Last reviewed <?= e(fdate($e['verified_at'], 'd M Y, g:i a')) ?></p>
      <?php endif; ?>
      <?php if ($e['remarks']): ?>
        <p class="mt-3 rounded-card bg-canvas px-3 py-2 text-sm text-ink-soft"><span class="font-semibold text-ink">Note:</span> <?= e($e['remarks']) ?></p>
      <?php endif; ?>

      <?php if (!$e['profile_completed']): ?>
        <p class="mt-3 flex items-start gap-2 rounded-card border border-warning/30 bg-warning/5 px-3 py-2 text-xs text-ink-soft">
          <span class="mt-0.5 shrink-0 text-warning"><?= icon('alert', 'h-4 w-4') ?></span>
          This organisation has not submitted a complete profile yet.
        </p>
      <?php endif; ?>

      <form method="post" action="<?= url('/official/employers/' . $e['id'] . '/decide') ?>" class="mt-4 space-y-3">
        <?= csrf_field() ?>
        <div>
          <label class="label" for="remarks">Note</label>
          <textarea id="remarks" name="remarks" rows="3" maxlength="255" class="field"
                    placeholder="Required when rejecting or suspending. The employer sees this."><?= e($e['remarks']) ?></textarea>
        </div>
        <div class="grid gap-2">
          <?php if ($e['status'] !== 'verified'): ?>
            <button type="submit" name="decision" value="verify" class="btn-primary btn-block"><?= icon('shield-check', 'h-4 w-4') ?>Mark verified</button>
          <?php endif; ?>
          <?php if ($e['status'] !== 'rejected'): ?>
            <button type="submit" name="decision" value="reject" class="btn-outline btn-block"><?= icon('x-circle', 'h-4 w-4') ?>Reject</button>
          <?php endif; ?>
          <?php if ($e['status'] !== 'suspended'): ?>
            <button type="submit" name="decision" value="suspend" class="btn-danger btn-block"
                    data-confirm-link="Suspend this organisation? Its published vacancies will be closed."><?= icon('alert', 'h-4 w-4') ?>Suspend</button>
          <?php else: ?>
            <button type="submit" name="decision" value="reinstate" class="btn-outline btn-block"><?= icon('refresh', 'h-4 w-4') ?>Reinstate (back to pending)</button>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div class="card card-pad">
      <h2 class="card-title">Verification checklist</h2>
      <ul class="mt-3 space-y-2 text-sm text-ink-soft">
        <?php foreach ([
          'The registered name matches the PAN and any uploaded certificate.',
          'PAN is well formed and not already used by another organisation.',
          'The address and contact person look plausible and reachable.',
          'Any published vacancies are genuine and charge candidates nothing.',
        ] as $item): ?>
          <li class="flex gap-2"><span class="shrink-0 text-brand-500"><?= icon('check', 'h-4 w-4') ?></span><?= e($item) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </aside>
</div>
