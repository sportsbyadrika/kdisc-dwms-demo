<?php
/** @var array $slides @var array $stats @var array $latestJobs @var array $topSkills @var array $services */
$pageTitle = null;
$cards = [
    [
        'title' => 'Skills',
        'desc'  => 'Enrol in government-backed skilling programmes with certification, stipend support and placement linkage.',
        'icon'  => 'sparkles',
        'path'  => '/skills',
        'cta'   => 'Browse programmes',
        'stat'  => $stats['skills'] . ' programmes',
        'tone'  => 'from-emerald-500 to-emerald-700',
    ],
    [
        'title' => 'Jobs',
        'desc'  => 'Search verified vacancies published by registered employers, check your eligibility and apply in a single click.',
        'icon'  => 'briefcase',
        'path'  => '/jobs',
        'cta'   => 'Search jobs',
        'stat'  => $stats['jobs'] . ' open positions',
        'tone'  => 'from-brand-500 to-brand-700',
    ],
    [
        'title' => 'Career Services',
        'desc'  => 'Career counselling, resume clinics, mock interviews and migration guidance from accredited service desks.',
        'icon'  => 'compass',
        'path'  => '/career-services',
        'cta'   => 'Explore services',
        'stat'  => $stats['services'] . ' services',
        'tone'  => 'from-amber-500 to-amber-600',
    ],
];
?>

<!-- ======================================================= hero panel -->
<section class="bg-white" aria-label="Highlights">
  <div class="shell py-6 sm:py-8">
    <?php if ($slides): ?>
    <div x-data="{
            active: 0,
            total: <?= count($slides) ?>,
            timer: null,
            start() { this.stop(); this.timer = setInterval(() => this.next(), 7000); },
            stop()  { clearInterval(this.timer); },
            next()  { this.active = (this.active + 1) % this.total; },
            prev()  { this.active = (this.active - 1 + this.total) % this.total; },
            go(i)   { this.active = i; }
         }"
         x-init="start()" @mouseenter="stop()" @mouseleave="start()"
         class="relative grid overflow-hidden rounded-card shadow-card">

      <?php foreach ($slides as $i => $s): ?>
        <!-- Every slide occupies the same grid cell, so each one keeps the
             container open on its own; the hidden ones simply drop out. -->
        <div x-show="active === <?= $i ?>" x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             <?= $i === 0 ? '' : 'x-cloak' ?>
             class="col-start-1 row-start-1">
          <div class="relative min-h-[300px] bg-brand-700 sm:min-h-[360px] lg:min-h-[400px]">
            <img src="<?= e(upload_url($s['image'], asset('img/hero-pattern.svg'))) ?>" alt=""
                 class="absolute inset-0 h-full w-full object-cover" <?= $i === 0 ? '' : 'loading="lazy"' ?>>
            <div class="absolute inset-0 bg-gradient-to-r from-brand-900/90 via-brand-800/70 to-brand-700/30"></div>
            <div class="relative flex min-h-[300px] items-center px-6 py-10 sm:min-h-[360px] sm:px-10 lg:min-h-[400px] lg:px-14">
              <div class="max-w-xl text-white">
                <span class="badge bg-white/15 text-white backdrop-blur"><?= icon('flame', 'h-3.5 w-3.5') ?>DWMS 2.0</span>
                <h1 class="mt-4 text-3xl font-bold leading-tight sm:text-4xl lg:text-[2.75rem]"><?= e($s['title']) ?></h1>
                <?php if ($s['subtitle']): ?>
                  <p class="mt-3 max-w-lg text-sm leading-relaxed text-white/85 sm:text-base"><?= e($s['subtitle']) ?></p>
                <?php endif; ?>
                <?php if ($s['cta_label']): ?>
                  <a href="<?= url($s['cta_url'] ?: '/') ?>" class="btn-lg mt-6 inline-flex items-center gap-2 rounded-full bg-white px-6 py-2.5 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">
                    <?= e($s['cta_label']) ?><?= icon('arrow-right', 'h-4 w-4') ?>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

      <?php if (count($slides) > 1): ?>
        <button type="button" @click="prev()" aria-label="Previous slide"
                class="absolute left-3 top-1/2 hidden -translate-y-1/2 rounded-full bg-black/30 p-2 text-white backdrop-blur transition hover:bg-black/50 sm:block"><?= icon('chevron-left') ?></button>
        <button type="button" @click="next()" aria-label="Next slide"
                class="absolute right-3 top-1/2 hidden -translate-y-1/2 rounded-full bg-black/30 p-2 text-white backdrop-blur transition hover:bg-black/50 sm:block"><?= icon('chevron-right') ?></button>
        <div class="absolute bottom-4 left-1/2 flex -translate-x-1/2 items-center gap-2">
          <?php foreach ($slides as $i => $_): ?>
            <button type="button" @click="go(<?= $i ?>)" aria-label="Go to slide <?= $i + 1 ?>"
                    class="h-1.5 rounded-full bg-white/50 transition-all"
                    :class="active === <?= $i ?> ? 'w-7 !bg-white' : 'w-3 hover:bg-white/80'"></button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- search bar -->
    <form action="<?= url('/jobs') ?>" method="get" class="mt-6 rounded-card border border-line bg-white p-3 shadow-card sm:p-4">
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr,1fr,auto]">
        <div class="relative">
          <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-ink-faint"><?= icon('search', 'h-4 w-4') ?></span>
          <label class="sr-only" for="hero-q">Job title or skill</label>
          <input id="hero-q" name="q" type="search" placeholder="Job title, skill or company" class="field !py-2.5 pl-9">
        </div>
        <div class="relative">
          <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-ink-faint"><?= icon('map-pin', 'h-4 w-4') ?></span>
          <label class="sr-only" for="hero-d">District</label>
          <select id="hero-d" name="district" class="field !py-2.5 pl-9">
            <option value="">All districts</option>
            <?php foreach ($districts as $d): ?><option value="<?= e($d) ?>"><?= e($d) ?></option><?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn-primary btn-lg">Search</button>
      </div>
    </form>
  </div>
