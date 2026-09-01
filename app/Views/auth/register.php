<?php
/** @var int $step @var array $reg @var int $cooldown @var string|null $demoCode */
$steps = [
    1 => ['E-mail address', 'mail'],
    2 => ['Verify', 'shield-check'],
    3 => ['Basic profile', 'user'],
];
ob_start();
?>
<div class="card">
  <div class="card-pad">
    <h1 class="text-2xl font-bold text-ink">Create your job seeker account</h1>
    <p class="mt-1 text-sm text-ink-soft">Three short steps. You can complete e-KYC and the rest of your profile afterwards.</p>

    <!-- stepper -->
    <ol class="mt-7 flex items-center gap-2" aria-label="Registration progress">
      <?php foreach ($steps as $n => [$label, $ic]): ?>
        <li class="step">
          <span class="<?= $n < $step ? 'step-done' : ($n === $step ? 'step-now' : 'step-todo') ?>">
            <?= $n < $step ? icon('check', 'h-4 w-4') : $n ?>
          </span>
          <span class="hidden text-xs font-semibold sm:block <?= $n === $step ? 'text-ink' : 'text-ink-faint' ?>"><?= e($label) ?></span>
          <?php if ($n < count($steps)): ?>
            <span class="h-0.5 flex-1 rounded-full <?= $n < $step ? 'bg-success' : 'bg-line' ?>"></span>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>

  <div class="border-t border-line card-pad">
    <?php if ($step === 1): ?>
      <h2 class="card-title">Step 1 — your e-mail address</h2>
      <p class="mt-1 text-sm text-ink-soft">We will send a 6-digit one-time password to confirm the address belongs to you.</p>
      <form method="post" action="<?= url('/register/email') ?>" class="mt-6 fieldset">
        <?= csrf_field() ?>
        <div>
          <label class="label" for="r-email">E-mail address <span class="text-danger">*</span></label>
          <input id="r-email" name="email" type="email" required autocomplete="email" autofocus
                 class="field <?= error_for('email') ? 'field-error' : '' ?>"
                 value="<?= e(old('email', $reg['email'] ?? '')) ?>" placeholder="you@example.com">
          <?php if ($m = error_for('email')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
          <p class="hint">Use an address you check regularly — application updates are sent here.</p>
        </div>
        <button type="submit" class="btn-primary btn-lg btn-block"><?= icon('send', 'h-4 w-4') ?>Send one-time password</button>
      </form>

    <?php elseif ($step === 2): ?>
      <h2 class="card-title">Step 2 — verify your e-mail</h2>
      <p class="mt-1 text-sm text-ink-soft">
        Enter the 6-digit code sent to <strong class="text-ink"><?= e($reg['email']) ?></strong>.
        <a href="<?= url('/register', ['step' => 1]) ?>" class="link">Change address</a>
      </p>
      <?php partial('otp-demo', ['demoCode' => $demoCode]); ?>

      <form method="post" action="<?= url('/register/verify') ?>" class="mt-6 fieldset">
        <?= csrf_field() ?>
        <div>
          <label class="label" for="r-code">One-time password <span class="text-danger">*</span></label>
          <input id="r-code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus
                 autocomplete="one-time-code"
                 class="field text-center font-mono text-2xl tracking-[0.5em]" placeholder="000000">
          <p class="hint">The code is valid for 10 minutes.</p>
        </div>
        <button type="submit" class="btn-primary btn-lg btn-block"><?= icon('shield-check', 'h-4 w-4') ?>Verify and continue</button>
      </form>

      <form method="post" action="<?= url('/register/email') ?>" class="mt-3">
        <?= csrf_field() ?>
        <input type="hidden" name="email" value="<?= e($reg['email']) ?>">
        <button type="submit" class="btn-ghost btn-block" <?= $cooldown > 0 ? 'disabled' : '' ?>>
          <?= icon('refresh', 'h-4 w-4') ?>
          <?= $cooldown > 0 ? 'Resend available in ' . (int) $cooldown . 's' : 'Resend one-time password' ?>
        </button>
      </form>

    <?php else: ?>
      <h2 class="card-title">Step 3 — your basic profile</h2>
      <p class="mt-1 text-sm text-ink-soft">
        <span class="badge-green"><?= icon('check', 'h-3.5 w-3.5') ?><?= e($reg['email']) ?> verified</span>
      </p>

      <form method="post" action="<?= url('/register/complete') ?>" enctype="multipart/form-data" class="mt-6 fieldset">
        <?= csrf_field() ?>

        <div x-data="filePicker('', 'image')" class="flex items-center gap-5">
          <span class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-line bg-brand-50 text-brand-300">
            <template x-if="preview"><img :src="preview" alt="" class="h-full w-full object-cover"></template>
            <template x-if="!preview"><span><?= icon('user', 'h-8 w-8') ?></span></template>
          </span>
          <div>
            <label class="label" for="r-photo">Photograph</label>
            <input id="r-photo" name="photo" type="file" accept="image/png,image/jpeg,image/webp" @change="pick($event)" x-ref="photo"
                   class="block w-full text-sm text-ink-soft file:mr-3 file:rounded-full file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
            <p class="hint">JPG, PNG or WebP, up to <?= (int) config('security.max_upload_mb', 5) ?> MB. You can add it later.</p>
            <?php if ($m = error_for('photo')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
            <button type="button" x-show="preview" x-cloak @click="clear($refs.photo)" class="mt-1 text-xs font-semibold text-danger">Remove</button>
          </div>
        </div>

        <div class="form-grid">
          <div>
            <label class="label" for="r-name">Full name <span class="text-danger">*</span></label>
            <input id="r-name" name="name" required autocomplete="name" class="field <?= error_for('name') ? 'field-error' : '' ?>"
                   value="<?= e(old('name')) ?>" placeholder="As printed on your certificates">
            <?php if ($m = error_for('name')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
          </div>
          <div>
            <label class="label" for="r-mobile">Mobile number <span class="text-danger">*</span></label>
            <div class="flex">
              <span class="inline-flex items-center rounded-l border border-r-0 border-ink/30 bg-black/[0.03] px-3 text-sm text-ink-soft">+91</span>
              <input id="r-mobile" name="mobile" inputmode="numeric" maxlength="10" required autocomplete="tel-national"
                     class="field rounded-l-none <?= error_for('mobile') ? 'field-error' : '' ?>" value="<?= e(old('mobile')) ?>" placeholder="9876543210">
            </div>
            <?php if ($m = error_for('mobile')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
          </div>
        </div>

        <div class="form-grid" x-data="{ show: false }">
          <div>
            <label class="label" for="r-password">Password <span class="text-danger">*</span></label>
            <div class="relative">
              <input id="r-password" name="password" :type="show ? 'text' : 'password'" required minlength="8" autocomplete="new-password"
                     class="field pr-10 <?= error_for('password') ? 'field-error' : '' ?>">
              <button type="button" @click="show = !show" class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-ink-faint hover:text-ink" :aria-label="show ? 'Hide password' : 'Show password'">
                <?= icon('eye', 'h-4 w-4') ?>
              </button>
            </div>
            <?php if ($m = error_for('password')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p>
            <?php else: ?><p class="hint">At least 8 characters.</p><?php endif; ?>
          </div>
          <div>
            <label class="label" for="r-password2">Confirm password <span class="text-danger">*</span></label>
            <input id="r-password2" name="password_confirmation" :type="show ? 'text' : 'password'" required autocomplete="new-password"
                   class="field <?= error_for('password_confirmation') ? 'field-error' : '' ?>">
            <?php if ($m = error_for('password_confirmation')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
          </div>
        </div>

        <label class="flex items-start gap-2.5 text-sm text-ink-soft">
          <input type="checkbox" name="terms" value="1" class="checkbox" required>
          <span>I have read and accept the <a href="<?= url('/terms') ?>" class="link" target="_blank">terms of use</a>
            and the <a href="<?= url('/privacy') ?>" class="link" target="_blank">privacy policy</a>.</span>
        </label>
        <?php if ($m = error_for('terms')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>

        <button type="submit" class="btn-primary btn-lg btn-block"><?= icon('check', 'h-4 w-4') ?>Create my account</button>
      </form>
    <?php endif; ?>
  </div>

  <div class="border-t border-line px-5 py-4 text-center text-sm text-ink-soft sm:px-6">
    Already registered? <a href="<?= url('/login') ?>" class="link">Sign in</a>
    &nbsp;·&nbsp; Hiring? <a href="<?= url('/employer/register') ?>" class="link">Register as employer</a>
  </div>
</div>
<?php
$slot = ob_get_clean();
partial('auth-shell', [
    'slot'       => $slot,
    'asideTitle' => 'One profile, every opportunity',
    'asideSub'   => 'Register once and your details follow you across every vacancy, skilling programme and career service on the platform.',
]);
