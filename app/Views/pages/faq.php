<?php
$groups = [
  'Job seekers' => [
    ['Do I need to pay anything to register?', 'No. Registration, applying for vacancies and booking career services are free. A small number of skilling programmes charge a course fee, which is shown on the programme page before you enrol.'],
    ['What do I need before I start registering?', 'A working e-mail address and a mobile number. You will verify the e-mail with a one-time password, then set your name, photograph and password. e-KYC and the rest of your profile can be completed later from your dashboard.'],
    ['Is Aadhaar e-KYC mandatory?', 'No. You can use the platform without it. Completing e-KYC adds a verified badge to your profile, and some employers and departments filter for verified candidates.'],
    ['Who sees my Aadhaar details?', 'Only the government department named on the consent screen, and only after you tick the consent statement. We store the verification outcome and a masked reference, not the full number.'],
    ['Can I apply without uploading a resume?', 'Yes, though employers usually shortlist faster when a resume is attached. You can upload one from the Resume section of your dashboard at any time.'],
    ['I clicked Apply but was asked to sign in. Did I lose the job?', 'No. The vacancy is saved to your list automatically. After you sign in or register, open Saved jobs from your dashboard and apply in one click.'],
    ['How do I know what happened to my application?', 'Every application has a status — applied, shortlisted, interview, selected or not selected. Employers update it as they progress, and you can see the current status under Applications.'],
  ],
  'Employers' => [
    ['How long does verification take?', 'The verification desk usually reviews a complete submission within three working days. You can publish job titles as drafts while verification is pending; they go live once your organisation is verified.'],
    ['What is a curation sheet?', 'It is the structured form used to publish a job title — the role and responsibilities, the eligibility criteria, the engagement terms and the selection process. Capturing it in one structure lets candidates compare roles fairly.'],
    ['Can more than one person from my organisation use the account?', 'The MVP supports one login per organisation. Multiple recruiter logins under a single employer are on the roadmap.'],
    ['Can I see candidates who have not applied to me?', 'No. You see the profiles of candidates who apply to your vacancies. Broader search is available to departmental officials only.'],
  ],
  'Officials' => [
    ['How are user accounts created?', 'A super administrator creates offices, departments and sections, then creates users against them and assigns a role. Roles carry the permission set that decides what each user can reach.'],
    ['Can I restrict a user to one office?', 'Yes. Each user is attached to an office, department or section, and role permissions decide what they can do within it.'],
  ],
];
?>
<?php partial('page-hero', [
  'heading' => 'Frequently asked questions',
  'sub'     => 'Answers to the questions we are asked most often. If yours is not here, write to us from the contact page.',
  'crumbs'  => ['FAQ' => null],
]); ?>

<section class="shell space-y-8 py-10">
  <?php foreach ($groups as $group => $items): ?>
    <div>
      <h2 class="section-title"><?= e($group) ?></h2>
      <div class="mt-4 space-y-2">
        <?php foreach ($items as $i => [$q, $a]): ?>
          <div x-data="{ open: false }" class="card overflow-hidden">
            <button type="button" @click="open = !open" :aria-expanded="open"
                    class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left transition hover:bg-brand-50/50">
              <span class="text-sm font-semibold text-ink"><?= e($q) ?></span>
              <span class="shrink-0 text-ink-faint transition-transform" :class="open && 'rotate-180'"><?= icon('chevron-down', 'h-4 w-4') ?></span>
            </button>
            <div x-show="open" x-cloak x-transition>
              <p class="border-t border-line px-5 py-4 text-sm leading-relaxed text-ink-soft"><?= e($a) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <div class="card card-pad text-center">
    <h2 class="card-title">Still stuck?</h2>
    <p class="mt-1 text-sm text-ink-soft">Our support desk answers within two working days.</p>
    <a href="<?= url('/contact') ?>" class="btn-primary mt-4">Contact support</a>
  </div>
</section>
