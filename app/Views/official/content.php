<?php
/** @var string $section @var array $spec @var array $rows @var array|null $editing */
$formOpen = $editing !== null || has_errors();
$statusTone = ['published' => 'badge-green', 'draft' => 'badge-amber', 'archived' => 'badge-gray'];
?>
<?php partial('dash-header', [
  'title' => $spec['title'],
  'sub'   => $spec['sub'],
  'actions' => '<span class="badge-gray">' . count($rows) . ' ' . e($spec['singular']) . '(s)</span>',
]); ?>

<div x-data="{ open: <?= $formOpen ? 'true' : 'false' ?> }" class="space-y-4">
  <?php if ($rows): ?>
    <div class="space-y-3">
      <?php foreach ($rows as $r): $d = $spec['display']($r); ?>
        <article class="card card-pad">
          <div class="flex flex-wrap items-start gap-4">
            <span class="flex h-14 w-20 shrink-0 items-center justify-center overflow-hidden rounded bg-brand-50 text-brand-400">
              <?php if (!empty($d['image'])): ?>
                <img src="<?= e(upload_url($d['image'])) ?>" alt="" class="h-full w-full object-cover">
              <?php else: ?><?= icon($spec['icon'], 'h-6 w-6') ?><?php endif; ?>
            </span>

            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="min-w-0">
                  <h2 class="flex flex-wrap items-center gap-2 text-sm font-semibold text-ink">
                    <?= e($d['title']) ?>
                    <?php if (isset($d['status'])): ?>
                      <span class="<?= $statusTone[$d['status']] ?? 'badge-gray' ?>"><?= e(ucfirst($d['status'])) ?></span>
                    <?php elseif (isset($d['active'])): ?>
                      <span class="<?= $d['active'] ? 'badge-green' : 'badge-gray' ?>"><?= $d['active'] ? 'Visible' : 'Hidden' ?></span>
                    <?php endif; ?>
                  </h2>
                  <?php if (!empty($d['subtitle'])): ?><p class="text-sm text-ink-soft"><?= e(str_excerpt($d['subtitle'], 130)) ?></p><?php endif; ?>
                </div>
                <div class="flex shrink-0 gap-1">
                  <?php if (!empty($d['publicPath']) && ($d['status'] ?? '') === 'published'): ?>
                    <a href="<?= url($d['publicPath']) ?>" target="_blank" rel="noopener"
                       class="rounded p-1.5 text-ink-faint hover:bg-brand-50 hover:text-brand-700" aria-label="View live"><?= icon('external', 'h-4 w-4') ?></a>
                  <?php endif; ?>
                  <a href="<?= url('/official/' . $section, ['edit' => $r['id']]) ?>"
                     class="rounded p-1.5 text-ink-faint hover:bg-brand-50 hover:text-brand-700" aria-label="Edit"><?= icon('edit', 'h-4 w-4') ?></a>
                  <form method="post" action="<?= url('/official/' . $section . '/' . $r['id'] . '/delete') ?>"
                        data-confirm="Delete this <?= e($spec['singular']) ?>? This cannot be undone.">
                    <?= csrf_field() ?>
                    <button type="submit" class="rounded p-1.5 text-ink-faint hover:bg-danger/10 hover:text-danger" aria-label="Delete"><?= icon('trash', 'h-4 w-4') ?></button>
                  </form>
                </div>
              </div>

              <?php if ($d['meta']): ?>
                <p class="mt-1.5 flex flex-wrap gap-x-3 gap-y-1 text-xs text-ink-faint">
                  <?php foreach ($d['meta'] as $m): ?><span><?= e($m) ?></span><?php endforeach; ?>
                </p>
              <?php endif; ?>

              <?php if (!empty($spec['statusColumn'])): ?>
                <div class="mt-3 flex flex-wrap gap-2">
                  <?php foreach (['published' => 'Publish', 'draft' => 'Move to draft', 'archived' => 'Archive'] as $status => $label):
                      if ($d['status'] === $status) { continue; } ?>
                    <form method="post" action="<?= url('/official/' . $section . '/' . $r['id'] . '/status') ?>">
                      <?= csrf_field() ?>
                      <input type="hidden" name="status" value="<?= $status ?>">
                      <button type="submit" class="<?= $status === 'published' ? 'btn-primary' : 'btn-ghost' ?> btn-sm"><?= e($label) ?></button>
                    </form>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div x-show="!open">
      <?php partial('empty-state', [
        'icon' => $spec['icon'], 'title' => 'Nothing here yet', 'message' => $spec['empty'],
        'action' => '<button type="button" @click="open = true" class="btn-primary btn-sm">' . icon('plus', 'h-4 w-4') . 'Add ' . e($spec['singular']) . '</button>',
      ]); ?>
    </div>
  <?php endif; ?>

  <button type="button" x-show="!open" @click="open = true" class="btn-outline btn-block"><?= icon('plus', 'h-4 w-4') ?>Add <?= e($spec['singular']) ?></button>

  <div x-show="open" x-cloak x-transition class="card">
    <div class="card-head">
      <h2 class="card-title"><?= $editing ? 'Edit' : 'Add' ?> <?= e($spec['singular']) ?></h2>
      <?php if ($editing): ?><a href="<?= url('/official/' . $section) ?>" class="btn-ghost btn-sm">Cancel edit</a>
      <?php else: ?><button type="button" @click="open = false" class="btn-ghost btn-sm">Close</button><?php endif; ?>
    </div>
    <form method="post" action="<?= url('/official/' . $section) ?>" enctype="multipart/form-data" class="card-pad">
      <?= csrf_field() ?>
      <?php if ($editing): ?><input type="hidden" name="record_id" value="<?= (int) $editing['id'] ?>"><?php endif; ?>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <?php foreach ($spec['fields'] as $name => $f):
            partial('field', ['name' => $name, 'f' => $f, 'value' => old($name, $editing[$name] ?? ''), 'idPrefix' => $section]);
        endforeach; ?>
      </div>

      <div class="mt-5 flex flex-wrap gap-2">
        <button type="submit" class="btn-primary"><?= icon('check', 'h-4 w-4') ?><?= $editing ? 'Save changes' : 'Add ' . e($spec['singular']) ?></button>
        <a href="<?= url('/official/' . $section) ?>" class="btn-ghost">Cancel</a>
      </div>
      <?php if (!$editing && !empty($spec['statusColumn'])): ?>
        <p class="mt-3 text-xs text-ink-faint">New records are saved as a draft. Publish them from the list once you are happy.</p>
      <?php endif; ?>
    </form>
  </div>
</div>