</section>

<!-- ==================================================== three main cards -->
<section class="shell py-10 sm:py-12" aria-labelledby="services-heading">
  <div class="text-center">
    <h2 id="services-heading" class="section-title">What are you here for?</h2>
    <p class="mx-auto mt-2 max-w-2xl text-sm text-ink-soft">
      Three doorways into the workforce ecosystem — build the skills you need, find the work they lead to, and get guided along the way.
    </p>
  </div>

  <div class="mt-8 grid grid-cols-1 gap-5 md:grid-cols-3">
    <?php foreach ($cards as $c): ?>
      <a href="<?= url($c['path']) ?>"
         class="group relative flex flex-col overflow-hidden rounded-card bg-white p-6 shadow-card transition hover:-translate-y-1 hover:shadow-pop focus-visible:-translate-y-1">
        <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r <?= $c['tone'] ?>"></span>
        <span class="flex items-center justify-between gap-3">
          <h3 class="text-lg font-semibold text-ink group-hover:text-brand-700"><?= e($c['title']) ?></h3>
          <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-card bg-gradient-to-br <?= $c['tone'] ?> text-white shadow-sm">
            <?= icon($c['icon'], 'h-6 w-6') ?>
          </span>
        </span>
        <p class="mt-3 flex-1 text-sm leading-relaxed text-ink-soft"><?= e($c['desc']) ?></p>
        <span class="mt-4 flex items-center justify-between border-t border-line pt-4">
          <span class="text-xs font-semibold uppercase tracking-wide text-ink-faint"><?= e($c['stat']) ?></span>
          <span class="inline-flex items-center gap-1 text-sm font-semibold text-brand-500 group-hover:text-brand-700">
            <?= e($c['cta']) ?><?= icon('arrow-right', 'h-4 w-4 transition-transform group-hover:translate-x-0.5') ?>
          </span>
        </span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ============================================================== stats -->
