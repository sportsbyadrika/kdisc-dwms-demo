<?php
/** @var array $users @var array|null $editing @var array $roles @var array $offices @var array|null $generated @var array $me */
$formOpen = $editing !== null || has_errors();
?>
<?php partial('dash-header', [
  'title' => 'Users',
  'sub'   => 'Departmental accounts. A new user gets a one-time password and must change it at first sign-in.',
  'actions' => '<span class="badge-gray">' . count($users) . ' users</span>',
]); ?>

<?php if ($generated): ?>
  <div class="mb-4 rounded-card border border-success/30 bg-success/5 p-5">
    <p class="flex items-center gap-2 text-sm font-semibold text-success"><?= icon('key', 'h-4 w-4') ?>Temporary password generated</p>
    <p class="mt-1 text-sm text-ink-soft">Share these credentials securely. This password is shown only once and cannot be retrieved later.</p>
    <dl class="mt-3 grid gap-3 sm:grid-cols-2">
      <div class="rounded bg-white px-4 py-2.5">
        <dt class="text-xs font-medium uppercase tracking-wide text-ink-faint">E-mail</dt>
        <dd class="font-mono text-sm font-semibold text-ink"><?= e($generated['email']) ?></dd>
      </div>
      <div class="rounded bg-white px-4 py-2.5">
        <dt class="text-xs font-medium uppercase tracking-wide text-ink-faint">Temporary password</dt>
        <dd class="font-mono text-sm font-semibold text-ink"><?= e($generated['password']) ?></dd>
      </div>
    </dl>
  </div>
<?php endif; ?>

