<?php
/** @var array $seeker @var array $methods @var array|null $department @var array $departments
 *  @var string|null $demoCode @var array|null $pendingRef @var int $cooldown */
$status = $seeker['kyc_status'];
?>
<?php partial('dash-header', [
  'title' => 'e-KYC verification',
  'sub'   => 'Verify your identity once. Verified profiles are shortlisted first and are required by some departments.',
]); ?>

<!-- current status -->
<div class="card card-pad">
  <div class="flex flex-wrap items-center gap-4">
    <span class="flex h-12 w-12 items-center justify-center rounded-full <?= $status === 'verified' ? 'bg-success/10 text-success' : 'bg-brand-50 text-brand-500' ?>">
      <?= icon($status === 'verified' ? 'shield-check' : 'fingerprint', 'h-6 w-6') ?>
    </span>
    <div class="min-w-0 flex-1">
      <p class="text-sm font-semibold text-ink">
        <?php if ($status === 'verified'): ?>Your identity is verified
        <?php elseif ($status === 'pending'): ?>Verification in progress
        <?php elseif ($status === 'failed'): ?>Verification failed
        <?php else: ?>e-KYC not started<?php endif; ?>
      </p>
      <p class="text-sm text-ink-soft">
        <?php if ($status === 'verified'): ?>
          Verified with <?= e($methods[$seeker['kyc_method']] ?? 'Aadhaar') ?> · reference <?= e($seeker['kyc_ref']) ?> · <?= e(fdate($seeker['kyc_verified_at'], 'd M Y, g:i a')) ?>
        <?php else: ?>
          Complete e-KYC to add a verified badge to your profile.
        <?php endif; ?>
      </p>
    </div>
    <?php if ($status === 'verified'): ?><span class="badge-green"><?= icon('check', 'h-3 w-3') ?>Verified</span>
    <?php elseif ($status === 'pending'): ?><span class="badge-amber"><?= icon('clock', 'h-3 w-3') ?>Awaiting OTP</span>
    <?php else: ?><span class="badge-gray">Not verified</span><?php endif; ?>
  </div>
</div>

