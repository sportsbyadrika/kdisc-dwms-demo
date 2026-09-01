<?php
/** @var array $offices @var array $tree @var array|null $editing @var array $parents @var array $districts */
$typeBadge = ['office' => 'badge-blue', 'department' => 'badge-amber', 'section' => 'badge-gray'];
$formOpen  = $editing !== null || has_errors();

$renderNode = static function (array $node, int $depth) use (&$renderNode, $tree, $typeBadge) {
    ?>
    <li>
      <div class="flex flex-wrap items-center gap-3 rounded-card border border-line bg-white px-4 py-3"
           style="margin-left: <?= $depth * 20 ?>px">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded bg-brand-50 text-brand-500">
          <?= icon($node['type'] === 'office' ? 'building' : ($node['type'] === 'department' ? 'layers' : 'grid'), 'h-4 w-4') ?>
        </span>
        <div class="min-w-0 flex-1">
          <p class="flex flex-wrap items-center gap-2 text-sm font-semibold text-ink">
            <?= e($node['name']) ?>
            <span class="<?= $typeBadge[$node['type']] ?>"><?= e(ucfirst($node['type'])) ?></span>
            <?php if (!$node['is_active']): ?><span class="badge-red">Inactive</span><?php endif; ?>
          </p>
          <p class="flex flex-wrap gap-x-3 text-xs text-ink-faint">
            <?php if ($node['code']): ?><span><?= e($node['code']) ?></span><?php endif; ?>
            <?php if ($node['district']): ?><span><?= e($node['district']) ?></span><?php endif; ?>
            <span><?= (int) $node['users'] ?> user(s)</span>
            <?php if ($node['children']): ?><span><?= (int) $node['children'] ?> sub-unit(s)</span><?php endif; ?>
          </p>
        </div>
        <div class="flex shrink-0 gap-1">
          <a href="<?= url('/official/offices', ['edit' => $node['id']]) ?>"
             class="rounded p-1.5 text-ink-faint hover:bg-brand-50 hover:text-brand-700" aria-label="Edit"><?= icon('edit', 'h-4 w-4') ?></a>
          <form method="post" action="<?= url('/official/offices/' . $node['id'] . '/delete') ?>" data-confirm="Delete this office? This cannot be undone.">
            <?= csrf_field() ?>
            <button type="submit" class="rounded p-1.5 text-ink-faint hover:bg-danger/10 hover:text-danger" aria-label="Delete"><?= icon('trash', 'h-4 w-4') ?></button>
          </form>
        </div>
      </div>
      <?php if (!empty($tree[$node['id']])): ?>
        <ul class="mt-2 space-y-2"><?php foreach ($tree[$node['id']] as $child) { $renderNode($child, $depth + 1); } ?></ul>
      <?php endif; ?>
    </li>
    <?php
};
?>
<?php partial('dash-header', [
  'title' => 'Offices, departments and sections',
  'sub'   => 'One tree: an office holds departments, and a department holds sections. Users are attached to any level.',
  'actions' => '<span class="badge-gray">' . count($offices) . ' units</span>',
]); ?>

