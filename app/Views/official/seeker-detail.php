<?php
/** @var array $seeker @var array $addresses @var array $documents @var array $qualifications
 *  @var array $experiences @var array $skills @var array $applications
 *  @var array $docTypes @var array $quals @var array $statuses */
$s = $seeker;
$kycTone = ['verified' => 'badge-green', 'pending' => 'badge-amber', 'failed' => 'badge-red', 'not_started' => 'badge-gray'];
?>
<?php partial('dash-header', [
  'title' => $s['name'],
  'sub'   => 'Registered ' . fdate($s['created_at']) . ' · profile ' . (int) $s['profile_score'] . '% complete',
  'actions' => '<a href="' . url('/official/seekers') . '" class="btn-ghost">' . icon('arrow-left', 'h-4 w-4') . 'Back to registry</a>',
]); ?>

<div class="grid gap-4 lg:grid-cols-[1fr,320px]">
  <div class="space-y-4">
    <div class="card">
      <div class="card-head"><h2 class="card-title">Identity</h2>
        <span class="<?= $kycTone[$s['kyc_status']] ?? 'badge-gray' ?>">e-KYC <?= e(str_replace('_', ' ', $s['kyc_status'])) ?></span>
      </div>
      <dl class="divide-y divide-line">
        <?php foreach ([
          'E-mail'      => $s['email'] . ($s['email_verified'] ? ' (verified)' : ' (unverified)'),
          'Mobile'      => $s['mobile'] ? '+91 ' . $s['mobile'] : null,
          'Date of birth' => $s['dob'] ? fdate($s['dob']) : null,
          'Gender'      => $s['gender'] ? ucfirst($s['gender']) : null,
          'Headline'    => $s['headline'],
          'e-KYC method'=> $s['kyc_method'] ? ucfirst(str_replace('_', ' ', $s['kyc_method'])) : null,
          'e-KYC reference' => $s['kyc_ref'],
          'Consent recorded' => $s['kyc_consent_at'] ? fdate($s['kyc_consent_at'], 'd M Y, g:i a') : null,
        ] as $label => $value): ?>
          <div class="flex flex-col gap-0.5 px-5 py-3 sm:flex-row sm:gap-4">
            <dt class="w-44 shrink-0 text-xs font-medium uppercase tracking-wide text-ink-faint"><?= e($label) ?></dt>
            <dd class="text-sm <?= $value ? 'text-ink' : 'text-ink-faint' ?>"><?= $value ? e($value) : 'Not provided' ?></dd>
          </div>
        <?php endforeach; ?>
      </dl>
    </div>

    <?php if ($addresses): ?>
      <div class="card">
        <div class="card-head"><h2 class="card-title">Addresses</h2></div>
        <ul class="divide-y divide-line">
          <?php foreach ($addresses as $a): ?>
            <li class="px-5 py-3">
              <p class="text-xs font-medium uppercase tracking-wide text-ink-faint"><?= e(ucfirst($a['address_type'])) ?></p>
              <p class="text-sm text-ink"><?= e(implode(', ', array_filter([$a['line1'], $a['line2'], $a['city'], $a['district'], $a['state'], $a['pincode']]))) ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-head"><h2 class="card-title">Documents</h2><span class="badge-gray"><?= count($documents) ?></span></div>
      <?php if ($documents): ?>
        <ul class="divide-y divide-line">
          <?php foreach ($documents as $d): ?>
            <li class="flex flex-wrap items-center gap-3 px-5 py-3">
              <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded bg-brand-50 text-brand-500"><?= icon('id-card', 'h-4 w-4') ?></span>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-ink"><?= e($docTypes[$d['doc_type']] ?? $d['doc_type']) ?></p>
                <p class="truncate text-xs text-ink-faint">
                  <?= $d['doc_number'] ? e(\App\Core\Sections::mask($d['doc_number'])) : 'No number' ?>
                  <?php if ($d['valid_upto']): ?> · valid to <?= e(fdate($d['valid_upto'], 'M Y')) ?><?php endif; ?>
                </p>
              </div>
              <?php if ($d['file_path']): ?>
                <a href="<?= e(upload_url($d['file_path'])) ?>" target="_blank" rel="noopener" class="btn-outline btn-sm"><?= icon('eye', 'h-3.5 w-3.5') ?>Open</a>
              <?php endif; ?>
              <form method="post" action="<?= url('/official/documents/' . $d['id'] . '/verify') ?>" class="flex items-center gap-2">
                <?= csrf_field() ?>
                <input type="hidden" name="verified" value="<?= $d['is_verified'] ? '0' : '1' ?>">
                <button type="submit" class="<?= $d['is_verified'] ? 'btn-ghost' : 'btn-primary' ?> btn-sm">
                  <?= $d['is_verified'] ? 'Remove verification' : 'Mark verified' ?>
                </button>
              </form>
              <?php if ($d['is_verified']): ?><span class="badge-green"><?= icon('check', 'h-3 w-3') ?>Verified</span><?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?><p class="p-5 text-sm text-ink-faint">No documents uploaded.</p><?php endif; ?>
    </div>

    <?php if ($qualifications): ?>
      <div class="card">
        <div class="card-head"><h2 class="card-title">Qualifications</h2></div>
        <ul class="divide-y divide-line">
          <?php foreach ($qualifications as $q): ?>
            <li class="px-5 py-3">
              <p class="text-sm font-medium text-ink"><?= e($q['course']) ?><?= $q['specialisation'] ? ' — ' . e($q['specialisation']) : '' ?></p>
              <p class="text-xs text-ink-faint">
                <?= e($quals[$q['level']] ?? $q['level']) ?><?= $q['institution'] ? ' · ' . e($q['institution']) : '' ?><?= $q['year_of_pass'] ? ' · ' . (int) $q['year_of_pass'] : '' ?>
              </p>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($experiences): ?>
      <div class="card">
        <div class="card-head"><h2 class="card-title">Experience</h2></div>
        <ul class="divide-y divide-line">
          <?php foreach ($experiences as $x): ?>
            <li class="px-5 py-3">
              <p class="text-sm font-medium text-ink"><?= e($x['designation']) ?></p>
              <p class="text-xs text-ink-faint">
                <?= e($x['organisation']) ?> · <?= e(fdate($x['from_date'], 'M Y')) ?> – <?= $x['is_current'] ? 'Present' : e(fdate($x['to_date'], 'M Y')) ?>
              </p>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($applications): ?>
      <div class="card">
        <div class="card-head"><h2 class="card-title">Applications</h2><span class="badge-gray"><?= count($applications) ?></span></div>
        <div class="scroll-x">
          <table class="table">
            <thead><tr><th>Job title</th><th>Employer</th><th>Applied</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach ($applications as $a): ?>
                <tr>
                  <td class="text-sm"><?= e($a['title']) ?></td>
                  <td class="text-sm text-ink-soft"><?= e($a['company_name']) ?></td>
                  <td class="whitespace-nowrap text-sm text-ink-soft"><?= e(fdate($a['applied_at'])) ?></td>
                  <td><span class="badge-gray"><?= e($statuses[$a['status']] ?? $a['status']) ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <aside class="space-y-4 lg:sticky lg:top-20 lg:self-start">
    <div class="card card-pad text-center">
      <span class="mx-auto flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-brand-100 text-lg font-bold text-brand-700">
        <?php if ($s['photo']): ?><img src="<?= e(upload_url($s['photo'])) ?>" alt="" class="h-full w-full object-cover">
        <?php else: ?><?= e(initials($s['name'])) ?><?php endif; ?>
      </span>
      <p class="mt-3 text-sm font-semibold text-ink"><?= e($s['name']) ?></p>
      <p class="text-xs text-ink-faint"><?= e($s['email']) ?></p>
      <div class="mt-3">
        <div class="flex items-center justify-between text-xs">
          <span class="font-medium text-ink-soft">Profile strength</span>
          <span class="font-bold text-brand-700"><?= (int) $s['profile_score'] ?>%</span>
        </div>
        <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-black/10">
          <div class="h-full rounded-full bg-brand-500" style="width: <?= (int) $s['profile_score'] ?>%"></div>
        </div>
      </div>
    </div>

    <?php if ($skills): ?>
      <div class="card card-pad">
        <h2 class="card-title">Skills</h2>
        <div class="mt-3 flex flex-wrap gap-1.5">
          <?php foreach ($skills as $sk): ?>
            <span class="chip"><?= e($sk['skill_name']) ?> <span class="text-ink-faint"><?= e(ucfirst($sk['proficiency'])) ?></span></span>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="card card-pad border-l-4 border-l-brand-500">
      <p class="flex items-center gap-2 text-sm font-semibold text-ink"><?= icon('lock', 'h-4 w-4 text-brand-500') ?>Handle with care</p>
      <p class="mt-1 text-sm text-ink-soft">
        This is a citizen's personal record. Use it only for the employment service you are providing, and never share it outside the department.
      </p>
    </div>
  </aside>
</div>
