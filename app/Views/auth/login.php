<?php
/** @var string $heading @var string $sub @var string $action @var array $altLinks */
ob_start();
?>
<div class="card">
  <div class="card-pad">
    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-50 text-brand-500"><?= icon($guardIcon ?? 'user') ?></span>
    <h1 class="mt-4 text-2xl font-bold text-ink"><?= e($heading) ?></h1>
    <p class="mt-1 text-sm text-ink-soft"><?= e($sub) ?></p>

    <form method="post" action="<?= url($action) ?>" class="mt-6 fieldset">
      <?= csrf_field() ?>
      <div>
        <label class="label" for="l-email">E-mail address</label>
        <input id="l-email" name="email" type="email" required autofocus autocomplete="username"
               class="field <?= error_for('email') ? 'field-error' : '' ?>" value="<?= e(old('email')) ?>" placeholder="you@example.com">
        <?php if ($m = error_for('email')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
      </div>
      <div x-data="{ show: false }">
        <label class="label" for="l-password">Password</label>
        <div class="relative">
          <input id="l-password" name="password" :type="show ? 'text' : 'password'" required autocomplete="current-password" class="field pr-10">
          <button type="button" @click="show = !show" class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-ink-faint hover:text-ink"
                  :aria-label="show ? 'Hide password' : 'Show password'"><?= icon('eye', 'h-4 w-4') ?></button>
        </div>
      </div>
      <div class="flex items-center justify-between text-sm">
        <label class="flex items-center gap-2 text-ink-soft"><input type="checkbox" name="remember" value="1" class="checkbox"> Remember me</label>
        <?php if (!empty($forgotPath)): ?><a href="<?= url($forgotPath) ?>" class="link">Forgot password?</a><?php endif; ?>
      </div>
      <button type="submit" class="btn-primary btn-lg btn-block"><?= icon('lock', 'h-4 w-4') ?>Sign in</button>
    </form>

    <?php if (!empty($registerPath)): ?>
      <div class="my-6 flex items-center gap-3 text-xs uppercase tracking-wide text-ink-faint">
        <span class="h-px flex-1 bg-line"></span>or<span class="h-px flex-1 bg-line"></span>
      </div>
      <a href="<?= url($registerPath) ?>" class="btn-outline btn-block"><?= icon('plus', 'h-4 w-4') ?><?= e($registerLabel) ?></a>
    <?php endif; ?>
  </div>

  <?php if (!empty($altLinks)): ?>
    <div class="border-t border-line px-5 py-4 text-center text-sm text-ink-soft sm:px-6">
      Signing in as someone else?
      <?php foreach ($altLinks as $i => [$label, $path]): ?>
        <?= $i ? ' · ' : ' ' ?><a href="<?= url($path) ?>" class="link"><?= e($label) ?></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php
$slot = ob_get_clean();
partial('auth-shell', [
    'slot'       => $slot,
    'tone'       => $tone ?? 'brand',
    'asideTitle' => $asideTitle ?? 'Why register?',
    'asideSub'   => $asideSub ?? null,
    'points'     => $points ?? null,
]);
