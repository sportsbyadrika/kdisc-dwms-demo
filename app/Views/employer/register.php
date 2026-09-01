<?php
/** @var int $step @var array $reg @var int $cooldown @var string|null $demoCode */
$steps = [1 => ['E-mail address', 'mail'], 2 => ['Verify', 'shield-check'], 3 => ['Create login', 'building']];
ob_start();
?>
<div class="card">
  <div class="card-pad">
    <h1 class="text-2xl font-bold text-ink">Register your organisation</h1>
    <p class="mt-1 text-sm text-ink-soft">Create a login first — the full company profile comes next, and takes about ten minutes.</p>
    <div class="mt-7"><?php partial('stepper', ['steps' => $steps, 'step' => $step]); ?></div>
  </div>

  <div class="border-t border-line card-pad">
    <?php if ($step === 1): ?>
      <h2 class="card-title">Step 1 — your official e-mail address</h2>
      <p class="mt-1 text-sm text-ink-soft">Use an address on your organisation's domain where possible — it speeds up verification.</p>
      <form method="post" action="<?= url('/employer/register/email') ?>" class="mt-6 fieldset">
        <?= csrf_field() ?>
        <div>
          <label class="label" for="e-email">E-mail address <span class="text-danger">*</span></label>
          <input id="e-email" name="email" type="email" required autofocus autocomplete="email"
                 class="field <?= error_for('email') ? 'field-error' : '' ?>" value="<?= e(old('email', $reg['email'] ?? '')) ?>"
                 placeholder="hr@yourcompany.in">
          <?php if ($m = error_for('email')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>
        <button type="submit" class="btn-primary btn-lg btn-block"><?= icon('send', 'h-4 w-4') ?>Send one-time password</button>
      </form>

    <?php elseif ($step === 2): ?>
      <h2 class="card-title">Step 2 — verify your e-mail</h2>
      <p class="mt-1 text-sm text-ink-soft">Enter the 6-digit code sent to <strong class="text-ink"><?= e($reg['email']) ?></strong>.
        <a href="<?= url('/employer/register', ['step' => 1]) ?>" class="link">Change address</a></p>
      <?php partial('otp-demo', ['demoCode' => $demoCode]); ?>
      <form method="post" action="<?= url('/employer/register/verify') ?>" class="mt-6 fieldset">
        <?= csrf_field() ?>
        <div>
          <label class="label" for="e-code">One-time password <span class="text-danger">*</span></label>
          <input id="e-code" name="code" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" required autofocus autocomplete="one-time-code"
                 class="field text-center font-mono text-2xl tracking-[0.5em]" placeholder="000000">
        </div>
        <button type="submit" class="btn-primary btn-lg btn-block"><?= icon('shield-check', 'h-4 w-4') ?>Verify and continue</button>
      </form>
      <form method="post" action="<?= url('/employer/register/email') ?>" class="mt-3">
        <?= csrf_field() ?>
        <input type="hidden" name="email" value="<?= e($reg['email']) ?>">
        <button type="submit" class="btn-ghost btn-block" <?= $cooldown > 0 ? 'disabled' : '' ?>>
          <?= icon('refresh', 'h-4 w-4') ?><?= $cooldown > 0 ? 'Resend available in ' . (int) $cooldown . 's' : 'Resend one-time password' ?>
        </button>
      </form>

    <?php else: ?>
      <h2 class="card-title">Step 3 — create your login</h2>
      <p class="mt-1 text-sm"><span class="badge-green"><?= icon('check', 'h-3 w-3') ?><?= e($reg['email']) ?> verified</span></p>

      <form method="post" action="<?= url('/employer/register/complete') ?>" class="mt-6 fieldset">
        <?= csrf_field() ?>
        <div>
          <label class="label" for="e-company">Organisation name <span class="text-danger">*</span></label>
          <input id="e-company" name="company_name" required class="field <?= error_for('company_name') ? 'field-error' : '' ?>"
                 value="<?= e(old('company_name')) ?>" placeholder="As registered, including Pvt Ltd / LLP">
          <?php if ($m = error_for('company_name')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>
        <div class="form-grid">
          <div>
            <label class="label" for="e-person">Contact person <span class="text-danger">*</span></label>
            <input id="e-person" name="contact_person" required class="field <?= error_for('contact_person') ? 'field-error' : '' ?>"
                   value="<?= e(old('contact_person')) ?>">
            <?php if ($m = error_for('contact_person')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
          </div>
          <div>
            <label class="label" for="e-mobile">Mobile number <span class="text-danger">*</span></label>
            <div class="flex">
              <span class="inline-flex items-center rounded-l border border-r-0 border-ink/30 bg-black/[0.03] px-3 text-sm text-ink-soft">+91</span>
              <input id="e-mobile" name="contact_mobile" inputmode="numeric" maxlength="10" required
                     class="field rounded-l-none <?= error_for('contact_mobile') ? 'field-error' : '' ?>" value="<?= e(old('contact_mobile')) ?>">
            </div>
            <?php if ($m = error_for('contact_mobile')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
          </div>
        </div>
        <div class="form-grid" x-data="{ show: false }">
          <div>
            <label class="label" for="e-password">Password <span class="text-danger">*</span></label>
            <div class="relative">
              <input id="e-password" name="password" :type="show ? 'text' : 'password'" required minlength="8" autocomplete="new-password"
                     class="field pr-10 <?= error_for('password') ? 'field-error' : '' ?>">
              <button type="button" @click="show = !show" class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-ink-faint hover:text-ink"
                      :aria-label="show ? 'Hide password' : 'Show password'"><?= icon('eye', 'h-4 w-4') ?></button>
            </div>
            <?php if ($m = error_for('password')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p>
            <?php else: ?><p class="hint">At least 8 characters.</p><?php endif; ?>
          </div>
          <div>
            <label class="label" for="e-password2">Confirm password <span class="text-danger">*</span></label>
            <input id="e-password2" name="password_confirmation" :type="show ? 'text' : 'password'" required autocomplete="new-password"
                   class="field <?= error_for('password_confirmation') ? 'field-error' : '' ?>">
            <?php if ($m = error_for('password_confirmation')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
          </div>
        </div>
        <label class="flex items-start gap-2.5 text-sm text-ink-soft">
          <input type="checkbox" name="terms" value="1" class="checkbox" required>
          <span>I confirm I am authorised to register this organisation and accept the
            <a href="<?= url('/terms') ?>" class="link" target="_blank">terms of use</a> and
            <a href="<?= url('/privacy') ?>" class="link" target="_blank">privacy policy</a>.</span>
        </label>
        <?php if ($m = error_for('terms')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        <button type="submit" class="btn-primary btn-lg btn-block"><?= icon('check', 'h-4 w-4') ?>Create employer account</button>
      </form>
    <?php endif; ?>
  </div>

  <div class="border-t border-line px-5 py-4 text-center text-sm text-ink-soft sm:px-6">
    Already registered? <a href="<?= url('/employer/login') ?>" class="link">Employer sign in</a>
    &nbsp;·&nbsp; Looking for work? <a href="<?= url('/register') ?>" class="link">Register as a job seeker</a>
  </div>
</div>
<?php
$slot = ob_get_clean();
partial('auth-shell', [
    'slot'       => $slot,
    'asideTitle' => 'Reach verified candidates',
    'asideSub'   => 'Publish structured job titles and screen candidates whose identity and qualifications are already verified.',
    'points'     => [
        ['shield-check', 'Verified once', 'Candidates complete e-KYC before they apply.'],
        ['clipboard', 'Comparable applications', 'Structured qualification and experience data.'],
        ['wallet', 'No listing fee', 'Publishing job titles on DWMS costs nothing.'],
    ],
]);
