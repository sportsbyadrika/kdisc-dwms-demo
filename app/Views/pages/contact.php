<?php partial('page-hero', [
  'heading' => 'Contact us',
  'sub'     => 'Questions about registration, a vacancy, a skilling programme or a grievance — write to us and we will respond within two working days.',
  'crumbs'  => ['Contact' => null],
]); ?>

<section class="shell grid gap-6 py-10 lg:grid-cols-3">
  <div class="lg:col-span-2 card">
    <div class="card-head"><h2 class="card-title">Send us a message</h2></div>
    <form method="post" action="<?= url('/contact') ?>" class="card-pad fieldset">
      <?= csrf_field() ?>
      <!-- honeypot -->
      <div class="hidden" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

      <div class="form-grid">
        <div>
          <label class="label" for="c-name">Full name <span class="text-danger">*</span></label>
          <input id="c-name" name="name" class="field <?= error_for('name') ? 'field-error' : '' ?>" value="<?= e(old('name')) ?>" required>
          <?php if ($m = error_for('name')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="label" for="c-email">E-mail <span class="text-danger">*</span></label>
          <input id="c-email" name="email" type="email" class="field <?= error_for('email') ? 'field-error' : '' ?>" value="<?= e(old('email')) ?>" required>
          <?php if ($m = error_for('email')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="label" for="c-phone">Mobile number</label>
          <input id="c-phone" name="phone" inputmode="numeric" maxlength="10" class="field <?= error_for('phone') ? 'field-error' : '' ?>" value="<?= e(old('phone')) ?>">
          <?php if ($m = error_for('phone')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="label" for="c-subject">Subject</label>
          <input id="c-subject" name="subject" class="field" value="<?= e(old('subject')) ?>" placeholder="Registration, vacancy, grievance…">
        </div>
      </div>
      <div>
        <label class="label" for="c-message">Message <span class="text-danger">*</span></label>
        <textarea id="c-message" name="message" rows="6" class="field <?= error_for('message') ? 'field-error' : '' ?>" required><?= e(old('message')) ?></textarea>
        <?php if ($m = error_for('message')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        <p class="hint">Please do not include Aadhaar numbers, passwords or bank details in this form.</p>
      </div>
      <button type="submit" class="btn-primary btn-lg"><?= icon('send', 'h-4 w-4') ?>Send message</button>
    </form>
  </div>

  <aside class="space-y-5">
    <div class="card card-pad">
      <h2 class="card-title">Head office</h2>
      <ul class="mt-3 space-y-3 text-sm text-ink-soft">
        <li class="flex items-start gap-2"><span class="mt-0.5 text-brand-500"><?= icon('map-pin', 'h-4 w-4') ?></span><?= e(setting('contact_address', '')) ?></li>
        <li class="flex items-center gap-2"><span class="text-brand-500"><?= icon('phone', 'h-4 w-4') ?></span>
          <a class="link" href="tel:<?= e(preg_replace('/\s+/', '', setting('contact_phone', ''))) ?>"><?= e(setting('contact_phone', '')) ?></a></li>
        <li class="flex items-center gap-2"><span class="text-brand-500"><?= icon('mail', 'h-4 w-4') ?></span>
          <a class="link" href="mailto:<?= e(setting('contact_email', '')) ?>"><?= e(setting('contact_email', '')) ?></a></li>
        <li class="flex items-start gap-2"><span class="mt-0.5 text-brand-500"><?= icon('clock', 'h-4 w-4') ?></span>Monday to Friday, 10:00 – 17:00 (excluding public holidays)</li>
      </ul>
    </div>
    <div class="card card-pad">
      <h2 class="card-title">Before you write</h2>
      <ul class="mt-3 space-y-2 text-sm text-ink-soft">
        <li class="flex gap-2"><span class="text-brand-500"><?= icon('check', 'h-4 w-4') ?></span>Most registration questions are answered in the <a href="<?= url('/faq') ?>" class="link">FAQ</a>.</li>
        <li class="flex gap-2"><span class="text-brand-500"><?= icon('check', 'h-4 w-4') ?></span>For a specific vacancy, quote the job code shown on the job page.</li>
        <li class="flex gap-2"><span class="text-brand-500"><?= icon('check', 'h-4 w-4') ?></span>Employers can reach the verification desk from their dashboard.</li>
      </ul>
    </div>
  </aside>
</section>