<div x-data="{ open: <?= $formOpen ? 'true' : 'false' ?> }" class="space-y-4">
  <?php if ($offices): ?>
    <ul class="space-y-2">
      <?php foreach ($tree[0] ?? [] as $root) { $renderNode($root, 0); } ?>
    </ul>
  <?php else: ?>
    <?php partial('empty-state', [
      'icon' => 'building', 'title' => 'No offices yet',
      'message' => 'Create the top-level office first, then add departments and sections under it.',
    ]); ?>
  <?php endif; ?>

  <button type="button" x-show="!open" @click="open = true" class="btn-outline btn-block"><?= icon('plus', 'h-4 w-4') ?>Add an office, department or section</button>

  <div x-show="open" x-cloak x-transition class="card">
    <div class="card-head">
      <h2 class="card-title"><?= $editing ? 'Edit' : 'Add' ?> unit</h2>
      <?php if ($editing): ?><a href="<?= url('/official/offices') ?>" class="btn-ghost btn-sm">Cancel edit</a>
      <?php else: ?><button type="button" @click="open = false" class="btn-ghost btn-sm">Close</button><?php endif; ?>
    </div>
    <form method="post" action="<?= url('/official/offices') ?>" class="card-pad">
      <?= csrf_field() ?>
      <?php if ($editing): ?><input type="hidden" name="office_id" value="<?= (int) $editing['id'] ?>"><?php endif; ?>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
          <label class="label" for="o-name">Name <span class="text-danger">*</span></label>
          <input id="o-name" name="name" required class="field <?= error_for('name') ? 'field-error' : '' ?>" value="<?= e(old('name', $editing['name'] ?? '')) ?>">
          <?php if ($m = error_for('name')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="label" for="o-type">Type <span class="text-danger">*</span></label>
          <select id="o-type" name="type" required class="field <?= error_for('type') ? 'field-error' : '' ?>">
            <?php foreach (['office' => 'Office (top level)', 'department' => 'Department', 'section' => 'Section'] as $k => $label): ?>
              <option value="<?= $k ?>" <?= old('type', $editing['type'] ?? 'office') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="label" for="o-parent">Parent unit</label>
          <select id="o-parent" name="parent_id" class="field <?= error_for('parent_id') ? 'field-error' : '' ?>">
            <option value="">None (top-level office)</option>
            <?php foreach ($parents as $p): if ($editing && (int) $p['id'] === (int) $editing['id']) { continue; } ?>
              <option value="<?= (int) $p['id'] ?>" <?= (string) old('parent_id', $editing['parent_id'] ?? '') === (string) $p['id'] ? 'selected' : '' ?>>
                <?= e($p['name']) ?> (<?= e(ucfirst($p['type'])) ?>)
              </option>
            <?php endforeach; ?>
          </select>
          <?php if ($m = error_for('parent_id')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p>
          <?php else: ?><p class="hint">Required for departments and sections.</p><?php endif; ?>
        </div>
        <div>
          <label class="label" for="o-code">Code</label>
          <input id="o-code" name="code" maxlength="40" class="field <?= error_for('code') ? 'field-error' : '' ?>" value="<?= e(old('code', $editing['code'] ?? '')) ?>">
          <?php if ($m = error_for('code')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="label" for="o-district">District</label>
          <select id="o-district" name="district" class="field">
            <option value="">Not set</option>
            <?php foreach ($districts as $d): ?>
              <option value="<?= e($d) ?>" <?= old('district', $editing['district'] ?? '') === $d ? 'selected' : '' ?>><?= e($d) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="sm:col-span-2">
          <label class="label" for="o-address">Address</label>
          <input id="o-address" name="address" class="field" value="<?= e(old('address', $editing['address'] ?? '')) ?>">
        </div>
        <div>
          <label class="label" for="o-phone">Phone</label>
          <input id="o-phone" name="phone" class="field" value="<?= e(old('phone', $editing['phone'] ?? '')) ?>">
        </div>
        <div>
          <label class="label" for="o-email">E-mail</label>
          <input id="o-email" name="email" type="email" class="field <?= error_for('email') ? 'field-error' : '' ?>" value="<?= e(old('email', $editing['email'] ?? '')) ?>">
          <?php if ($m = error_for('email')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>
        <div class="sm:col-span-2">
          <label class="flex items-center gap-2.5 text-sm text-ink-soft">
            <input type="checkbox" name="is_active" value="1" class="checkbox" <?= old('is_active', $editing['is_active'] ?? 1) ? 'checked' : '' ?>>
            <span>Active</span>
          </label>
        </div>
      </div>

      <div class="mt-5 flex flex-wrap gap-2">
        <button type="submit" class="btn-primary"><?= icon('check', 'h-4 w-4') ?><?= $editing ? 'Save changes' : 'Create unit' ?></button>
        <a href="<?= url('/official/offices') ?>" class="btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>
