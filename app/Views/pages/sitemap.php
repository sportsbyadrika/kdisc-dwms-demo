<?php
$map = [
  'Public' => [
    ['Home', '/'], ['Search jobs', '/jobs'], ['Skilling programmes', '/skills'],
    ['Career services', '/career-services'], ['For employers', '/employers'],
    ['About', '/about'], ['Contact', '/contact'], ['FAQ', '/faq'],
  ],
  'Job seeker' => [
    ['Register', '/register'], ['Login', '/login'], ['Dashboard', '/dashboard'],
    ['My profile', '/dashboard/profile'], ['e-KYC', '/dashboard/kyc'],
    ['Resume', '/dashboard/resume'], ['Qualifications', '/dashboard/qualifications'],
    ['Experience', '/dashboard/experience'], ['Applications', '/dashboard/applications'],
    ['Saved jobs', '/dashboard/saved'],
  ],
  'Employer' => [
    ['Register', '/employer/register'], ['Login', '/employer/login'],
    ['Dashboard', '/employer/dashboard'], ['Company profile', '/employer/profile'],
    ['Job titles', '/employer/jobs'], ['Publish a job title', '/employer/jobs/create'],
  ],
  'Officials' => [
    ['Login', '/official/login'], ['Dashboard', '/official/dashboard'],
    ['Offices', '/official/offices'], ['Users', '/official/users'], ['Roles', '/official/roles'],
    ['Hero panel', '/official/hero'], ['Skilling programmes', '/official/skills'],
    ['Career services', '/official/careers'], ['Employer verification', '/official/employers'],
  ],
  'Legal' => [
    ['Privacy policy', '/privacy'], ['Terms of use', '/terms'], ['Accessibility', '/accessibility'],
  ],
];
?>
<?php partial('page-hero', ['heading' => 'Sitemap', 'sub' => 'Every page on DWMS 2.0, in one list.', 'crumbs' => ['Sitemap' => null]]); ?>
<section class="shell grid grid-cols-1 gap-5 py-10 sm:grid-cols-2 lg:grid-cols-3">
  <?php foreach ($map as $group => $links): ?>
    <div class="card card-pad">
      <h2 class="card-title"><?= e($group) ?></h2>
      <ul class="mt-3 space-y-2">
        <?php foreach ($links as [$label, $path]): ?>
          <li><a href="<?= url($path) ?>" class="flex items-center gap-1.5 text-sm text-ink-soft hover:text-brand-700">
            <?= icon('chevron-right', 'h-3 w-3 text-brand-400') ?><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endforeach; ?>
</section>
