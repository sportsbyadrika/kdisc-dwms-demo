<?php partial('page-hero', [
  'heading' => 'About DWMS 2.0',
  'sub'     => 'A single, verified workforce platform connecting job seekers, employers, skilling institutions and government departments.',
  'crumbs'  => ['About' => null],
]); ?>

<section class="shell grid grid-cols-1 gap-8 py-10 lg:grid-cols-3">
  <div class="lg:col-span-2 space-y-6">
    <div class="card card-pad">
      <h2 class="card-title">What DWMS 2.0 does</h2>
      <div class="prose-dwms mt-3">
        <p>The Digital Workforce Management System (DWMS) 2.0 is the second generation of the state's employment platform. It replaces disconnected registers and spreadsheets with one record per job seeker and one verified profile per employer.</p>
        <p>A job seeker registers once — verifying their e-mail, completing e-KYC and building a structured profile covering qualifications, experience, certifications and achievements. That single profile then travels with them across every vacancy, skilling programme and career service on the platform.</p>
        <p>An employer registers their organisation, submits statutory details for verification, and publishes job titles as curation sheets: a structured description of the role, its eligibility criteria and its selection process. Applications land in one dashboard where they can be screened, shortlisted and closed out.</p>
      </div>
    </div>

    <div class="card card-pad">
      <h2 class="card-title">Principles we build on</h2>
      <ul class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <?php foreach ([
          ['shield-check', 'Verified once, trusted everywhere', 'e-KYC and document verification happen once and are reused across every application.'],
          ['id-card', 'Consent-first data sharing', 'Aadhaar and other identity details are shared with a government department only after explicit, recorded consent.'],
          ['target', 'Structured, comparable jobs', 'Every vacancy is captured as a curation sheet so candidates can compare roles on the same terms.'],
          ['users', 'One record per citizen', 'No duplicate registrations, no re-keying the same data at every office.'],
        ] as [$ic, $t, $d]): ?>
          <li class="flex gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-500"><?= icon($ic, 'h-4 w-4') ?></span>
            <span>
              <span class="block text-sm font-semibold text-ink"><?= e($t) ?></span>
              <span class="block text-sm text-ink-soft"><?= e($d) ?></span>
            </span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="card card-pad">
      <h2 class="card-title">Who uses the platform</h2>
      <div class="mt-4 divide-y divide-line">
        <?php foreach ([
          ['Job seekers', 'Register, complete e-KYC, build a profile, search and apply for vacancies, enrol in skilling programmes and book career services.'],
          ['Employers', 'Register the organisation, publish curated job titles, screen applicants and manage the hiring pipeline.'],
          ['Departmental officials', 'Verify employers and documents, publish skilling programmes and career services, and manage offices, users and roles.'],
        ] as [$who, $what]): ?>
          <div class="flex flex-col gap-1 py-3 sm:flex-row sm:gap-6">
            <p class="w-48 shrink-0 text-sm font-semibold text-ink"><?= e($who) ?></p>
            <p class="text-sm text-ink-soft"><?= e($what) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <aside class="space-y-5">
    <div class="card card-pad">
      <h2 class="card-title">Get started</h2>
      <div class="mt-4 space-y-2">
        <a href="<?= url('/register') ?>" class="btn-primary btn-block">Register as job seeker</a>
        <a href="<?= url('/employer/register') ?>" class="btn-outline btn-block">Register as employer</a>
        <a href="<?= url('/contact') ?>" class="btn-ghost btn-block">Talk to us</a>
      </div>
    </div>
    <div class="card card-pad">
      <h2 class="card-title">Reach us</h2>
      <ul class="mt-3 space-y-3 text-sm text-ink-soft">
        <li class="flex items-start gap-2"><span class="mt-0.5 text-brand-500"><?= icon('map-pin', 'h-4 w-4') ?></span><?= e(setting('contact_address', '')) ?></li>
        <li class="flex items-center gap-2"><span class="text-brand-500"><?= icon('phone', 'h-4 w-4') ?></span><?= e(setting('contact_phone', '')) ?></li>
        <li class="flex items-center gap-2"><span class="text-brand-500"><?= icon('mail', 'h-4 w-4') ?></span><?= e(setting('contact_email', '')) ?></li>
      </ul>
    </div>
  </aside>
</section>