<?php if ($status !== 'verified'): ?>
<div class="mt-4 grid gap-4 lg:grid-cols-[1.4fr,1fr]">
  <div class="card">
    <div class="card-head"><h2 class="card-title">Aadhaar e-KYC</h2><span class="badge-blue">Recommended</span></div>

    <?php if (!$pendingRef): ?>
      <!-- step 1: number + consent -->
      <form method="post" action="<?= url('/dashboard/kyc/send-otp') ?>" class="card-pad fieldset">
        <?= csrf_field() ?>

        <div>
          <label class="label" for="k-aadhaar">Aadhaar number <span class="text-danger">*</span></label>
          <input id="k-aadhaar" name="aadhaar" inputmode="numeric" maxlength="14" required
                 class="field font-mono text-lg tracking-widest <?= error_for('aadhaar') ? 'field-error' : '' ?>"
                 placeholder="0000 0000 0000"
                 x-data x-on:input="$el.value = $el.value.replace(/\D/g,'').replace(/(\d{4})(?=\d)/g,'$1 ').slice(0,14)">
          <?php if ($m = error_for('aadhaar')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p>
          <?php else: ?><p class="hint">Your Aadhaar number is used only for this verification and is never stored — we keep a masked reference such as XXXX XXXX 1234.</p><?php endif; ?>
        </div>

        <div>
          <label class="label" for="k-dept">Share verified details with <span class="text-danger">*</span></label>
          <select id="k-dept" name="department_id" required class="field <?= error_for('department_id') ? 'field-error' : '' ?>">
            <option value="">Select the department</option>
            <?php foreach ($departments as $d): ?>
              <option value="<?= (int) $d['id'] ?>" <?= (int) ($seeker['kyc_department_id'] ?? 0) === (int) $d['id'] ? 'selected' : '' ?>>
                <?= e($d['name']) ?> (<?= e(ucfirst($d['type'])) ?>)
              </option>
            <?php endforeach; ?>
          </select>
          <?php if ($m = error_for('department_id')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>

        <div class="rounded-card border border-line bg-canvas p-4">
          <p class="text-xs font-bold uppercase tracking-wider text-ink-faint">Consent</p>
          <label class="mt-2 flex items-start gap-2.5 text-sm text-ink-soft">
            <input type="checkbox" name="consent" value="1" class="checkbox" required>
            <span>I voluntarily give my consent to use my Aadhaar number for e-KYC authentication with UIDAI, and I authorise DWMS 2.0 to share the demographic details returned by UIDAI — my name, date of birth, gender, address and photograph — with the government department selected above, for the purpose of verifying my registration on this platform. I understand that my Aadhaar number itself is not stored, that this consent is recorded with a timestamp, and that I may withdraw it at any time by writing to the department.</span>
          </label>
          <?php if ($m = error_for('consent')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>

        <button type="submit" class="btn-primary btn-lg"><?= icon('send', 'h-4 w-4') ?>Send OTP</button>
      </form>

    <?php else: ?>
      <!-- step 2: OTP -->
      <div class="card-pad">
        <p class="flex items-center gap-2 text-sm text-ink-soft">
          <span class="text-success"><?= icon('check-circle', 'h-4 w-4') ?></span>
          Consent recorded. An OTP has been sent to the mobile number registered with Aadhaar
          <strong class="text-ink"><?= e($pendingRef['masked']) ?></strong>.
        </p>
        <?php partial('otp-demo', ['demoCode' => $demoCode]); ?>

        <form method="post" action="<?= url('/dashboard/kyc/verify') ?>" class="mt-5 fieldset">
          <?= csrf_field() ?>
          <div>
            <label class="label" for="k-otp">One-time password <span class="text-danger">*</span></label>
            <input id="k-otp" name="code" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" required autofocus
                   autocomplete="one-time-code"
                   class="field text-center font-mono text-2xl tracking-[0.5em] <?= error_for('code') ? 'field-error' : '' ?>" placeholder="000000">
            <?php if ($m = error_for('code')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p>
            <?php else: ?><p class="hint">Valid for 10 minutes.</p><?php endif; ?>
          </div>
          <button type="submit" class="btn-primary btn-lg btn-block"><?= icon('shield-check', 'h-4 w-4') ?>Submit</button>
        </form>

        <form method="post" action="<?= url('/dashboard/kyc/cancel') ?>" class="mt-3">
          <?= csrf_field() ?>
          <button type="submit" class="btn-ghost btn-block btn-sm">Cancel and start again</button>
        </form>
      </div>
    <?php endif; ?>
  </div>

  <div class="space-y-4">
    <div class="card card-pad">
      <h2 class="card-title">How it works</h2>
      <ol class="mt-4 space-y-4">
        <?php foreach ([
          ['Enter your Aadhaar number', 'We validate the check digit locally before anything is sent.'],
          ['Record your consent', 'Your consent to share the details with the selected department is stored with a timestamp.'],
          ['Receive an OTP', 'UIDAI sends a one-time password to the mobile number linked to your Aadhaar.'],
          ['Submit the OTP', 'On success your profile is marked verified — no document upload needed.'],
        ] as $i => [$t, $d]): ?>
          <li class="flex gap-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-500 text-xs font-bold text-white"><?= $i + 1 ?></span>
            <span>
              <span class="block text-sm font-semibold text-ink"><?= e($t) ?></span>
              <span class="block text-xs text-ink-soft"><?= e($d) ?></span>
            </span>
          </li>
        <?php endforeach; ?>
      </ol>
    </div>

    <div class="card card-pad">
      <h2 class="card-title">Other verification routes</h2>
      <p class="mt-1 text-sm text-ink-soft">If you do not wish to use Aadhaar, upload one of these under
        <a href="<?= url('/dashboard/documents') ?>" class="link">Documents &amp; proofs</a> and a verification officer will check it manually.</p>
      <ul class="mt-3 space-y-2">
        <?php foreach (['pan' => 'PAN card', 'driving_license' => 'Driving licence', 'passport' => 'Passport'] as $label): ?>
          <li class="flex items-center gap-2 text-sm text-ink-soft"><span class="text-brand-500"><?= icon('id-card', 'h-4 w-4') ?></span><?= e($label) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="card card-pad border-l-4 border-l-warning">
      <p class="flex items-center gap-2 text-sm font-semibold text-ink"><?= icon('info', 'h-4 w-4 text-warning') ?>Integration status</p>
      <p class="mt-1 text-sm text-ink-soft">
        The UIDAI gateway is not connected in this MVP. Consent capture, masking, the audit trail and the status
        transitions are final — only the two OTP calls are stubbed, and the OTP is issued locally.
      </p>
      <?php if (\App\Core\Otp::demoMode()): ?>
        <p class="mt-2 text-xs text-ink-faint">For testing, <code class="rounded bg-black/5 px-1">2345 6789 0124</code> is a checksum-valid number.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php else: ?>
  <div class="mt-4 card card-pad">
    <h2 class="card-title">What verification unlocks</h2>
    <ul class="mt-3 grid gap-3 sm:grid-cols-2">
      <?php foreach ([
        ['briefcase', 'Apply to vacancies that require a verified identity'],
        ['target', 'Appear higher in employer shortlists'],
        ['graduation', 'Enrol in government-funded skilling programmes'],
        ['compass', 'Book career services that need identity confirmation'],
      ] as [$ic, $t]): ?>
        <li class="flex items-center gap-2.5 text-sm text-ink-soft">
          <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-success/10 text-success"><?= icon($ic, 'h-4 w-4') ?></span><?= e($t) ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>
