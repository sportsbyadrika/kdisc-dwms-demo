<?php
$flashes = take_flashes();
if (!$flashes) {
    return;
}
$styles = [
    'success' => ['badge-green', 'check-circle', 'border-success/30 bg-success/5 text-success'],
    'error'   => ['badge-red',   'x-circle',     'border-danger/30 bg-danger/5 text-danger'],
    'warning' => ['badge-amber', 'alert',        'border-warning/30 bg-warning/5 text-warning'],
    'info'    => ['badge-blue',  'info',         'border-brand-200 bg-brand-50 text-brand-700'],
];
?>
<div class="shell pt-4">
  <div class="space-y-2">
    <?php foreach ($flashes as $f):
        [$badge, $ic, $box] = $styles[$f['type']] ?? $styles['info']; ?>
      <div x-data="{ show: true }" x-show="show" x-transition
           class="flex items-start gap-3 rounded-card border px-4 py-3 text-sm font-medium <?= $box ?>" role="status">
        <span class="mt-0.5 shrink-0"><?= icon($ic, 'h-4 w-4') ?></span>
        <p class="flex-1"><?= e($f['message']) ?></p>
        <button type="button" @click="show = false" class="shrink-0 opacity-60 hover:opacity-100" aria-label="Dismiss"><?= icon('x', 'h-4 w-4') ?></button>
      </div>
    <?php endforeach; ?>
  </div>
</div>
