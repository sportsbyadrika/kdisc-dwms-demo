<?php
/** @var array $programme @var array|null $enrolment @var array $similar */
use App\Core\Auth;
$p = $programme;
$duration = $p['duration_value'] ? $p['duration_value'] . ' ' . $p['duration_unit'] : null;
?>
<?php partial('page-hero', ['heading' => $p['title'], 'sub' => '', 'crumbs' => ['Skills' => '/skills', $p['title'] => null]]); ?>

<section class="shell grid gap-6 py-6 lg:grid-cols-[1fr,340px]">
  <div class="min-w-0 space-y-4">
    <div class="card card-pad">
      <div class="flex flex-wrap items-start gap-4">
        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-card bg-success/10 text-success"><?= icon('graduation', 'h-7 w-7') ?></span>
        <div class="min-w-0 flex-1">
          <?php if ($p['category_name']): ?><p class="text-[11px] font-bold uppercase tracking-wider text-brand-500"><?= e($p['category_name']) ?></p><?php endif; ?>
          <h1 class="text-xl font-bold text-ink sm:text-2xl"><?= e($p['title']) ?></h1>
          <p class="mt-1 text-sm text-ink-soft"><?= e($p['provider']) ?></p>
          <div class="mt-3 flex flex-wrap gap-1.5">
            <?php if ($p['is_free']): ?><span class="badge-green">No fee</span><?php else: ?><span class="badge-gray"><?= e(money((float) $p['fee'])) ?></span><?php endif; ?>
            <?php if ($p['is_certified']): ?><span class="badge-blue"><?= icon('shield-check', 'h-3 w-3') ?>Certificate awarded</span><?php endif; ?>
            <span class="chip"><?= e(ucfirst($p['level'])) ?></span>
            <span class="chip"><?= e(ucfirst($p['mode'])) ?></span>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-head"><h2 class="card-title">Programme details</h2></div>
      <dl class="grid grid-cols-1 gap-px bg-line sm:grid-cols-2">
        <?php foreach (array_filter([
          ['Duration', $duration, 'clock'],
          ['Mode', ucfirst($p['mode']), 'globe'],
          ['Level', ucfirst($p['level']), 'chart'],
          ['Fee', $p['is_free'] ? 'No fee' : money((float) $p['fee']), 'wallet'],
          ['Seats', $p['seats'] ? (int) $p['seats'] : null, 'users'],
          ['Starts', $p['start_date'] ? fdate($p['start_date']) : 'Rolling intake', 'calendar'],
          ['Venue', $p['venue'], 'map-pin'],
          ['District', $p['district'], 'map-pin'],
        ], static fn($r) => $r[1] !== null && $r[1] !== '') as [$label, $value, $ic]): ?>
          <div class="flex items-start gap-3 bg-white px-5 py-3.5">
            <span class="mt-0.5 shrink-0 text-brand-500"><?= icon($ic, 'h-4 w-4') ?></span>
            <div class="min-w-0">
              <dt class="text-xs font-medium uppercase tracking-wide text-ink-faint"><?= e($label) ?></dt>
              <dd class="text-sm font-medium text-ink"><?= e($value) ?></dd>
            </div>
          </div>
        <?php endforeach; ?>
      </dl>
    </div>

    <?php foreach ([
      ['About this programme', $p['description'], 'document'],
      ['What you will be able to do', $p['outcomes'], 'target'],
      ['Who can apply', $p['eligibility'], 'users'],
    ] as [$heading, $body, $ic]): if (!trim((string) $body)) { continue; } ?>
      <div class="card card-pad">
        <h2 class="flex items-center gap-2 card-title"><?= icon($ic, 'h-4 w-4 text-brand-500') ?><?= e($heading) ?></h2>
        <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-ink-soft"><?= e($body) ?></p>
      </div>
    <?php endforeach; ?>
  </div>

  <aside class="space-y-4 lg:sticky lg:top-20 lg:self-start">
    <div class="card card-pad">
      <?php if ($enrolment): ?>
        <p class="flex items-center gap-2 text-sm font-semibold text-success"><?= icon('check-circle', 'h-5 w-5') ?>Interest recorded</p>
        <p class="mt-1 text-sm text-ink-soft">Registered on <?= e(fdate($enrolment['created_at'])) ?>. The training provider will contact you with the next steps.</p>
      <?php else: ?>
        <p class="text-sm font-semibold text-ink">Interested in this programme?</p>
        <p class="mt-1 text-sm text-ink-soft">Register your interest and the training provider will get in touch with the joining process.</p>
        <form method="post" action="<?= url('/skills/' . $p['id'] . '/enrol') ?>" class="mt-4">
          <?= csrf_field() ?>
          <button type="submit" class="btn-primary btn-block btn-lg"><?= icon('check', 'h-4 w-4') ?>Register interest</button>
        </form>
        <?php if (!Auth::check('seeker')): ?>
          <p class="mt-2 text-center text-xs text-ink-faint">You will be asked to sign in first.</p>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($p['apply_url']): ?>
        <a href="<?= e($p['apply_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn-outline btn-block mt-2">
          <?= icon('external', 'h-4 w-4') ?>Provider's application page
        </a>
      <?php endif; ?>
    </div>

    <?php if ($p['contact_email'] || $p['contact_phone']): ?>
      <div class="card card-pad">
        <h2 class="card-title">Contact the provider</h2>
        <ul class="mt-3 space-y-2 text-sm text-ink-soft">
          <?php if ($p['contact_email']): ?>
            <li class="flex items-center gap-2"><span class="text-brand-500"><?= icon('mail', 'h-4 w-4') ?></span>
              <a href="mailto:<?= e($p['contact_email']) ?>" class="link truncate"><?= e($p['contact_email']) ?></a></li>
          <?php endif; ?>
          <?php if ($p['contact_phone']): ?>
            <li class="flex items-center gap-2"><span class="text-brand-500"><?= icon('phone', 'h-4 w-4') ?></span><?= e($p['contact_phone']) ?></li>
          <?php endif; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($similar): ?>
      <div class="card">
        <div class="card-head"><h2 class="card-title">Related programmes</h2></div>
        <ul class="divide-y divide-line">
          <?php foreach ($similar as $s): ?>
            <li class="px-5 py-3">
              <a href="<?= url('/skills/' . $s['id']) ?>" class="block text-sm font-medium text-ink hover:text-brand-700"><?= e($s['title']) ?></a>
              <p class="truncate text-xs text-ink-faint"><?= e($s['provider']) ?></p>
              <p class="text-xs text-ink-soft"><?= $s['is_free'] ? 'No fee' : e(money((float) $s['fee'])) ?> · <?= e(ucfirst($s['mode'])) ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
  </aside>
</section>
