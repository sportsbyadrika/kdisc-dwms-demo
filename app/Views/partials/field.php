<?php
/**
 * Renders one form control from a field spec.
 * @var string $name @var array $f @var mixed $value @var string|null $idPrefix
 */
$id      = ($idPrefix ?? 'f') . '-' . $name;
$err     = error_for($name);
$req     = strpos((string) ($f['rules'] ?? ''), 'required') !== false;
$classes = 'field' . ($err ? ' field-error' : '');
?>
<div class="<?= !empty($f['half']) ? '' : 'sm:col-span-2' ?>">
  <?php if ($f['type'] !== 'checkbox'): ?>
    <label class="label" for="<?= e($id) ?>"><?= e($f['label']) ?><?= $req ? ' <span class="text-danger">*</span>' : '' ?></label>
  <?php endif; ?>

  <?php if ($f['type'] === 'select'): ?>
    <select id="<?= e($id) ?>" name="<?= e($name) ?>" class="<?= $classes ?>" <?= $req ? 'required' : '' ?>>
      <option value="">Select…</option>
      <?php foreach ($f['options'] as $k => $label): ?>
        <option value="<?= e($k) ?>" <?= (string) $value === (string) $k ? 'selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>

  <?php elseif ($f['type'] === 'textarea'): ?>
    <textarea id="<?= e($id) ?>" name="<?= e($name) ?>" rows="<?= (int) ($f['rows'] ?? 4) ?>" class="<?= $classes ?>"
              <?= $req ? 'required' : '' ?> placeholder="<?= e($f['placeholder'] ?? '') ?>"><?= e($value) ?></textarea>

  <?php elseif ($f['type'] === 'checkbox'): ?>
    <label class="flex items-center gap-2.5 pt-1 text-sm text-ink-soft">
      <input id="<?= e($id) ?>" type="checkbox" name="<?= e($name) ?>" value="1" class="checkbox" <?= $value ? 'checked' : '' ?>>
      <span><?= e($f['label']) ?></span>
    </label>

  <?php elseif ($f['type'] === 'file'): ?>
    <input id="<?= e($id) ?>" type="file" name="<?= e($name) ?>"
           accept="<?= e(implode(',', array_map(static fn($x) => '.' . $x, $f['accept'] ?? []))) ?>"
           class="block w-full text-sm text-ink-soft file:mr-3 file:rounded-full file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
    <?php if (!empty($value)): ?>
      <p class="mt-1 flex items-center gap-1.5 text-xs text-ink-soft">
        <?= icon('document', 'h-3.5 w-3.5 text-brand-500') ?>
        <a href="<?= e(upload_url($value)) ?>" target="_blank" rel="noopener" class="link">Current file</a>
        <span class="text-ink-faint">— choosing a new file replaces it.</span>
      </p>
    <?php endif; ?>

  <?php else: ?>
    <input id="<?= e($id) ?>" type="<?= e($f['type']) ?>" name="<?= e($name) ?>" class="<?= $classes ?>"
           value="<?= e($value) ?>" <?= $req ? 'required' : '' ?>
           <?= isset($f['min']) ? 'min="' . e($f['min']) . '"' : '' ?>
           <?= isset($f['max']) ? 'max="' . e($f['max']) . '"' : '' ?>
           <?= isset($f['step']) ? 'step="' . e($f['step']) . '"' : '' ?>
           placeholder="<?= e($f['placeholder'] ?? '') ?>">
  <?php endif; ?>

  <?php if ($err): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($err) ?></p>
  <?php elseif (!empty($f['hint'])): ?><p class="hint"><?= e($f['hint']) ?></p><?php endif; ?>
</div>
