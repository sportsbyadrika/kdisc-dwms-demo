<?php /** @var array $seekers @var string|null $q @var string|null $kyc */
$kycTone = ['verified' => 'badge-green', 'pending' => 'badge-amber', 'failed' => 'badge-red', 'not_started' => 'badge-gray']; ?>
<?php partial('dash-header', ['title' => 'Job seekers', 'sub' => 'The registry of citizens registered on the platform.']); ?>

<form method="get" action="<?= url('/official/seekers') ?>" class="card card-pad mb-4 grid grid-cols-1 gap-3 sm:grid-cols-[1fr,200px,auto]">
  <div class="relative">
    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-ink-faint"><?= icon('search', 'h-4 w-4') ?></span>
    <label class="sr-only" for="ms-q">Search</label>
    <input id="ms-q" name="q" type="search" value="<?= e($q) ?>" placeholder="Name, e-mail or mobile" class="field pl-9">
  </div>
  <div>
    <label class="sr-only" for="ms-kyc">e-KYC status</label>
    <select id="ms-kyc" name="kyc" class="field">
      <option value="">Any e-KYC status</option>
      <?php foreach (['verified' => 'Verified', 'pending' => 'Pending', 'not_started' => 'Not started', 'failed' => 'Failed'] as $k => $label): ?>
        <option value="<?= $k ?>" <?= $kyc === $k ? 'selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button type="submit" class="btn-primary">Search</button>
</form>

<?php if (!$seekers): ?>
  <?php partial('empty-state', ['icon' => 'users', 'title' => 'No job seekers found', 'message' => 'Try a different search term or filter.']); ?>
<?php else: ?>
  <div class="card">
    <div class="scroll-x">
      <table class="table">
        <thead><tr><th>Name</th><th>Contact</th><th>District</th><th class="text-right">Profile</th><th class="text-right">Applications</th><th>e-KYC</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($seekers as $s): ?>
            <tr>
              <td>
                <span class="flex items-center gap-2.5">
                  <span class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-100 text-[11px] font-bold text-brand-700">
                    <?php if ($s['photo']): ?><img src="<?= e(upload_url($s['photo'])) ?>" alt="" class="h-full w-full object-cover">
                    <?php else: ?><?= e(initials($s['name'])) ?><?php endif; ?>
                  </span>
                  <span class="min-w-0">
                    <span class="block font-medium text-ink"><?= e($s['name']) ?></span>
                    <span class="block truncate text-xs text-ink-faint"><?= e(str_excerpt($s['headline'], 45) ?: 'Registered ' . fdate($s['created_at'])) ?></span>
                  </span>
                </span>
              </td>
              <td class="text-sm text-ink-soft">
                <span class="block truncate"><?= e($s['email']) ?></span>
                <?php if ($s['mobile']): ?><span class="block text-xs text-ink-faint">+91 <?= e($s['mobile']) ?></span><?php endif; ?>
              </td>
              <td class="text-sm text-ink-soft"><?= e($s['district'] ?: '—') ?></td>
              <td class="text-right text-sm"><?= (int) $s['profile_score'] ?>%</td>
              <td class="text-right text-sm"><?= (int) $s['applications'] ?></td>
              <td><span class="<?= $kycTone[$s['kyc_status']] ?? 'badge-gray' ?>"><?= e(ucwords(str_replace('_', ' ', $s['kyc_status']))) ?></span></td>
              <td class="text-right"><a href="<?= url('/official/seekers/' . $s['id']) ?>" class="text-sm font-semibold text-brand-500 hover:text-brand-700">Open</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
