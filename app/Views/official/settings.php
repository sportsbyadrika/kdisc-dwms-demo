<?php
/** @var array $settings */
$labels = [
  'site_title' => 'Site title', 'site_tagline' => 'Tagline',
  'contact_email' => 'Contact e-mail', 'contact_phone' => 'Contact phone', 'contact_address' => 'Postal address',
  'about_short' => 'Short description (footer and meta description)',
  'facebook_url' => 'Facebook URL', 'twitter_url' => 'X (Twitter) URL',
  'linkedin_url' => 'LinkedIn URL', 'youtube_url' => 'YouTube URL',
];
$long = ['contact_address', 'about_short'];
?>
<?php partial('dash-header', ['title' => 'Site settings', 'sub' => 'These values appear across the public site — in the footer, the contact page and page metadata.']); ?>

<form method="post" action="<?= url('/official/settings') ?>" class="card">
  <?= csrf_field() ?>
  <div class="card-pad grid grid-cols-1 gap-4 sm:grid-cols-2">
    <?php foreach ($settings as $s):
        $key   = $s['setting_key'];
        $label = $labels[$key] ?? ucwords(str_replace('_', ' ', $key));
        $isLong = in_array($key, $long, true); ?>
      <div class="<?= $isLong ? 'sm:col-span-2' : '' ?>">
        <label class="label" for="set-<?= e($key) ?>"><?= e($label) ?></label>
        <?php if ($isLong): ?>
          <textarea id="set-<?= e($key) ?>" name="<?= e($key) ?>" rows="3" class="field"><?= e($s['setting_value']) ?></textarea>
        <?php else: ?>
          <input id="set-<?= e($key) ?>" name="<?= e($key) ?>" class="field" value="<?= e($s['setting_value']) ?>">
        <?php endif; ?>
        <p class="hint font-mono"><?= e($key) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="border-t border-line px-5 py-4 sm:px-6">
    <button type="submit" class="btn-primary"><?= icon('check', 'h-4 w-4') ?>Save settings</button>
  </div>
</form>