<div x-data="{ open: <?= $formOpen ? 'true' : 'false' ?> }" class="space-y-4">
  <div class="card">
    <div class="scroll-x">
      <table class="table">
        <thead><tr><th>User</th><th>Role</th><th>Office</th><th>Last sign-in</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td>
                <span class="flex items-center gap-2.5">
                  <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-100 text-[11px] font-bold text-brand-700"><?= e(initials($u['name'])) ?></span>
                  <span class="min-w-0">
                    <span class="block font-medium text-ink"><?= e($u['name']) ?><?= (int) $u['id'] === (int) $me['id'] ? ' <span class="badge-gray">You</span>' : '' ?></span>
                    <span class="block truncate text-xs text-ink-faint"><?= e($u['email']) ?><?= $u['designation'] ? ' · ' . e($u['designation']) : '' ?></span>
                  </span>
                </span>
              </td>
              <td><span class="badge-blue"><?= e($u['role_name']) ?></span></td>
              <td class="text-sm text-ink-soft"><?= e($u['office_name'] ?: '—') ?><?php if ($u['office_type']): ?><span class="block text-xs text-ink-faint"><?= e(ucfirst($u['office_type'])) ?></span><?php endif; ?></td>
              <td class="whitespace-nowrap text-sm text-ink-soft"><?= e($u['last_login_at'] ? fdate($u['last_login_at'], 'd M Y') : 'Never') ?></td>
              <td>
                <?php if (!$u['is_active']): ?><span class="badge-red">Inactive</span>
                <?php elseif ($u['must_reset']): ?><span class="badge-amber">Password reset due</span>
                <?php else: ?><span class="badge-green">Active</span><?php endif; ?>
              </td>
              <td>
                <div class="flex justify-end gap-1">
                  <a href="<?= url('/official/users', ['edit' => $u['id']]) ?>" class="rounded p-1.5 text-ink-faint hover:bg-brand-50 hover:text-brand-700" aria-label="Edit"><?= icon('edit', 'h-4 w-4') ?></a>
                  <form method="post" action="<?= url('/official/users/' . $u['id'] . '/reset-password') ?>" data-confirm="Generate a new temporary password for this user?">
                    <?= csrf_field() ?>
                    <button type="submit" class="rounded p-1.5 text-ink-faint hover:bg-brand-50 hover:text-brand-700" aria-label="Reset password"><?= icon('key', 'h-4 w-4') ?></button>
                  </form>
                  <?php if ((int) $u['id'] !== (int) $me['id'] && $u['is_active']): ?>
                    <form method="post" action="<?= url('/official/users/' . $u['id'] . '/deactivate') ?>" data-confirm="Deactivate this user? They will not be able to sign in.">
                      <?= csrf_field() ?>
                      <button type="submit" class="rounded p-1.5 text-ink-faint hover:bg-danger/10 hover:text-danger" aria-label="Deactivate"><?= icon('logout', 'h-4 w-4') ?></button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <button type="button" x-show="!open" @click="open = true" class="btn-outline btn-block"><?= icon('plus', 'h-4 w-4') ?>Create a user</button>

  <div x-show="open" x-cloak x-transition class="card">
    <div class="card-head">
      <h2 class="card-title"><?= $editing ? 'Edit user' : 'Create a user' ?></h2>
      <?php if ($editing): ?><a href="<?= url('/official/users') ?>" class="btn-ghost btn-sm">Cancel edit</a>
      <?php else: ?><button type="button" @click="open = false" class="btn-ghost btn-sm">Close</button><?php endif; ?>
    </div>
    <form method="post" action="<?= url('/official/users') ?>" class="card-pad">
      <?= csrf_field() ?>
      <?php if ($editing): ?><input type="hidden" name="user_id" value="<?= (int) $editing['id'] ?>"><?php endif; ?>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label class="label" for="u-name">Full name <span class="text-danger">*</span></label>
          <input id="u-name" name="name" required class="field <?= error_for('name') ? 'field-error' : '' ?>" value="<?= e(old('name', $editing['name'] ?? '')) ?>">
          <?php if ($m = error_for('name')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="label" for="u-designation">Designation</label>
          <input id="u-designation" name="designation" class="field" value="<?= e(old('designation', $editing['designation'] ?? '')) ?>">
        </div>
        <div>
          <label class="label" for="u-email">E-mail <span class="text-danger">*</span></label>
          <input id="u-email" name="email" type="email" required class="field <?= error_for('email') ? 'field-error' : '' ?>" value="<?= e(old('email', $editing['email'] ?? '')) ?>">
          <?php if ($m = error_for('email')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="label" for="u-mobile">Mobile</label>
          <input id="u-mobile" name="mobile" inputmode="numeric" maxlength="10" class="field <?= error_for('mobile') ? 'field-error' : '' ?>" value="<?= e(old('mobile', $editing['mobile'] ?? '')) ?>">
          <?php if ($m = error_for('mobile')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="label" for="u-role">Role <span class="text-danger">*</span></label>
          <select id="u-role" name="role_id" required class="field <?= error_for('role_id') ? 'field-error' : '' ?>">
            <option value="">Select a role</option>
            <?php foreach ($roles as $r): ?>
              <option value="<?= (int) $r['id'] ?>" <?= (string) old('role_id', $editing['role_id'] ?? '') === (string) $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if ($m = error_for('role_id')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p>
          <?php else: ?><p class="hint">The role decides which sections the user can reach.</p><?php endif; ?>
        </div>
        <div>
          <label class="label" for="u-office">Office / department / section</label>
          <select id="u-office" name="office_id" class="field">
            <option value="">Not attached</option>
            <?php foreach ($offices as $o): ?>
              <option value="<?= (int) $o['id'] ?>" <?= (string) old('office_id', $editing['office_id'] ?? '') === (string) $o['id'] ? 'selected' : '' ?>>
                <?= e($o['name']) ?> (<?= e(ucfirst($o['type'])) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="sm:col-span-2">
          <label class="flex items-center gap-2.5 text-sm text-ink-soft">
            <input type="checkbox" name="is_active" value="1" class="checkbox" <?= old('is_active', $editing['is_active'] ?? 1) ? 'checked' : '' ?>>
            <span>Active — the user can sign in</span>
          </label>
        </div>
      </div>

      <?php if (!$editing): ?>
        <p class="mt-4 flex items-start gap-2 rounded-card bg-canvas px-4 py-3 text-xs text-ink-soft">
          <span class="mt-0.5 shrink-0 text-brand-500"><?= icon('info', 'h-4 w-4') ?></span>
          A temporary password is generated and shown once after the user is created. The user must change it at first sign-in.
        </p>
      <?php endif; ?>

      <div class="mt-5 flex flex-wrap gap-2">
        <button type="submit" class="btn-primary"><?= icon('check', 'h-4 w-4') ?><?= $editing ? 'Save changes' : 'Create user' ?></button>
        <a href="<?= url('/official/users') ?>" class="btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>
