<?php partial('page-hero', [
  'heading' => 'Hire from a verified talent pool',
  'sub'     => 'Publish curated job titles, reach candidates whose identity and qualifications are already verified, and manage every application in one dashboard.',
  'crumbs'  => ['For employers' => null],
]); ?>

<section class="shell py-10">
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
    <?php foreach ([
      [$stats['seekers'], 'Registered job seekers', 'users'],
      [$stats['jobs'], 'Job titles published', 'briefcase'],
      [$stats['apps'], 'Applications processed', 'inbox'],
    ] as [$n, $label, $ic]): ?>
      <div class="card card-pad flex items-center gap-4">
        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-50 text-brand-500"><?= icon($ic) ?></span>
        <div>
          <p class="text-2xl font-bold text-ink"><?= number_format((int) $n) ?></p>
          <p class="text-xs font-medium uppercase tracking-wide text-ink-faint"><?= e($label) ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="mt-10 grid grid-cols-1 gap-8 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-6">
      <div class="card card-pad">
        <h2 class="card-title">How it works</h2>
        <ol class="mt-5 space-y-5">
          <?php foreach ([
            ['Register your organisation', 'Create a login with your official e-mail, then complete the guided profile — company details, ownership, PAN and statutory registrations, address and contact person.'],
            ['Get verified', 'The verification desk reviews your submission, usually within three working days. Verified organisations carry a badge on every job they publish.'],
            ['Publish a job title', 'The curation sheet wizard walks you through the role, eligibility, engagement terms and selection process, so candidates see complete, comparable information.'],
            ['Screen and shortlist', 'Applications arrive with structured profiles and resumes attached. Move candidates through applied, shortlisted, interview and selected — the candidate sees the status change.'],
          ] as $i => [$t, $d]): ?>
            <li class="flex gap-4">
              <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-500 text-sm font-bold text-white"><?= $i + 1 ?></span>
              <span>
                <span class="block text-sm font-semibold text-ink"><?= e($t) ?></span>
                <span class="mt-0.5 block text-sm text-ink-soft"><?= e($d) ?></span>
              </span>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>

      <div class="card card-pad">
        <h2 class="card-title">What you get</h2>
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
          <?php foreach ([
            ['shield-check', 'Verified candidates', 'Identity and documents verified once, reused across every application.'],
            ['clipboard', 'Structured applications', 'Qualification, experience and certification data in a consistent shape.'],
            ['chart', 'Hiring insight', 'See views, applications and shortlisting rates for every job title.'],
            ['wallet', 'No listing fee', 'Publishing job titles on DWMS 2.0 costs nothing.'],
          ] as [$ic, $t, $d]): ?>
            <div class="flex gap-3">
              <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-success/10 text-success"><?= icon($ic, 'h-4 w-4') ?></span>
              <span>
                <span class="block text-sm font-semibold text-ink"><?= e($t) ?></span>
                <span class="block text-sm text-ink-soft"><?= e($d) ?></span>
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <aside class="space-y-5">
      <div class="card card-pad">
        <h2 class="card-title">Start hiring</h2>
        <p class="mt-2 text-sm text-ink-soft">Registration takes about ten minutes if you have your PAN and registration details to hand.</p>
        <div class="mt-4 space-y-2">
          <a href="<?= url('/employer/register') ?>" class="btn-primary btn-block">Register your organisation</a>
          <a href="<?= url('/employer/login') ?>" class="btn-outline btn-block">Employer login</a>
        </div>
      </div>
      <div class="card card-pad">
        <h2 class="card-title">Keep handy</h2>
        <ul class="mt-3 space-y-2 text-sm text-ink-soft">
          <?php foreach (['Company PAN', 'GSTIN (if registered)', 'Incorporation / registration number', 'Registered office address', 'Contact person details'] as $item): ?>
            <li class="flex gap-2"><span class="text-brand-500"><?= icon('check', 'h-4 w-4') ?></span><?= e($item) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </aside>
  </div>
</section>
