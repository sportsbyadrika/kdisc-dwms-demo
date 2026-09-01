<?php
/** @var array $employer @var int $step @var array $steps @var array $ownership @var array $ranges @var array $districts */
$v = static fn(string $f, $d = '') => old($f, $employer[$f] ?? $d);
$err = static function (string $f) { $m = error_for($f); return $m ? '<p class="err">' . icon('alert', 'h-3.5 w-3.5') . e($m) . '</p>' : ''; };
$cls = static fn(string $f) => 'field' . (error_for($f) ? ' field-error' : '');
?>
<?php partial('dash-header', [
  'title' => 'Company profile',
  'sub'   => 'The verification desk reviews this before your job titles go live.',
  'actions' => $employer['profile_completed']
      ? '<span class="badge-green">' . icon('check', 'h-3 w-3') . 'Submitted</span>'
      : '<span class="badge-amber">' . icon('clock', 'h-3 w-3') . 'Draft</span>',
]); ?>

<div class="card">
  <div class="card-pad"><?php partial('stepper', ['steps' => $steps, 'step' => $step, 'linkBase' => '/employer/profile']); ?></div>

  <form method="post" action="<?= url('/employer/profile') ?>" enctype="multipart/form-data" class="border-t border-line">
    <?= csrf_field() ?>
    <input type="hidden" name="step" value="<?= (int) $step ?>">

    <?php if ($step === 1): ?>
      <div class="card-pad">
        <h2 class="card-title">Organisation details</h2>
        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div class="sm:col-span-2 flex items-center gap-5" x-data="filePicker('<?= e(upload_url($employer['logo']) ?? '') ?>', 'image')">
            <span class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-card border border-line bg-brand-50 text-brand-300">
              <template x-if="preview"><img :src="preview" alt="" class="h-full w-full object-cover"></template>
              <template x-if="!preview"><span><?= icon('building', 'h-8 w-8') ?></span></template>
            </span>
            <div>
              <label class="label" for="p-logo">Organisation logo</label>
              <input id="p-logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml" @change="pick($event)"
                     class="block w-full text-sm text-ink-soft file:mr-3 file:rounded-full file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
              <?= $err('logo') ?>
              <p class="hint">Shown on every job title you publish.</p>
            </div>
          </div>

          <div class="sm:col-span-2">
            <label class="label" for="p-name">Registered name <span class="text-danger">*</span></label>
            <input id="p-name" name="company_name" required class="<?= $cls('company_name') ?>" value="<?= e($v('company_name')) ?>">
            <?= $err('company_name') ?>
          </div>
          <div>
            <label class="label" for="p-industry">Industry <span class="text-danger">*</span></label>
            <input id="p-industry" name="industry" required class="<?= $cls('industry') ?>" value="<?= e($v('industry')) ?>"
                   placeholder="Information Technology, Healthcare…">
            <?= $err('industry') ?>
          </div>
          <div>
            <label class="label" for="p-ownership">Type of ownership <span class="text-danger">*</span></label>
            <select id="p-ownership" name="ownership_type" required class="<?= $cls('ownership_type') ?>">
              <option value="">Select…</option>
              <?php foreach ($ownership as $k => $label): ?>
                <option value="<?= e($k) ?>" <?= $v('ownership_type') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
            <?= $err('ownership_type') ?>
          </div>
          <div>
            <label class="label" for="p-size">Number of employees <span class="text-danger">*</span></label>
            <select id="p-size" name="employee_range" required class="<?= $cls('employee_range') ?>">
              <option value="">Select…</option>
              <?php foreach ($ranges as $r): ?>
                <option value="<?= e($r) ?>" <?= $v('employee_range') === $r ? 'selected' : '' ?>><?= e($r) ?></option>
              <?php endforeach; ?>
            </select>
            <?= $err('employee_range') ?>
          </div>
          <div>
            <label class="label" for="p-year">Year established</label>
            <input id="p-year" name="established_year" type="number" min="1800" max="<?= date('Y') ?>"
                   class="<?= $cls('established_year') ?>" value="<?= e($v('established_year')) ?>">
            <?= $err('established_year') ?>
          </div>
          <div class="sm:col-span-2">
            <label class="label" for="p-website">Website</label>
            <input id="p-website" name="website" class="<?= $cls('website') ?>" value="<?= e($v('website')) ?>" placeholder="https://">
            <?= $err('website') ?>
          </div>
          <div class="sm:col-span-2">
            <label class="label" for="p-about">About the organisation</label>
            <textarea id="p-about" name="about" rows="4" class="<?= $cls('about') ?>"
                      placeholder="What your organisation does, in two or three sentences."><?= e($v('about')) ?></textarea>
            <?= $err('about') ?>
          </div>
        </div>
      </div>

    <?php elseif ($step === 2): ?>
      <div class="card-pad">
        <h2 class="card-title">Statutory details</h2>
        <p class="mt-1 text-sm text-ink-soft">These are checked against public registers during verification.</p>
        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="label" for="p-pan">PAN <span class="text-danger">*</span></label>
            <input id="p-pan" name="pan" required maxlength="10" class="<?= $cls('pan') ?> uppercase" value="<?= e($v('pan')) ?>" placeholder="AABCT1234F">
            <?= $err('pan') ?>
          </div>
          <div>
            <label class="label" for="p-gst">GSTIN</label>
            <input id="p-gst" name="gstin" maxlength="15" class="<?= $cls('gstin') ?> uppercase" value="<?= e($v('gstin')) ?>" placeholder="32AABCT1234F1ZP">
            <?= $err('gstin') ?>
          </div>
          <div>
            <label class="label" for="p-cin">CIN</label>
            <input id="p-cin" name="cin" maxlength="21" class="<?= $cls('cin') ?> uppercase" value="<?= e($v('cin')) ?>">
            <?= $err('cin') ?>
            <p class="hint">For companies registered with the MCA.</p>
          </div>
          <div>
            <label class="label" for="p-reg">Registration number</label>
            <input id="p-reg" name="registration_no" class="<?= $cls('registration_no') ?>" value="<?= e($v('registration_no')) ?>">
            <?= $err('registration_no') ?>
            <p class="hint">Society, trust, firm or shops-and-establishments number.</p>
          </div>
          <div class="sm:col-span-2">
            <label class="label" for="p-licence">Labour licence number</label>
            <input id="p-licence" name="labour_licence_no" class="<?= $cls('labour_licence_no') ?>" value="<?= e($v('labour_licence_no')) ?>">
            <?= $err('labour_licence_no') ?>
          </div>
        </div>
        <p class="mt-4 flex items-start gap-2 rounded-card bg-canvas px-4 py-3 text-xs text-ink-soft">
          <span class="mt-0.5 shrink-0 text-brand-500"><?= icon('info', 'h-4 w-4') ?></span>
          Upload scanned copies under <a href="<?= url('/employer/documents') ?>" class="link">Documents</a> — verification is usually faster when they are attached.
        </p>
      </div>

    <?php elseif ($step === 3): ?>
      <div class="card-pad">
        <h2 class="card-title">Registered address and contact</h2>
        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div class="sm:col-span-2">
            <label class="label" for="p-a1">Address line 1 <span class="text-danger">*</span></label>
            <input id="p-a1" name="address_line1" required class="<?= $cls('address_line1') ?>" value="<?= e($v('address_line1')) ?>">
            <?= $err('address_line1') ?>
          </div>
          <div class="sm:col-span-2">
            <label class="label" for="p-a2">Address line 2</label>
            <input id="p-a2" name="address_line2" class="<?= $cls('address_line2') ?>" value="<?= e($v('address_line2')) ?>">
          </div>
          <div>
            <label class="label" for="p-city">City / town</label>
            <input id="p-city" name="city" class="<?= $cls('city') ?>" value="<?= e($v('city')) ?>">
          </div>
          <div>
            <label class="label" for="p-district">District <span class="text-danger">*</span></label>
            <select id="p-district" name="district" required class="<?= $cls('district') ?>">
              <option value="">Select district</option>
              <?php foreach ($districts as $d): ?>
                <option value="<?= e($d) ?>" <?= $v('district') === $d ? 'selected' : '' ?>><?= e($d) ?></option>
              <?php endforeach; ?>
              <option value="Other" <?= $v('district') === 'Other' ? 'selected' : '' ?>>Outside Kerala</option>
            </select>
            <?= $err('district') ?>
          </div>
          <div>
            <label class="label" for="p-state">State <span class="text-danger">*</span></label>
            <input id="p-state" name="state" required class="<?= $cls('state') ?>" value="<?= e($v('state', 'Kerala')) ?>">
            <?= $err('state') ?>
          </div>
          <div>
            <label class="label" for="p-pin">PIN code <span class="text-danger">*</span></label>
            <input id="p-pin" name="pincode" inputmode="numeric" maxlength="6" required class="<?= $cls('pincode') ?>" value="<?= e($v('pincode')) ?>">
            <?= $err('pincode') ?>
          </div>

          <div class="sm:col-span-2 mt-2 border-t border-line pt-4">
            <h3 class="text-sm font-semibold text-ink">Contact person</h3>
          </div>
          <div>
            <label class="label" for="p-cp">Name <span class="text-danger">*</span></label>
            <input id="p-cp" name="contact_person" required class="<?= $cls('contact_person') ?>" value="<?= e($v('contact_person')) ?>">
            <?= $err('contact_person') ?>
          </div>
          <div>
            <label class="label" for="p-cd">Designation</label>
            <input id="p-cd" name="contact_designation" class="<?= $cls('contact_designation') ?>" value="<?= e($v('contact_designation')) ?>">
          </div>
          <div>
            <label class="label" for="p-cm">Mobile number <span class="text-danger">*</span></label>
            <div class="flex">
              <span class="inline-flex items-center rounded-l border border-r-0 border-ink/30 bg-black/[0.03] px-3 text-sm text-ink-soft">+91</span>
              <input id="p-cm" name="contact_mobile" inputmode="numeric" maxlength="10" required
                     class="<?= $cls('contact_mobile') ?> rounded-l-none" value="<?= e($v('contact_mobile')) ?>">
            </div>
            <?= $err('contact_mobile') ?>
          </div>
          <div>
            <label class="label" for="p-ce">E-mail <span class="text-danger">*</span></label>
            <input id="p-ce" name="contact_email" type="email" required class="<?= $cls('contact_email') ?>" value="<?= e($v('contact_email', $employer['email'])) ?>">
            <?= $err('contact_email') ?>
          </div>
        </div>
      </div>

    <?php else: ?>
      <div class="card-pad">
        <h2 class="card-title">Review and submit</h2>
        <p class="mt-1 text-sm text-ink-soft">Check the details below, then submit for verification.</p>

        <?php
        $review = [
          'Organisation' => [
            'Registered name' => $employer['company_name'],
            'Industry' => $employer['industry'],
            'Ownership' => $ownership[$employer['ownership_type']] ?? null,
            'Employees' => $employer['employee_range'],
            'Established' => $employer['established_year'],
            'Website' => $employer['website'],
          ],
          'Statutory' => [
            'PAN' => $employer['pan'],
            'GSTIN' => $employer['gstin'],
            'CIN' => $employer['cin'],
            'Registration number' => $employer['registration_no'],
            'Labour licence' => $employer['labour_licence_no'],
          ],
          'Address & contact' => [
            'Address' => trim(implode(', ', array_filter([$employer['address_line1'], $employer['address_line2'], $employer['city'], $employer['district'], $employer['state'], $employer['pincode']]))),
            'Contact person' => trim(($employer['contact_person'] ?: '') . ($employer['contact_designation'] ? ', ' . $employer['contact_designation'] : '')),
            'Mobile' => $employer['contact_mobile'] ? '+91 ' . $employer['contact_mobile'] : null,
            'E-mail' => $employer['contact_email'],
          ],
        ];
        ?>
        <div class="mt-5 space-y-5">
          <?php foreach ($review as $group => $rows): $n = array_search($group, ['Organisation', 'Statutory', 'Address & contact'], true) + 1; ?>
            <div class="rounded-card border border-line">
              <div class="flex items-center justify-between border-b border-line px-4 py-2.5">
                <h3 class="text-sm font-semibold text-ink"><?= e($group) ?></h3>
                <a href="<?= url('/employer/profile', ['step' => $n]) ?>" class="text-xs font-semibold text-brand-500 hover:text-brand-700">Edit</a>
              </div>
              <dl class="divide-y divide-line">
                <?php foreach ($rows as $label => $value): ?>
                  <div class="flex flex-col gap-0.5 px-4 py-2.5 sm:flex-row sm:gap-4">
                    <dt class="w-48 shrink-0 text-xs font-medium uppercase tracking-wide text-ink-faint"><?= e($label) ?></dt>
                    <dd class="text-sm <?= $value ? 'text-ink' : 'text-danger' ?>"><?= $value ? e($value) : 'Not provided' ?></dd>
                  </div>
                <?php endforeach; ?>
              </dl>
            </div>
          <?php endforeach; ?>
        </div>

        <label class="mt-5 flex items-start gap-2.5 text-sm text-ink-soft">
          <input type="checkbox" name="declaration" value="1" class="checkbox" required>
          <span>I declare that the information above is true and that I am authorised to submit it on behalf of this organisation.</span>
        </label>
      </div>
    <?php endif; ?>

    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line px-5 py-4 sm:px-6">
      <?php if ($step > 1): ?>
        <a href="<?= url('/employer/profile', ['step' => $step - 1]) ?>" class="btn-ghost"><?= icon('arrow-left', 'h-4 w-4') ?>Back</a>
      <?php else: ?><span></span><?php endif; ?>
      <button type="submit" class="btn-primary">
        <?= $step === 4 ? icon('check', 'h-4 w-4') . 'Submit for verification' : 'Save and continue' . icon('arrow-right', 'h-4 w-4') ?>
      </button>
    </div>
  </form>
</div>