<section class="border-y border-line bg-white py-8">
  <div class="shell grid grid-cols-2 gap-6 text-center sm:grid-cols-4">
    <?php foreach ([
        ['jobs', 'Open vacancies', 'briefcase'],
        ['employers', 'Verified employers', 'building'],
        ['seekers', 'Registered job seekers', 'users'],
        ['skills', 'Skilling programmes', 'graduation'],
    ] as [$key, $label, $ic]): ?>
      <div>
        <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-brand-500"><?= icon($ic, 'h-5 w-5') ?></span>
        <p class="mt-2 text-2xl font-bold text-ink sm:text-3xl"><?= number_format((int) $stats[$key]) ?></p>
        <p class="text-xs font-medium uppercase tracking-wide text-ink-faint"><?= e($label) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ======================================================== latest jobs -->
<?php if ($latestJobs): ?>
<section class="shell py-10 sm:py-12" aria-labelledby="latest-jobs">
  <div class="flex items-end justify-between gap-4">
    <div>
      <h2 id="latest-jobs" class="section-title">Latest opportunities</h2>
      <p class="mt-1 text-sm text-ink-soft">Freshly published curation sheets from verified employers.</p>
    </div>
    <a href="<?= url('/jobs') ?>" class="hidden shrink-0 items-center gap-1 text-sm font-semibold text-brand-500 hover:text-brand-700 sm:inline-flex">
      See all jobs <?= icon('arrow-right', 'h-4 w-4') ?>
    </a>
  </div>

  <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($latestJobs as $j): ?>
      <article class="flex flex-col rounded-card bg-white p-5 shadow-card transition hover:shadow-pop">
        <div class="flex items-start gap-3">
          <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded bg-brand-50 text-brand-500">
            <?php if (!empty($j['logo'])): ?>
              <img src="<?= e(upload_url($j['logo'])) ?>" alt="" class="h-full w-full object-cover">
            <?php else: ?><?= icon('building', 'h-5 w-5') ?><?php endif; ?>
          </span>
          <div class="min-w-0">
            <h3 class="truncate text-sm font-semibold text-ink"><?= e($j['title']) ?></h3>
            <p class="truncate text-xs text-ink-soft"><?= e($j['company_name']) ?></p>
          </div>
        </div>
        <dl class="mt-3 space-y-1.5 text-xs text-ink-soft">
          <div class="flex items-center gap-1.5"><?= icon('map-pin', 'h-3.5 w-3.5 shrink-0') ?><span class="truncate"><?= e($j['job_location'] ?: $j['district'] ?: 'Kerala') ?></span></div>
          <div class="flex items-center gap-1.5"><?= icon('wallet', 'h-3.5 w-3.5 shrink-0') ?><span><?= e(salary_range($j['salary_min'], $j['salary_max'])) ?></span></div>
          <div class="flex items-center gap-1.5"><?= icon('clock', 'h-3.5 w-3.5 shrink-0') ?><span>Apply by <?= e(fdate($j['last_date'])) ?></span></div>
        </dl>
        <div class="mt-4 flex items-center justify-between border-t border-line pt-4">
          <span class="badge-blue"><?= e(ucwords(str_replace('_', ' ', $j['employment_type']))) ?></span>
          <a href="<?= url('/jobs/' . $j['id']) ?>" class="text-sm font-semibold text-brand-500 hover:text-brand-700">View &amp; apply</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <a href="<?= url('/jobs') ?>" class="btn-outline btn-block mt-6 sm:hidden">See all jobs</a>
</section>
<?php endif; ?>

