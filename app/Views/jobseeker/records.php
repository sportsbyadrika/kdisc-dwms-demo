<?php
/** @var string $section @var array $spec @var array $rows @var array|null $editing */
$formOpen = $editing !== null || has_errors();
?>
<?php partial('dash-header', [
  'title' => $spec['title'],
  'sub'   => $spec['sub'],
  'actions' => '<span class="badge-gray">' . count($rows) . ' recorded</span>',
]); ?>

<div x-data="{ open: <?= $formOpen ? 'true' : 'false' ?> }" class="space-y-4">

  <!-- existing records -->
  <?php if ($rows): ?>
    <div class="<?= !empty($spec['compact']) ? 'card card-pad' : 'space-y-3' ?>">
      <?php if (!empty($spec['compact'])): ?>
        <ul class="flex flex-wrap gap-2">
          <?php foreach ($rows as $r): $d = $spec['display']($r); ?>
            <li class="group flex items-center gap-2 rounded-full border border-line bg-white py-1.5 pl-3.5 pr-1.5 text-sm">
              <span class="font-medium text-ink"><?= e($d['title']) ?></span>
              <?php if ($d['meta']): ?><span class="text-xs text-ink-faint"><?= e(implode(' · ', $d['meta'])) ?></span><?php endif; ?>
              <a href="<?= url('/dashboard/' . $section, ['edit' => $r['id']]) ?>"
                 class="rounded-full p-1 text-ink-faint hover:bg-brand-50 hover:text-brand-700" aria-label="Edit"><?= icon('edit', 'h-3.5 w-3.5') ?></a>
              <form method="post" action="<?= url('/dashboard/' . $section . '/' . $r['id'] . '/delete') ?>"
                    data-confirm="Delete this <?= e($spec['singular']) ?>?">
                <?= csrf_field() ?>
                <button type="submit" class="rounded-full p-1 text-ink-faint hover:bg-danger/10 hover:text-danger" aria-label="Delete"><?= icon('x', 'h-3.5 w-3.5') ?></button>
              </form>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <?php foreach ($rows as $r): $d = $spec['display']($r); ?>
          <article class="card card-pad">
            <div class="flex items-start gap-4">
              <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-500"><?= icon($spec['icon'], 'h-5 w-5') ?></span>
              <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-start justify-between gap-2">
                  <div class="min-w-0">
                    <h3 class="text-sm font-semibold text-ink"><?= e($d['title']) ?></h3>
                    <?php if (!empty($d['subtitle'])): ?><p class="text-sm text-ink-soft"><?= e($d['subtitle']) ?></p><?php endif; ?>
                  </div>
                  <div class="flex shrink-0 items-center gap-1">
                    <?php if (!empty($d['verified'])): ?><span class="badge-green mr-1"><?= icon('check', 'h-3 w-3') ?>Verified</span><?php endif; ?>
                    <a href="<?= url('/dashboard/' . $section, ['edit' => $r['id']]) ?>"
                       class="rounded p-1.5 text-ink-faint hover:bg-brand-50 hover:text-brand-700" aria-label="Edit"><?= icon('edit', 'h-4 w-4') ?></a>
                    <form method="post" action="<?= url('/dashboard/' . $section . '/' . $r['id'] . '/delete') ?>"
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

                <?php if (!empty($d['body'])): ?>
                  <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-ink-soft"><?= e(str_excerpt($d['body'], 400)) ?></p>
                <?php endif; ?>

                <?php if (!empty($d['file']) || !empty($d['link'])): ?>
                  <p class="mt-3 flex flex-wrap gap-3">
                    <?php if (!empty($d['file'])): ?>
                      <a href="<?= e(upload_url($d['file'])) ?>" target="_blank" rel="noopener"
                         class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-500 hover:text-brand-700">
                        <?= icon('document', 'h-3.5 w-3.5') ?>View document</a>
                    <?php endif; ?>
                    <?php if (!empty($d['link'])): ?>
                      <a href="<?= e($d['link']) ?>" target="_blank" rel="noopener noreferrer"
                         class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-500 hover:text-brand-700">
                        <?= icon('external', 'h-3.5 w-3.5') ?>Verify credential</a>
                    <?php endif; ?>
                  </p>
                <?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div x-show="!open">
      <?php partial('empty-state', [
        'icon' => $spec['icon'],
        'title' => 'No ' . $spec['title'] . ' yet',
        'message' => $spec['empty'],
        'action' => '<button type="button" @click="open = true" class="btn-primary btn-sm">' . icon('plus', 'h-4 w-4') . 'Add ' . e($spec['singular']) . '</button>',
      ]); ?>
    </div>
  <?php endif; ?>

  <!-- add / edit form -->
  <button type="button" x-show="!open" @click="open = true" class="btn-outline btn-block">
    <?= icon('plus', 'h-4 w-4') ?>Add <?= e($spec['singular']) ?>
  </button>

  <div x-show="open" x-cloak x-transition class="card">
    <div class="card-head">
      <h2 class="card-title"><?= $editing ? 'Edit' : 'Add' ?> <?= e($spec['singular']) ?></h2>
      <?php if ($editing): ?>
        <a href="<?= url('/dashboard/' . $section) ?>" class="btn-ghost btn-sm">Cancel edit</a>
      <?php else: ?>
        <button type="button" @click="open = false" class="btn-ghost btn-sm">Close</button>
      <?php endif; ?>
    </div>

    <form method="post" action="<?= url('/dashboard/' . $section . '/save') ?>" enctype="multipart/form-data" class="card-pad">
      <?= csrf_field() ?>
      <?php if ($editing): ?><input type="hidden" name="record_id" value="<?= (int) $editing['id'] ?>"><?php endif; ?>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <?php foreach ($spec['fields'] as $name => $f):
            $value = old($name, $editing[$name] ?? '');
            partial('field', ['name' => $name, 'f' => $f, 'value' => $value, 'idPrefix' => $section]);
        endforeach; ?>
      </div>

      <div class="mt-5 flex flex-wrap gap-2">
        <button type="submit" class="btn-primary"><?= icon('check', 'h-4 w-4') ?><?= $editing ? 'Save changes' : 'Add ' . e($spec['singular']) ?></button>
        <a href="<?= url('/dashboard/' . $section) ?>" class="btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>
