<?php partial('dash-header', ['title' => 'Change password', 'sub' => 'Choose a password you do not use anywhere else.']); ?>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-[1.3fr,1fr]">
  <div class="card">
    <div class="card-head"><h2 class="card-title">Update your password</h2></div>
    <form method="post" action="<?= url('/employer/password') ?>" class="card-pad fieldset">
      <?= csrf_field() ?>
      <div>
        <label class="label" for="p-current">Current password <span class="text-danger">*</span></label>
        <input id="p-current" name="current_password" type="password" required autocomplete="current-password"
               class="field <?= error_for('current_password') ? 'field-error' : '' ?>">
        <?php if ($m = error_for('current_password')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
      </div>
      <div>
        <label class="label" for="p-new">New password <span class="text-danger">*</span></label>
        <input id="p-new" name="password" type="password" minlength="8" required autocomplete="new-password"
               class="field <?= error_for('password') ? 'field-error' : '' ?>">
        <?php if ($m = error_for('password')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p>
        <?php else: ?><p class="hint">At least 8 characters.</p><?php endif; ?>
      </div>
      <div>
        <label class="label" for="p-confirm">Confirm new password <span class="text-danger">*</span></label>
        <input id="p-confirm" name="password_confirmation" type="password" required autocomplete="new-password"
               class="field <?= error_for('password_confirmation') ? 'field-error' : '' ?>">
        <?php if ($m = error_for('password_confirmation')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
      </div>
      <button type="submit" class="btn-primary"><?= icon('key', 'h-4 w-4') ?>Change password</button>
    </form>
  </div>

  <div class="card card-pad">
    <h2 class="card-title">Keeping your account safe</h2>
    <ul class="mt-3 space-y-2.5 text-sm text-ink-soft">
      <?php foreach ([
        'Use a password you do not use on any other website.',
        'Longer is stronger — a short phrase beats a complicated single word.',
        'Never share your password or a one-time password, not even with someone claiming to be from the department.',
        'Sign out when you use a shared or public computer.',
      ] as $tip): ?>
        <li class="flex gap-2"><span class="shrink-0 text-brand-500"><?= icon('check', 'h-4 w-4') ?></span><?= e($tip) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
