<?php
/** @var array $users @var array $groups @var bool $hasGroups @var string $selected @var int $ungrouped @var int $total
 *  @var array|null $editing @var array $roles @var array $offices @var array|null $generated @var array $me */
$formOpen = $editing !== null || has_errors();
$groupLabel = '';
foreach ($groups as $g) {
    if ($g['slug'] === $selected) {
        $groupLabel = $g['name'];
    }
}
if ($selected === 'ungrouped') {
    $groupLabel = 'No group assigned';
}
$showGroupCol = $hasGroups && $selected === '';
?>
<?php partial('dash-header', [
  'title' => 'Users',
  'sub'   => 'Departmental accounts. A new user gets a one-time password and must change it at first sign-in.',
  'actions' => ($groupLabel
        ? '<span class="badge-blue">' . e($groupLabel) . '</span>'
        : '') . '<span class="badge-gray">' . count($users) . ' of ' . (int) $total . ' users</span>',
]); ?>

<!-- ------------------------------------------------ user group cards -->
<?php if ($hasGroups): ?>
<section class="mb-5" aria-label="Filter users by group">
  <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
    <h2 class="text-xs font-bold uppercase tracking-wider text-ink-faint">User groups</h2>
    <?php if ($selected !== ''): ?>
      <a href="<?= url('/official/users') ?>" class="text-xs font-semibold text-brand-500 hover:text-brand-700">Clear filter</a>
    <?php endif; ?>
  </div>

  <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
    <!-- All users -->
    <a href="<?= url('/official/users') ?>" aria-current="<?= $selected === '' ? 'true' : 'false' ?>"
       class="group flex items-center gap-3 rounded-card border bg-white p-3.5 shadow-card transition hover:shadow-pop <?= $selected === '' ? 'border-brand-500 ring-1 ring-brand-500' : 'border-transparent' ?>">
      <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-card <?= $selected === '' ? 'bg-brand-500 text-white' : 'bg-brand-50 text-brand-500' ?>">
        <?= icon('grid', 'h-4 w-4') ?>
      </span>
      <span class="min-w-0 flex-1 text-sm font-semibold leading-snug text-ink">All users</span>
      <span class="shrink-0 rounded-full bg-black/5 px-2 py-0.5 text-[11px] font-bold text-ink-soft"><?= (int) $total ?></span>
    </a>

    <?php foreach ($groups as $g): $on = $selected === $g['slug']; ?>
      <a href="<?= url('/official/users', ['group' => $g['slug']]) ?>" aria-current="<?= $on ? 'true' : 'false' ?>"
         title="<?= e($g['description']) ?>"
         class="group flex items-center gap-3 rounded-card border bg-white p-3.5 shadow-card transition hover:shadow-pop <?= $on ? 'border-brand-500 ring-1 ring-brand-500' : 'border-transparent' ?>">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-card <?= $on ? 'bg-brand-500 text-white' : 'bg-brand-50 text-brand-500' ?>">
          <?= icon($g['icon'], 'h-4 w-4') ?>
        </span>
        <span class="min-w-0 flex-1 text-sm font-semibold leading-snug text-ink"><?= e($g['name']) ?></span>
        <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-bold <?= $on ? 'bg-brand-100 text-brand-700' : 'bg-black/5 text-ink-soft' ?>"><?= (int) $g['users'] ?></span>
      </a>
    <?php endforeach; ?>

    <?php if ($ungrouped > 0): $on = $selected === 'ungrouped'; ?>
      <a href="<?= url('/official/users', ['group' => 'ungrouped']) ?>" aria-current="<?= $on ? 'true' : 'false' ?>"
         class="group flex items-center gap-3 rounded-card border border-dashed bg-white p-3.5 shadow-card transition hover:shadow-pop <?= $on ? 'border-brand-500 ring-1 ring-brand-500' : 'border-line' ?>">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-card <?= $on ? 'bg-brand-500 text-white' : 'bg-black/5 text-ink-faint' ?>">
          <?= icon('alert', 'h-4 w-4') ?>
        </span>
        <span class="min-w-0 flex-1 text-sm font-semibold leading-snug text-ink">No group</span>
        <span class="shrink-0 rounded-full bg-black/5 px-2 py-0.5 text-[11px] font-bold text-ink-soft"><?= (int) $ungrouped ?></span>
      </a>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($generated): ?>
  <div class="mb-4 rounded-card border border-success/30 bg-success/5 p-5">
    <p class="flex items-center gap-2 text-sm font-semibold text-success"><?= icon('key', 'h-4 w-4') ?>Temporary password generated</p>
    <p class="mt-1 text-sm text-ink-soft">Share these credentials securely. This password is shown only once and cannot be retrieved later.</p>
    <dl class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
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
    <div class="scroll-x" tabindex="0" role="region" aria-label="Users table, scrolls horizontally">
      <table class="table [&_td]:px-3 [&_th]:px-3">
        <thead><tr>
          <th>User</th>
          <?php if ($showGroupCol): ?><th>Group</th><?php endif; ?>
          <th>Role</th><th>Office</th><th>Status</th><th></th>
        </tr></thead>
        <tbody>
          <?php if (!$users): ?>
            <tr>
              <td colspan="<?= $showGroupCol ? 6 : 5 ?>" class="!py-10 text-center">
                <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-brand-50 text-brand-400"><?= icon('users', 'h-5 w-5') ?></span>
                <span class="mt-3 block text-sm font-semibold text-ink">
                  <?= $groupLabel ? 'No users in ' . e($groupLabel) : 'No users yet' ?>
                </span>
                <span class="mt-1 block text-sm text-ink-soft">
                  <?= $groupLabel ? 'Create one in this group, or clear the filter to see everybody.' : 'Create the first departmental account below.' ?>
                </span>
                <?php if ($selected !== ''): ?>
                  <a href="<?= url('/official/users') ?>" class="btn-outline btn-sm mt-4">Show all users</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endif; ?>
          <?php foreach ($users as $u): ?>
            <tr>
              <td>
                <span class="flex items-center gap-2.5">
                  <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-100 text-[11px] font-bold text-brand-700"><?= e(initials($u['name'])) ?></span>
                  <span class="min-w-0">
                    <span class="block font-medium text-ink"><?= e($u['name']) ?><?= (int) $u['id'] === (int) $me['id'] ? ' <span class="badge-gray">You</span>' : '' ?></span>
                    <span class="block max-w-[11rem] truncate text-xs text-ink-faint"><?= e($u['email']) ?><?= $u['designation'] ? ' · ' . e($u['designation']) : '' ?></span>
                  </span>
                </span>
              </td>
              <?php if ($showGroupCol): ?>
                <td class="whitespace-nowrap">
                  <?php if ($u['group_name'] ?? null): ?>
                    <a href="<?= url('/official/users', ['group' => $u['group_slug']]) ?>"
                       class="inline-flex max-w-[9rem] items-center gap-1.5 text-sm text-ink-soft hover:text-brand-700"
                       title="<?= e($u['group_name']) ?>">
                      <?= icon($u['group_icon'], 'h-3.5 w-3.5 shrink-0 text-brand-500') ?>
                      <span class="truncate"><?= e($u['group_name']) ?></span>
                    </a>
                  <?php else: ?><span class="text-sm text-ink-faint">—</span><?php endif; ?>
                </td>
              <?php endif; ?>
              <td class="whitespace-nowrap"><span class="badge-blue max-w-[8rem]"><span class="truncate" title="<?= e($u['role_name']) ?>"><?= e($u['role_name']) ?></span></span></td>
              <td class="text-sm text-ink-soft">
                <span class="block max-w-[9rem] truncate" title="<?= e($u['office_name'] ?: '') ?>"><?= e($u['office_name'] ?: '—') ?></span>
                <?php if ($u['office_type']): ?><span class="block text-xs text-ink-faint"><?= e(ucfirst($u['office_type'])) ?></span><?php endif; ?>
              </td>
              <td class="whitespace-nowrap">
                <?php if (!$u['is_active']): ?><span class="badge-red">Inactive</span>
                <?php elseif ($u['must_reset']): ?><span class="badge-amber">Reset due</span>
                <?php else: ?><span class="badge-green">Active</span><?php endif; ?>
                <span class="mt-0.5 block text-xs text-ink-faint">
                  <?= $u['last_login_at'] ? 'Seen ' . e(fdate($u['last_login_at'], 'd M Y')) : 'Never signed in' ?>
                </span>
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
        <div<?= $hasGroups ? '' : ' hidden' ?>>
          <label class="label" for="u-group">User group</label>
          <select id="u-group" name="group_id" class="field <?= error_for('group_id') ? 'field-error' : '' ?>">
            <option value="">Not assigned</option>
            <?php
            // A new user created while a group card is selected defaults to it.
            $groupDefault = old('group_id', $editing['group_id'] ?? '');
            if ($groupDefault === '' && !$editing && $selected !== '' && $selected !== 'ungrouped') {
                foreach ($groups as $g) {
                    if ($g['slug'] === $selected) {
                        $groupDefault = $g['id'];
                    }
                }
            }
            foreach ($groups as $g): ?>
              <option value="<?= (int) $g['id'] ?>" <?= (string) $groupDefault === (string) $g['id'] ? 'selected' : '' ?>><?= e($g['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if ($m = error_for('group_id')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p>
          <?php else: ?><p class="hint">Decides which card on this page the user appears under.</p><?php endif; ?>
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
