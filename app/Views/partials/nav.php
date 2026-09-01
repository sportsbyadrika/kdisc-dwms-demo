<?php
use App\Core\Auth;

$me    = Auth::current();
$guard = $me['guard'] ?? null;

$menus = [
    ['label' => 'Home',            'path' => '/',                'icon' => 'home'],
    ['label' => 'Jobs',            'path' => '/jobs',            'icon' => 'briefcase'],
    ['label' => 'Skills',          'path' => '/skills',          'icon' => 'sparkles'],
    ['label' => 'Career Services', 'path' => '/career-services', 'icon' => 'compass'],
    ['label' => 'Employers',       'path' => '/employers',       'icon' => 'building'],
];

$dashboards = [
    'seeker'   => ['path' => '/dashboard',          'label' => 'My dashboard',       'icon' => 'grid'],
    'employer' => ['path' => '/employer/dashboard', 'label' => 'Employer dashboard', 'icon' => 'grid'],
    'official' => ['path' => '/official/dashboard', 'label' => 'Admin dashboard',    'icon' => 'grid'],
];
$profileLinks = [
    'seeker'   => ['/dashboard/profile',  '/dashboard/password'],
    'employer' => ['/employer/profile',   '/employer/password'],
    'official' => ['/official/profile',   '/official/password'],
];
?>
<header class="sticky top-0 z-40 border-b border-line bg-white shadow-nav">
  <nav class="shell flex h-14 items-center gap-2" x-data="{ profile: false, signin: false }" @keydown.escape="profile = false; signin = false">

    <!-- brand mark -->
    <a href="<?= url('/') ?>" class="flex shrink-0 items-center gap-2" aria-label="<?= e(setting('site_title', 'DWMS 2.0')) ?> home">
      <span class="flex h-9 w-9 items-center justify-center rounded bg-brand-500 text-white shadow-sm">
        <?= icon('layers', 'h-5 w-5') ?>
      </span>
      <span class="hidden sm:flex flex-col leading-none">
        <span class="text-[15px] font-bold tracking-tight text-brand-700"><?= e(setting('site_title', 'DWMS 2.0')) ?></span>
        <span class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-ink-faint">Workforce Portal</span>
      </span>
    </a>

    <!-- quick search -->
    <form action="<?= url('/jobs') ?>" method="get" class="ml-2 hidden max-w-xs flex-1 md:block">
      <label class="sr-only" for="nav-q">Search jobs</label>
      <div class="relative">
        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-ink-faint"><?= icon('search', 'h-4 w-4') ?></span>
        <input id="nav-q" name="q" type="search" placeholder="Search jobs, skills, services"
               class="w-full rounded border border-transparent bg-brand-50 py-1.5 pl-9 pr-3 text-sm text-ink placeholder:text-ink-faint focus:border-brand-500 focus:bg-white focus:outline-none">
      </div>
    </form>

    <div class="flex-1 md:hidden"></div>

    <!-- desktop menus -->
    <ul class="ml-auto hidden items-center gap-1 lg:flex">
      <?php foreach ($menus as $m): ?>
        <li>
          <a href="<?= url($m['path']) ?>" class="nav-link <?= is_active($m['path']) ? 'nav-link-active' : '' ?>">
            <?= icon($m['icon'], 'h-5 w-5') ?>
            <span><?= e($m['label']) ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="ml-auto flex items-center gap-2 lg:ml-3">
      <?php if ($me): $d = $dashboards[$guard]; [$profilePath, $passwordPath] = $profileLinks[$guard]; ?>
        <!-- signed-in profile menu -->
        <div class="relative" @click.outside="profile = false">
          <button type="button" @click="profile = !profile" :aria-expanded="profile"
                  class="nav-link !flex-row gap-2 rounded-full px-2 py-1 hover:bg-black/5 lg:!flex-col lg:gap-0.5 lg:px-3">
            <span class="flex h-7 w-7 items-center justify-center overflow-hidden rounded-full bg-brand-100 text-[11px] font-bold text-brand-700">
              <?php if (!empty($me['photo'])): ?>
                <img src="<?= e(upload_url($me['photo'])) ?>" alt="" class="h-full w-full object-cover">
              <?php else: ?>
                <?= e(initials($me['name'] ?: 'User')) ?>
              <?php endif; ?>
            </span>
            <span class="flex items-center gap-0.5 text-xs">Me <?= icon('chevron-down', 'h-3 w-3') ?></span>
          </button>

          <div x-show="profile" x-cloak x-transition.origin.top.right
               class="absolute right-0 mt-2 w-72 overflow-hidden rounded-card border border-line bg-white shadow-pop">
            <div class="flex items-start gap-3 p-4">
              <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-100 text-sm font-bold text-brand-700">
                <?php if (!empty($me['photo'])): ?>
                  <img src="<?= e(upload_url($me['photo'])) ?>" alt="" class="h-full w-full object-cover">
                <?php else: ?><?= e(initials($me['name'] ?: 'User')) ?><?php endif; ?>
              </span>
              <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-ink"><?= e($me['name'] ?: 'User') ?></p>
                <p class="truncate text-xs text-ink-faint"><?= e($me['email']) ?></p>
                <p class="mt-1"><span class="badge-blue"><?= e($me['role_name'] ?? ucfirst($guard === 'seeker' ? 'Job seeker' : $guard)) ?></span></p>
              </div>
            </div>
            <div class="border-t border-line py-1">
              <a href="<?= url($d['path']) ?>" class="drop-item"><?= icon($d['icon'], 'h-4 w-4') ?><?= e($d['label']) ?></a>
              <a href="<?= url($profilePath) ?>" class="drop-item"><?= icon('user', 'h-4 w-4') ?>My profile</a>
              <a href="<?= url($passwordPath) ?>" class="drop-item"><?= icon('key', 'h-4 w-4') ?>Change password</a>
            </div>
            <form method="post" action="<?= url('/logout') ?>" class="border-t border-line">
              <?= csrf_field() ?>
              <button type="submit" class="drop-item w-full text-left hover:bg-danger/5 hover:text-danger">
                <?= icon('logout', 'h-4 w-4') ?>Sign out
              </button>
            </form>
          </div>
        </div>
      <?php else: ?>
        <!-- signed-out: login + register -->
        <div class="relative hidden sm:block" @click.outside="signin = false">
          <button type="button" @click="signin = !signin" :aria-expanded="signin"
                  class="btn-ghost gap-1 !rounded-full">
            <?= icon('lock', 'h-4 w-4') ?> Login <?= icon('chevron-down', 'h-3 w-3') ?>
          </button>
          <div x-show="signin" x-cloak x-transition.origin.top.right
               class="absolute right-0 mt-2 w-64 overflow-hidden rounded-card border border-line bg-white py-1 shadow-pop">
            <a href="<?= url('/login') ?>" class="drop-item"><?= icon('user', 'h-4 w-4') ?><span>Job seeker login</span></a>
            <a href="<?= url('/employer/login') ?>" class="drop-item"><?= icon('building', 'h-4 w-4') ?><span>Employer login</span></a>
            <a href="<?= url('/official/login') ?>" class="drop-item"><?= icon('shield', 'h-4 w-4') ?><span>Officials login</span></a>
          </div>
        </div>
        <a href="<?= url('/register') ?>" class="btn-primary btn-sm"><?= icon('plus', 'h-4 w-4') ?>Register</a>
      <?php endif; ?>

      <!-- mobile menu toggle -->
      <button type="button" class="btn-ghost !rounded-full !px-2 lg:hidden"
              @click="$store.ui.mobileNav = !$store.ui.mobileNav"
              :aria-expanded="$store.ui.mobileNav" aria-label="Toggle navigation">
        <span x-show="!$store.ui.mobileNav"><?= icon('menu') ?></span>
        <span x-show="$store.ui.mobileNav" x-cloak><?= icon('x') ?></span>
      </button>
    </div>
  </nav>

  <!-- mobile drawer -->
  <div x-data x-show="$store.ui.mobileNav" x-cloak x-transition class="border-t border-line bg-white lg:hidden">
    <ul class="shell divide-y divide-line py-1">
      <?php foreach ($menus as $m): ?>
        <li>
          <a href="<?= url($m['path']) ?>"
             class="flex items-center gap-3 py-3 text-sm font-medium <?= is_active($m['path']) ? 'text-brand-700' : 'text-ink-soft' ?>">
            <?= icon($m['icon'], 'h-5 w-5') ?><?= e($m['label']) ?>
          </a>
        </li>
      <?php endforeach; ?>
      <?php if (!$me): ?>
        <li class="flex flex-wrap gap-2 py-3">
          <a href="<?= url('/login') ?>" class="btn-outline btn-sm">Job seeker login</a>
          <a href="<?= url('/employer/login') ?>" class="btn-outline btn-sm">Employer login</a>
          <a href="<?= url('/official/login') ?>" class="btn-outline btn-sm">Officials login</a>
        </li>
      <?php endif; ?>
    </ul>
  </div>
</header>
