<?php /** @var string $stage @var string $email @var string|null $demoCode */
ob_start(); ?>
<div class="card">
  <div class="card-pad">
    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-50 text-brand-500"><?= icon('key') ?></span>
    <h1 class="mt-4 text-2xl font-bold text-ink">Reset your password</h1>

    <?php if ($stage === 'request'): ?>
      <p class="mt-1 text-sm text-ink-soft">Enter your registered e-mail address and we will send a one-time password.</p>
      <form method="post" action="<?= url('/forgot-password') ?>" class="mt-6 fieldset">
        <?= csrf_field() ?>
        <div>
          <label class="label" for="f-email">E-mail address</label>
          <input id="f-email" name="email" type="email" required autofocus class="field <?= error_for('email') ? 'field-error' : '' ?>" value="<?= e(old('email')) ?>">
          <?php if ($m = error_for('email')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>
        <button type="submit" class="btn-primary btn-lg btn-block"><?= icon('send', 'h-4 w-4') ?>Send one-time password</button>
      </form>
    <?php else: ?>
      <p class="mt-1 text-sm text-ink-soft">Enter the code sent to <strong class="text-ink"><?= e($email) ?></strong> and choose a new password.</p>
      <?php partial('otp-demo', ['demoCode' => $demoCode]); ?>
      <form method="post" action="<?= url('/forgot-password/reset') ?>" class="mt-6 fieldset">
        <?= csrf_field() ?>
        <div>
          <label class="label" for="f-code">One-time password</label>
          <input id="f-code" name="code" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" required autofocus
                 class="field text-center font-mono text-2xl tracking-[0.5em] <?= error_for('code') ? 'field-error' : '' ?>" placeholder="000000">
          <?php if ($m = error_for('code')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="label" for="f-password">New password</label>
          <input id="f-password" name="password" type="password" minlength="8" required autocomplete="new-password"
                 class="field <?= error_for('password') ? 'field-error' : '' ?>">
          <?php if ($m = error_for('password')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php else: ?><p class="hint">At least 8 characters.</p><?php endif; ?>
        </div>
        <div>
          <label class="label" for="f-password2">Confirm new password</label>
          <input id="f-password2" name="password_confirmation" type="password" required autocomplete="new-password"
                 class="field <?= error_for('password_confirmation') ? 'field-error' : '' ?>">
          <?php if ($m = error_for('password_confirmation')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>
        <button type="submit" class="btn-primary btn-lg btn-block">Reset password</button>
        <a href="<?= url('/forgot-password') ?>" class="btn-ghost btn-block">Use a different e-mail address</a>
      </form>
    <?php endif; ?>
  </div>
  <div class="border-t border-line px-5 py-4 text-center text-sm text-ink-soft sm:px-6">
    Remembered it? <a href="<?= url('/login') ?>" class="link">Back to sign in</a>
  </div>
</div>
<?php
$slot = ob_get_clean();
partial('auth-shell', [
    'slot'       => $slot,
    'asideTitle' => 'Account recovery',
    'asideSub'   => 'For your security the one-time password expires after 10 minutes and can be used only once.',
    'points'     => [
        ['shield', 'Codes expire quickly', 'Every one-time password is valid for 10 minutes.'],
        ['lock',   'Passwords are hashed', 'Nobody at DWMS can read your password — not even support.'],
        ['info',   'Still locked out?',    'Write to us from the contact page and we will verify you manually.'],
    ],
]);