<!-- =================================================== skills + services -->
<section class="bg-white py-10 sm:py-12">
  <div class="shell grid grid-cols-1 gap-10 lg:grid-cols-2">
    <?php if ($topSkills): ?>
    <div class="min-w-0">
      <div class="flex items-end justify-between gap-3">
        <h2 class="section-title">Skilling programmes</h2>
        <a href="<?= url('/skills') ?>" class="text-sm font-semibold text-brand-500 hover:text-brand-700">See all</a>
      </div>
      <ul class="mt-5 space-y-3">
        <?php foreach ($topSkills as $s): ?>
          <li>
            <a href="<?= url('/skills/' . $s['id']) ?>" class="flex items-start gap-3 rounded-card border border-line p-4 transition hover:border-brand-200 hover:bg-brand-50/40">
              <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-success/10 text-success"><?= icon('graduation', 'h-5 w-5') ?></span>
              <span class="min-w-0 flex-1">
                <span class="block truncate text-sm font-semibold text-ink"><?= e($s['title']) ?></span>
                <span class="block truncate text-xs text-ink-soft"><?= e($s['provider']) ?></span>
                <span class="mt-1.5 flex flex-wrap gap-1.5">
                  <span class="chip"><?= e(ucfirst($s['mode'])) ?></span>
                  <?php if ($s['is_free']): ?><span class="badge-green">Free</span><?php else: ?><span class="chip"><?= e(money((float) $s['fee'])) ?></span><?php endif; ?>
                  <?php if ($s['district']): ?><span class="chip"><?= e($s['district']) ?></span><?php endif; ?>
                </span>
              </span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <?php if ($services): ?>
    <div class="min-w-0">
      <div class="flex items-end justify-between gap-3">
        <h2 class="section-title">Career services</h2>
        <a href="<?= url('/career-services') ?>" class="text-sm font-semibold text-brand-500 hover:text-brand-700">See all</a>
      </div>
      <ul class="mt-5 space-y-3">
        <?php foreach ($services as $s): ?>
          <li>
            <a href="<?= url('/career-services/' . $s['id']) ?>" class="flex items-start gap-3 rounded-card border border-line p-4 transition hover:border-brand-200 hover:bg-brand-50/40">
              <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-warning/10 text-warning"><?= icon($s['icon'] ?: 'compass', 'h-5 w-5') ?></span>
              <span class="min-w-0 flex-1">
                <span class="block truncate text-sm font-semibold text-ink"><?= e($s['title']) ?></span>
                <span class="block text-xs text-ink-soft"><?= e(str_excerpt($s['summary'], 90)) ?></span>
                <span class="mt-1.5 flex flex-wrap gap-1.5">
                  <span class="chip"><?= e(ucfirst($s['service_mode'])) ?></span>
                  <?php if ($s['is_free']): ?><span class="badge-green">No fee</span><?php endif; ?>
                </span>
              </span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ============================================================== CTA -->
<section class="shell py-12">
  <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="rounded-card bg-brand-700 p-8 text-white">
      <h2 class="text-xl font-semibold">Looking for work?</h2>
      <p class="mt-2 text-sm text-white/85">Verify your e-mail, complete e-KYC once, and apply to every opportunity with a single profile.</p>
      <a href="<?= url('/register') ?>" class="mt-5 inline-flex items-center gap-2 rounded-full bg-white px-5 py-2 text-sm font-semibold text-brand-700 hover:bg-brand-50">
        Register as job seeker <?= icon('arrow-right', 'h-4 w-4') ?>
      </a>
    </div>
    <div class="rounded-card border border-line bg-white p-8">
      <h2 class="text-xl font-semibold text-ink">Hiring for your organisation?</h2>
      <p class="mt-2 text-sm text-ink-soft">Publish curated job sheets, screen verified candidates and track every application from one dashboard.</p>
      <a href="<?= url('/employer/register') ?>" class="btn-primary mt-5">Register as employer <?= icon('arrow-right', 'h-4 w-4') ?></a>
    </div>
  </div>
</section>
