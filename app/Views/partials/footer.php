<?php
$year  = date('Y');
$cols  = [
    'Job seekers' => [
        ['Search jobs', '/jobs'],
        ['Skilling programmes', '/skills'],
        ['Career services', '/career-services'],
        ['Register as job seeker', '/register'],
        ['Job seeker login', '/login'],
    ],
    'Employers' => [
        ['Why DWMS', '/employers'],
        ['Register your organisation', '/employer/register'],
        ['Employer login', '/employer/login'],
        ['Post a job title', '/employer/jobs/create'],
    ],
    'About' => [
        ['About DWMS 2.0', '/about'],
        ['Contact us', '/contact'],
        ['Frequently asked questions', '/faq'],
        ['Officials login', '/official/login'],
    ],
    'Legal' => [
        ['Privacy policy', '/privacy'],
        ['Terms of use', '/terms'],
        ['Accessibility statement', '/accessibility'],
        ['Grievance redressal', '/contact'],
    ],
];
$social = [
    ['linkedin', setting('linkedin_url', '#'), 'LinkedIn'],
    ['facebook', setting('facebook_url', '#'), 'Facebook'],
    ['twitter',  setting('twitter_url', '#'),  'X'],
    ['youtube',  setting('youtube_url', '#'),  'YouTube'],
];
?>
<footer class="mt-16 border-t border-line bg-white">
  <div class="shell grid gap-8 py-12 sm:grid-cols-2 lg:grid-cols-6">

    <div class="lg:col-span-2">
      <div class="flex items-center gap-2">
        <span class="flex h-9 w-9 items-center justify-center rounded bg-brand-500 text-white"><?= icon('layers', 'h-5 w-5') ?></span>
        <span class="text-[15px] font-bold text-brand-700"><?= e(setting('site_title', 'DWMS 2.0')) ?></span>
      </div>
      <p class="mt-3 max-w-sm text-sm leading-relaxed text-ink-soft">
        <?= e(setting('about_short', 'DWMS 2.0 connects job seekers, employers and government departments on a single verified workforce platform.')) ?>
      </p>
      <ul class="mt-4 space-y-2 text-sm text-ink-soft">
        <li class="flex items-start gap-2"><span class="mt-0.5 text-brand-500"><?= icon('map-pin', 'h-4 w-4') ?></span><?= e(setting('contact_address', '')) ?></li>
        <li class="flex items-center gap-2"><span class="text-brand-500"><?= icon('phone', 'h-4 w-4') ?></span>
          <a class="link" href="tel:<?= e(preg_replace('/\s+/', '', setting('contact_phone', ''))) ?>"><?= e(setting('contact_phone', '')) ?></a></li>
        <li class="flex items-center gap-2"><span class="text-brand-500"><?= icon('mail', 'h-4 w-4') ?></span>
          <a class="link" href="mailto:<?= e(setting('contact_email', '')) ?>"><?= e(setting('contact_email', '')) ?></a></li>
      </ul>
      <div class="mt-5 flex items-center gap-2">
        <?php foreach ($social as [$ic, $href, $label]): ?>
          <a href="<?= e($href) ?>" aria-label="<?= e($label) ?>" rel="noopener noreferrer" target="_blank"
             class="flex h-9 w-9 items-center justify-center rounded-full border border-line text-ink-soft transition hover:border-brand-500 hover:bg-brand-50 hover:text-brand-700">
            <?= icon($ic, 'h-4 w-4') ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <?php foreach ($cols as $heading => $links): ?>
      <div>
        <h3 class="text-xs font-bold uppercase tracking-wider text-ink"><?= e($heading) ?></h3>
        <ul class="mt-3 space-y-2">
          <?php foreach ($links as [$label, $path]): ?>
            <li><a href="<?= url($path) ?>" class="text-sm text-ink-soft transition hover:text-brand-700 hover:underline"><?= e($label) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="border-t border-line bg-canvas">
    <div class="shell flex flex-col items-center justify-between gap-3 py-5 text-xs text-ink-faint sm:flex-row">
      <p>&copy; <?= $year ?> <?= e(config('app.org', 'K-DISC')) ?>. All rights reserved.</p>
      <p class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1">
        <a href="<?= url('/privacy') ?>" class="hover:text-brand-700 hover:underline">Privacy</a>
        <a href="<?= url('/terms') ?>" class="hover:text-brand-700 hover:underline">Terms</a>
        <a href="<?= url('/accessibility') ?>" class="hover:text-brand-700 hover:underline">Accessibility</a>
        <a href="<?= url('/sitemap') ?>" class="hover:text-brand-700 hover:underline">Sitemap</a>
      </p>
    </div>
  </div>
</footer>
