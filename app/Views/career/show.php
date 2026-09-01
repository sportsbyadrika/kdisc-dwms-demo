<?php
/** @var array $service @var array|null $request @var array $similar */
use App\Core\Auth;
$s = $service;
?>
<?php partial('page-hero', ['heading' => $s['title'], 'sub' => $s['summary'] ?: '', 'crumbs' => ['Career Services' => '/career-services', $s['title'] => null]]); ?>

<section class="shell grid gap-6 py-6 lg:grid-cols-[1fr,340px]">
  <div class="min-w-0 space-y-4">
    <div class="card card-pad">
      <div class="flex flex-wrap items-start gap-4">
        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-card bg-warning/10 text-warning"><?= icon($s['icon'] ?: 'compass', 'h-7 w-7') ?></span>
        <div class="min-w-0 flex-1">
          <?php if ($s['category_name']): ?><p class="text-[11px] font-bold uppercase tracking-wider text-brand-500"><?= e($s['category_name']) ?></p><?php endif; ?>
          <h1 class="text-xl font-bold text-ink sm:text-2xl"><?= e($s['title']) ?></h1>
          <?php if ($s['provider']): ?><p class="mt-1 text-sm text-ink-soft">Provided by <?= e($s['provider']) ?></p><?php endif; ?>
          <div class="mt-3 flex flex-wrap gap-1.5">
            <?php if ($s['is_free']): ?><span class="badge-green">No fee</span><?php else: ?><span class="badge-gray"><?= e(money((float) $s['fee'])) ?></span><?php endif; ?>
            <span class="chip"><?= e(ucfirst($s['service_mode'])) ?></span>
            <?php if ($s['district']): ?><span class="chip"><?= e($s['district']) ?></span><?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <?php if (trim((string) $s['description'])): ?>
      <div class="card card-pad">
        <h2 class="flex items-center gap-2 card-title"><?= icon('document', 'h-4 w-4 text-brand-500') ?>What this service covers</h2>
        <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-ink-soft"><?= e($s['description']) ?></p>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-head"><h2 class="card-title">Service details</h2></div>
      <dl class="grid grid-cols-1 gap-px bg-line sm:grid-cols-2">
        <?php foreach (array_filter([
          ['Delivery', ucfirst($s['service_mode']), 'globe'],
          ['Who it is for', $s['audience'], 'users'],
          ['Fee', $s['is_free'] ? 'No fee' : money((float) $s['fee']), 'wallet'],
          ['Schedule', $s['schedule_note'], 'calendar'],
          ['Venue', $s['venue'], 'map-pin'],
          ['District', $s['district'], 'map-pin'],
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
  </div>

  <aside class="space-y-4 lg:sticky lg:top-20 lg:self-start">
    <div class="card card-pad">
      <?php if ($request): ?>
        <p class="flex items-center gap-2 text-sm font-semibold text-success"><?= icon('check-circle', 'h-5 w-5') ?>Request received</p>
        <p class="mt-1 text-sm text-ink-soft">Requested on <?= e(fdate($request['created_at'])) ?>. Status:
          <span class="badge-blue"><?= e(ucfirst($request['status'])) ?></span>
        </p>
      <?php else: ?>
        <p class="text-sm font-semibold text-ink">Request this service</p>
        <p class="mt-1 text-sm text-ink-soft">Tell us briefly what you need and the desk will contact you on your registered mobile number.</p>
        <form method="post" action="<?= url('/career-services/' . $s['id'] . '/request') ?>" class="mt-4 space-y-3">
          <?= csrf_field() ?>
          <div>
            <label class="label" for="note">What would you like help with?</label>
            <textarea id="note" name="note" rows="3" maxlength="500" class="field" placeholder="Optional"></textarea>
          </div>
          <button type="submit" class="btn-primary btn-block btn-lg"><?= icon('send', 'h-4 w-4') ?>Request service</button>
        </form>
        <?php if (!Auth::check('seeker')): ?>
          <p class="mt-2 text-center text-xs text-ink-faint">You will be asked to sign in first.</p>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($s['booking_url']): ?>
        <a href="<?= e($s['booking_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn-outline btn-block mt-2">
          <?= icon('external', 'h-4 w-4') ?>Book a slot directly
        </a>
      <?php endif; ?>
    </div>

    <?php if ($s['contact_email'] || $s['contact_phone']): ?>
      <div class="card card-pad">
        <h2 class="card-title">Service desk</h2>
        <ul class="mt-3 space-y-2 text-sm text-ink-soft">
          <?php if ($s['contact_email']): ?>
            <li class="flex items-center gap-2"><span class="text-brand-500"><?= icon('mail', 'h-4 w-4') ?></span>
              <a href="mailto:<?= e($s['contact_email']) ?>" class="link truncate"><?= e($s['contact_email']) ?></a></li>
          <?php endif; ?>
          <?php if ($s['contact_phone']): ?>
            <li class="flex items-center gap-2"><span class="text-brand-500"><?= icon('phone', 'h-4 w-4') ?></span><?= e($s['contact_phone']) ?></li>
          <?php endif; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($similar): ?>
      <div class="card">
        <div class="card-head"><h2 class="card-title">Other services</h2></div>
        <ul class="divide-y divide-line">
          <?php foreach ($similar as $o): ?>
            <li class="flex items-start gap-3 px-5 py-3">
              <span class="mt-0.5 shrink-0 text-warning"><?= icon($o['icon'] ?: 'compass', 'h-4 w-4') ?></span>
              <span class="min-w-0">
                <a href="<?= url('/career-services/' . $o['id']) ?>" class="block text-sm font-medium text-ink hover:text-brand-700"><?= e($o['title']) ?></a>
                <span class="block truncate text-xs text-ink-faint"><?= e(str_excerpt($o['summary'], 60)) ?></span>
              </span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
  </aside>
</section>
