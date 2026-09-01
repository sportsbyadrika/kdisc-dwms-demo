<?php
/** @var array $addresses @var array $districts */
$blocks = [
  'communication' => ['c_', 'Address of communication', 'Where post and notices should reach you today.', true],
  'permanent'     => ['p_', 'Permanent address', 'Your home address as recorded on official documents.', false],
];
$same = $addresses['communication'] && $addresses['permanent']
     && $addresses['communication']['line1'] === $addresses['permanent']['line1']
     && $addresses['communication']['pincode'] === $addresses['permanent']['pincode'];
?>
<?php partial('dash-header', [
  'title' => 'Addresses',
  'sub'   => 'Employers and departments use these to check locality-based eligibility.',
]); ?>

<form method="post" action="<?= url('/dashboard/address') ?>" x-data="{ same: <?= $same ? 'true' : 'false' ?> }" class="space-y-4">
  <?= csrf_field() ?>

  <?php foreach ($blocks as $type => [$p, $heading, $sub, $required]):
      $a = $addresses[$type] ?? null; ?>
    <div class="card" <?= $type === 'permanent' ? 'x-show="!same" x-cloak' : '' ?>>
      <div class="card-head">
        <div>
          <h2 class="card-title"><?= e($heading) ?><?= $required ? ' <span class="text-danger">*</span>' : '' ?></h2>
          <p class="text-xs text-ink-faint"><?= e($sub) ?></p>
        </div>
        <?php if ($a): ?><span class="badge-green"><?= icon('check', 'h-3 w-3') ?>Saved</span><?php endif; ?>
      </div>

      <div class="card-pad grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
          <label class="label" for="<?= $p ?>line1">Address line 1 <?= $required ? '<span class="text-danger">*</span>' : '' ?></label>
          <input id="<?= $p ?>line1" name="<?= $p ?>line1" class="field <?= error_for($p . 'line1') ? 'field-error' : '' ?>"
                 value="<?= e(old($p . 'line1', $a['line1'] ?? '')) ?>" placeholder="House name / number, street">
          <?php if ($m = error_for($p . 'line1')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>
        <div class="sm:col-span-2">
          <label class="label" for="<?= $p ?>line2">Address line 2</label>
          <input id="<?= $p ?>line2" name="<?= $p ?>line2" class="field" value="<?= e(old($p . 'line2', $a['line2'] ?? '')) ?>" placeholder="Locality, post office">
        </div>
        <div>
          <label class="label" for="<?= $p ?>landmark">Landmark</label>
          <input id="<?= $p ?>landmark" name="<?= $p ?>landmark" class="field" value="<?= e(old($p . 'landmark', $a['landmark'] ?? '')) ?>">
        </div>
        <div>
          <label class="label" for="<?= $p ?>city">City / town / village</label>
          <input id="<?= $p ?>city" name="<?= $p ?>city" class="field" value="<?= e(old($p . 'city', $a['city'] ?? '')) ?>">
        </div>
        <div>
          <label class="label" for="<?= $p ?>district">District <?= $required ? '<span class="text-danger">*</span>' : '' ?></label>
          <select id="<?= $p ?>district" name="<?= $p ?>district" class="field <?= error_for($p . 'district') ? 'field-error' : '' ?>">
            <option value="">Select district</option>
            <?php foreach ($districts as $d): ?>
              <option value="<?= e($d) ?>" <?= old($p . 'district', $a['district'] ?? '') === $d ? 'selected' : '' ?>><?= e($d) ?></option>
            <?php endforeach; ?>
            <option value="Other" <?= old($p . 'district', $a['district'] ?? '') === 'Other' ? 'selected' : '' ?>>Outside Kerala</option>
          </select>
          <?php if ($m = error_for($p . 'district')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="label" for="<?= $p ?>pincode">PIN code <?= $required ? '<span class="text-danger">*</span>' : '' ?></label>
          <input id="<?= $p ?>pincode" name="<?= $p ?>pincode" inputmode="numeric" maxlength="6"
                 class="field <?= error_for($p . 'pincode') ? 'field-error' : '' ?>" value="<?= e(old($p . 'pincode', $a['pincode'] ?? '')) ?>">
          <?php if ($m = error_for($p . 'pincode')): ?><p class="err"><?= icon('alert', 'h-3.5 w-3.5') ?><?= e($m) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="label" for="<?= $p ?>state">State</label>
          <input id="<?= $p ?>state" name="<?= $p ?>state" class="field" value="<?= e(old($p . 'state', $a['state'] ?? 'Kerala')) ?>">
        </div>
        <div>
          <label class="label" for="<?= $p ?>country">Country</label>
          <input id="<?= $p ?>country" name="<?= $p ?>country" class="field" value="<?= e(old($p . 'country', $a['country'] ?? 'India')) ?>">
        </div>
      </div>

      <?php if ($type === 'communication'): ?>
        <div class="border-t border-line px-5 py-4 sm:px-6">
          <label class="flex items-center gap-2.5 text-sm text-ink-soft">
            <input type="checkbox" name="same_as_communication" value="1" class="checkbox" x-model="same">
            <span>My permanent address is the same as this address</span>
          </label>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <div class="card card-pad">
    <button type="submit" class="btn-primary"><?= icon('check', 'h-4 w-4') ?>Save addresses</button>
    <p class="mt-2 text-xs text-ink-faint">The permanent address is optional — leave it blank if you would rather not provide one.</p>
  </div>
</form>
