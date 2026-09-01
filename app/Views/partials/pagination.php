<?php
/** @var string $path @var array $filters @var array $result */
use App\Core\Search;

if ($result['pages'] <= 1) {
    return;
}
$page  = $result['page'];
$pages = $result['pages'];
$from  = max(1, $page - 2);
$to    = min($pages, $from + 4);
$from  = max(1, $to - 4);
?>
<nav class="mt-6 flex items-center justify-between gap-3" aria-label="Pagination">
  <p class="text-xs text-ink-faint">
    Showing <?= (($page - 1) * $result['perPage']) + 1 ?>–<?= min($page * $result['perPage'], $result['total']) ?>
    of <?= number_format($result['total']) ?>
  </p>
  <ul class="flex items-center gap-1">
    <li>
      <?php if ($page > 1): ?>
        <a href="<?= e(Search::pageUrl($path, $filters, $page - 1)) ?>" class="btn-ghost btn-sm !rounded" aria-label="Previous page"><?= icon('chevron-left', 'h-4 w-4') ?></a>
      <?php else: ?>
        <span class="btn-ghost btn-sm !rounded opacity-40"><?= icon('chevron-left', 'h-4 w-4') ?></span>
      <?php endif; ?>
    </li>
    <?php for ($i = $from; $i <= $to; $i++): ?>
      <li>
        <a href="<?= e(Search::pageUrl($path, $filters, $i)) ?>"
           class="flex h-8 min-w-8 items-center justify-center rounded px-2 text-sm font-semibold transition <?= $i === $page ? 'bg-brand-500 text-white' : 'text-ink-soft hover:bg-black/5' ?>"
           <?= $i === $page ? 'aria-current="page"' : '' ?>><?= $i ?></a>
      </li>
    <?php endfor; ?>
    <li>
      <?php if ($page < $pages): ?>
        <a href="<?= e(Search::pageUrl($path, $filters, $page + 1)) ?>" class="btn-ghost btn-sm !rounded" aria-label="Next page"><?= icon('chevron-right', 'h-4 w-4') ?></a>
      <?php else: ?>
        <span class="btn-ghost btn-sm !rounded opacity-40"><?= icon('chevron-right', 'h-4 w-4') ?></span>
      <?php endif; ?>
    </li>
  </ul>
</nav>
