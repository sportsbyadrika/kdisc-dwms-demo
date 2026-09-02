<?php /** @var array $seeker @var array $summary */ ?>
<?php partial('dash-header', [
  'title' => 'Basic details',
  'sub'   => 'These details appear at the top of your profile when an employer opens your application.',
]); ?>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-[1.5fr,1fr]">
  <div class="card">
    <div class="card-head"><h2 class="card-title">Your details</h2></div>
    <form method="post" action="<?= url('/dashboard/profile') ?>" enctype="multipart/form-data" class="card-pad fieldset">
      <?= csrf_field() ?>

      <div x-data="filePicker('<?= e(upload_url($seeker['photo']) ?? '') ?>', 'image')" class="flex items-center gap-5">
        <span class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-line bg-brand-50 text-lg font-bold text-brand-600">
          <template x-if="preview"><img :src="preview" alt="" class="h-full w-full object-cover"></template>
          <template x-if="!preview"><span><?= e(initials($seeker['name'])) ?></span></template>
        </span>
        <div>
          <label class="label" for="pf-photo">Photograph</label>
          <input id="pf-photo" name="photo" type="file" accept="image/png,image/jpeg,image/webp" @change="pick($event)" x-ref="photo"
                 class="block w-full text-sm text-ink-soft file:mr-3 file:rounded-full file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
          <?php if ($m = error_for('photo')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p>
          <?php else: ?><p class="hint">Square photographs look best. Up to <?= (int) config('security.max_upload_mb', 5) ?> MB.</p><?php endif; ?>
        </div>
      </div>

      <div class="form-grid">
        <div>
          <label class="label" for="pf-name">Full name <span class="text-danger">*</span></label>
          <input id="pf-name" name="name" required class="field <?= error_for('name') ? 'field-error' : '' ?>" value="<?= e(old('name', $seeker['name'])) ?>">
          <?php if ($m = error_for('name')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="label" for="pf-mobile">Mobile number <span class="text-danger">*</span></label>
          <div class="flex">
            <span class="inline-flex items-center rounded-l border border-r-0 border-ink/30 bg-black/[0.03] px-3 text-sm text-ink-soft">+91</span>
            <input id="pf-mobile" name="mobile" inputmode="numeric" maxlength="10" required
                   class="field rounded-l-none <?= error_for('mobile') ? 'field-error' : '' ?>" value="<?= e(old('mobile', $seeker['mobile'])) ?>">
          </div>
          <?php if ($m = error_for('mobile')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p>
          <?php else: ?><p class="hint">Changing this clears the verified flag on your mobile number.</p><?php endif; ?>
        </div>
        <div class="sm:col-span-2">
          <label class="label" for="pf-headline">Headline</label>
          <input id="pf-headline" name="headline" maxlength="160" class="field" value="<?= e(old('headline', $seeker['headline'])) ?>"
                 placeholder="Computer Science graduate seeking a backend developer role">
          <p class="hint">One line describing what you are looking for. Shown under your name.</p>
        </div>
        <div>
          <label class="label" for="pf-gender">Gender</label>
          <select id="pf-gender" name="gender" class="field">
            <option value="">Prefer not to say</option>
            <?php foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $k => $v): ?>
              <option value="<?= $k ?>" <?= old('gender', $seeker['gender']) === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="label" for="pf-dob">Date of birth</label>
          <input id="pf-dob" name="dob" type="date" max="<?= date('Y-m-d', strtotime('-14 years')) ?>"
                 class="field <?= error_for('dob') ? 'field-error' : '' ?>" value="<?= e(old('dob', $seeker['dob'])) ?>">
          <?php if ($m = error_for('dob')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p>
          <?php else: ?><p class="hint">Used to check age criteria on vacancies.</p><?php endif; ?>
        </div>
        <div class="sm:col-span-2">
          <label class="label" for="pf-about">About you</label>
          <textarea id="pf-about" name="about" rows="5" class="field" placeholder="A short summary of your background, strengths and what you are looking for."><?= e(old('about', $seeker['about'])) ?></textarea>
          <p class="hint">Up to 2000 characters.</p>
        </div>
      </div>

      <button type="submit" class="btn-primary"><?= icon('check', 'h-4 w-4') ?>Save details</button>
    </form>
  </div>

  <div class="space-y-4">
    <div class="card card-pad">
      <h2 class="card-title">Account</h2>
      <dl class="mt-3 space-y-3 text-sm">
        <div class="flex items-start justify-between gap-3">
          <dt class="text-ink-faint">E-mail</dt>
          <dd class="text-right">
            <span class="block font-medium text-ink"><?= e($seeker['email']) ?></span>
            <?php if ($seeker['email_verified']): ?><span class="badge-green mt-1"><?= icon('check', 'h-3 w-3') ?>Verified</span>
            <?php else: ?><span class="badge-amber mt-1">Unverified</span><?php endif; ?>
          </dd>
        </div>
        <div class="flex items-center justify-between gap-3">
          <dt class="text-ink-faint">e-KYC</dt>
          <dd><a href="<?= url('/dashboard/kyc') ?>" class="link"><?= $seeker['kyc_status'] === 'verified' ? 'Verified' : 'Complete now' ?></a></dd>
        </div>
        <div class="flex items-center justify-between gap-3">
          <dt class="text-ink-faint">Registered</dt>
          <dd class="font-medium text-ink"><?= e(fdate($seeker['created_at'])) ?></dd>
        </div>
        <div class="flex items-center justify-between gap-3">
          <dt class="text-ink-faint">Password</dt>
          <dd><a href="<?= url('/dashboard/password') ?>" class="link">Change</a></dd>
        </div>
      </dl>
    </div>

    <div class="card card-pad">
      <h2 class="card-title">Profile checklist</h2>
      <ul class="mt-3 space-y-2">
        <?php foreach ($summary['items'] as $item): ?>
          <li>
            <a href="<?= url($item['path']) ?>" class="flex items-start gap-2 text-sm <?= $item['done'] ? 'text-ink-faint' : 'text-ink-soft hover:text-brand-700' ?>">
              <span class="mt-0.5 shrink-0 <?= $item['done'] ? 'text-success' : 'text-ink-faint' ?>"><?= icon($item['done'] ? 'check-circle' : 'plus', 'h-4 w-4') ?></span>
              <span class="<?= $item['done'] ? 'line-through' : '' ?>"><?= e($item['label']) ?></span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>
