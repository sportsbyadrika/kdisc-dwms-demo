<?php /** @var array $messages */ ?>
<?php partial('dash-header', [
  'title' => 'Enquiries',
  'sub'   => 'Messages sent through the public contact form.',
  'actions' => '<span class="badge-gray">' . count($messages) . ' messages</span>',
]); ?>

<?php if (!$messages): ?>
  <?php partial('empty-state', ['icon' => 'inbox', 'title' => 'No enquiries', 'message' => 'Messages sent from the contact page will appear here.']); ?>
<?php else: ?>
  <div class="space-y-3">
    <?php foreach ($messages as $m): ?>
      <article class="card card-pad <?= $m['is_read'] ? '' : 'border-l-4 border-l-brand-500' ?>" x-data="{ open: <?= $m['is_read'] ? 'false' : 'true' ?> }">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="min-w-0">
            <h2 class="flex flex-wrap items-center gap-2 text-sm font-semibold text-ink">
              <?= e($m['subject'] ?: 'No subject') ?>
              <?php if (!$m['is_read']): ?><span class="badge-blue">New</span><?php endif; ?>
            </h2>
            <p class="text-xs text-ink-faint">
              <?= e($m['name']) ?> · <a href="mailto:<?= e($m['email']) ?>" class="link"><?= e($m['email']) ?></a>
              <?php if ($m['phone']): ?> · <?= e($m['phone']) ?><?php endif; ?>
              · <?= e(fdate($m['created_at'], 'd M Y, g:i a')) ?>
            </p>
          </div>
          <div class="flex shrink-0 gap-2">
            <button type="button" @click="open = !open" class="btn-ghost btn-sm" x-text="open ? 'Hide' : 'Read'">Read</button>
            <?php if (!$m['is_read']): ?>
              <form method="post" action="<?= url('/official/messages/' . $m['id'] . '/read') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn-outline btn-sm"><?= icon('check', 'h-3.5 w-3.5') ?>Mark read</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
        <div x-show="open" x-cloak x-transition class="mt-3 whitespace-pre-line rounded-card bg-canvas px-4 py-3 text-sm text-ink-soft"><?= e($m['message']) ?></div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
