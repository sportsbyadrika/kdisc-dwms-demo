<?php
/** @var array $steps  (n => [label, icon]) @var int $step @var string|null $linkBase */
?>
<ol class="flex items-center gap-2" aria-label="Progress">
  <?php $last = count($steps); foreach ($steps as $n => [$label, $ic]): ?>
    <li class="step">
      <?php $classes = $n < $step ? 'step-done' : ($n === $step ? 'step-now' : 'step-todo'); ?>
      <?php if (!empty($linkBase) && $n < $step): ?>
        <a href="<?= url($linkBase, ['step' => $n]) ?>" class="<?= $classes ?>" aria-label="Back to step <?= $n ?>"><?= icon('check', 'h-4 w-4') ?></a>
      <?php else: ?>
        <span class="<?= $classes ?>"><?= $n < $step ? icon('check', 'h-4 w-4') : $n ?></span>
      <?php endif; ?>
      <span class="hidden text-xs font-semibold sm:block <?= $n === $step ? 'text-ink' : 'text-ink-faint' ?>"><?= e($label) ?></span>
      <?php if ($n < $last): ?>
        <span class="h-0.5 flex-1 rounded-full <?= $n < $step ? 'bg-success' : 'bg-line' ?>"></span>
      <?php endif; ?>
    </li>
  <?php endforeach; ?>
</ol>
