<?php
/** @var array $roles @var array|null $editing @var array $permissions */
$formOpen = $editing !== null || has_errors();
$editingPerms = $editing ? (json_decode((string) $editing['permissions'], true) ?: []) : [];
?>
<?php partial('dash-header', [
  'title' => 'Roles and permissions',
  'sub'   => 'A role is a named set of permissions. Every user holds exactly one role.',
  'actions' => '<span class="badge-gray">' . count($roles) . ' roles</span>',
]); ?>

<div x-data="{ open: <?= $formOpen ? 'true' : 'false' ?> }" class="space-y-4">
  <div class="grid gap-3 sm:grid-cols-2">
    <?php foreach ($roles as $r):
        $perms = json_decode((string) $r['permissions'], true) ?: [];
        $isAll = in_array('*', $perms, true); ?>
      <article class="card card-pad">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <h2 class="flex flex-wrap items-center gap-2 text-sm font-semibold text-ink">
              <?= e($r['name']) ?>
              <?php if ((int) $r['is_system']): ?><span class="badge-gray">System</span><?php endif; ?>
            </h2>
            <p class="text-xs text-ink-faint"><?= e($r['slug']) ?> · <?= (int) $r['users'] ?> user(s)</p>
          </div>
          <div class="flex shrink-0 gap-1">
            <a href="<?= url('/official/roles', ['edit' => $r['id']]) ?>" class="rounded p-1.5 text-ink-faint hover:bg-brand-50 hover:text-brand-700" aria-label="Edit"><?= icon('edit', 'h-4 w-4') ?></a>
            <?php if (!(int) $r['is_system'] && (int) $r['users'] === 0): ?>
              <form method="post" action="<?= url('/official/roles/' . $r['id'] . '/delete') ?>" data-confirm="Delete this role?">
                <?= csrf_field() ?>
                <button type="submit" class="rounded p-1.5 text-ink-faint hover:bg-danger/10 hover:text-danger" aria-label="Delete"><?= icon('trash', 'h-4 w-4') ?></button>
              </form>
            <?php endif; ?>
          </div>
        </div>
        <?php if ($r['description']): ?><p class="mt-2 text-sm text-ink-soft"><?= e($r['description']) ?></p><?php endif; ?>
        <div class="mt-3 flex flex-wrap gap-1.5">
          <?php if ($isAll): ?>
            <span class="badge-green"><?= icon('check', 'h-3 w-3') ?>Full access</span>
          <?php else: foreach ($perms as $p): ?>
            <span class="chip"><?= e($permissions[$p] ?? $p) ?></span>
          <?php endforeach; endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <button type="button" x-show="!open" @click="open = true" class="btn-outline btn-block"><?= icon('plus', 'h-4 w-4') ?>Create a role</button>

  <div x-show="open" x-cloak x-transition class="card">
    <div class="card-head">
      <h2 class="card-title"><?= $editing ? 'Edit role' : 'Create a role' ?></h2>
      <?php if ($editing): ?><a href="<?= url('/official/roles') ?>" class="btn-ghost btn-sm">Cancel edit</a>
      <?php else: ?><button type="button" @click="open = false" class="btn-ghost btn-sm">Close</button><?php endif; ?>
    </div>
    <form method="post" action="<?= url('/official/roles') ?>" class="card-pad">
      <?= csrf_field() ?>
      <?php if ($editing): ?><input type="hidden" name="role_id" value="<?= (int) $editing['id'] ?>"><?php endif; ?>

      <?php if ($editing && $editing['slug'] === 'super_admin'): ?>
        <p class="mb-4 flex items-start gap-2 rounded-card border border-warning/30 bg-warning/5 px-4 py-3 text-sm text-ink-soft">
          <span class="mt-0.5 shrink-0 text-warning"><?= icon('alert', 'h-4 w-4') ?></span>
          The super administrator role always keeps full access — its permissions cannot be narrowed.
        </p>
      <?php endif; ?>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label class="label" for="r-name">Role name <span class="text-danger">*</span></label>
          <input id="r-name" name="name" required class="field <?= error_for('name') ? 'field-error' : '' ?>" value="<?= e(old('name', $editing['name'] ?? '')) ?>">
          <?php if ($m = error_for('name')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="label" for="r-desc">Description</label>
          <input id="r-desc" name="description" maxlength="255" class="field" value="<?= e(old('description', $editing['description'] ?? '')) ?>">
        </div>
      </div>

      <fieldset class="mt-5">
        <legend class="label">Permissions <span class="text-danger">*</span></legend>
        <?php if ($m = error_for('permissions')): ?><p class="err mb-2"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        <div class="grid gap-2 sm:grid-cols-2">
          <?php foreach ($permissions as $key => $label): ?>
            <label class="flex items-start gap-2.5 rounded-card border border-line px-3 py-2.5 text-sm transition hover:border-brand-200 hover:bg-brand-50/40">
              <input type="checkbox" name="permissions[]" value="<?= e($key) ?>" class="checkbox"
                     <?= in_array($key, $editingPerms, true) || in_array('*', $editingPerms, true) ? 'checked' : '' ?>>
              <span>
                <span class="block font-medium text-ink"><?= e($label) ?></span>
                <span class="block font-mono text-[11px] text-ink-faint"><?= e($key) ?></span>
              </span>
            </label>
          <?php endforeach; ?>
        </div>
      </fieldset>

      <div class="mt-5 flex flex-wrap gap-2">
        <button type="submit" class="btn-primary"><?= icon('check', 'h-4 w-4') ?><?= $editing ? 'Save role' : 'Create role' ?></button>
        <a href="<?= url('/official/roles') ?>" class="btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>
