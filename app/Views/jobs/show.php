<?php
/** @var array $job @var array|null $application @var array|null $eligibility @var bool $saved
 *  @var array $similar @var array $resumes */
use App\Core\Auth;
use App\Core\Lookup;

$loggedIn = Auth::check('seeker');
$expired  = $job['last_date'] && strtotime($job['last_date']) < strtotime('today');
$open     = $job['status'] === 'published' && !$expired;
$closes   = $job['last_date'] ? (int) floor((strtotime($job['last_date']) - strtotime('today')) / 86400) : null;
?>
<?php partial('page-hero', [
  'heading' => $job['title'],
  'sub'     => '',
  'crumbs'  => ['Jobs' => '/jobs', $job['title'] => null],
]); ?>

<section class="shell grid gap-6 py-6 lg:grid-cols-[1fr,340px]">
  <div class="min-w-0 space-y-4">

    <!-- header card -->
    <div class="card card-pad">
      <div class="flex flex-wrap items-start gap-4">
        <span class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded bg-brand-50 text-brand-500">
          <?php if ($job['logo']): ?><img src="<?= e(upload_url($job['logo'])) ?>" alt="" class="h-full w-full object-cover">
          <?php else: ?><?= icon('building', 'h-7 w-7') ?><?php endif; ?>
        </span>
        <div class="min-w-0 flex-1">
          <h1 class="text-xl font-bold text-ink sm:text-2xl"><?= e($job['title']) ?></h1>
          <p class="mt-1 flex flex-wrap items-center gap-2 text-sm text-ink-soft">
            <span class="font-medium text-ink"><?= e($job['company_name']) ?></span>
            <?php if ($job['employer_status'] === 'verified'): ?>
              <span class="badge-green"><?= icon('shield-check', 'h-3 w-3') ?>Verified employer</span>
            <?php endif; ?>
          </p>
          <p class="mt-2 flex flex-wrap gap-x-4 gap-y-1.5 text-xs text-ink-soft">
            <span class="flex items-center gap-1.5"><?= icon('map-pin', 'h-3.5 w-3.5 text-ink-faint') ?><?= e($job['job_location'] ?: $job['district'] ?: 'Kerala') ?></span>
            <span class="flex items-center gap-1.5"><?= icon('clock', 'h-3.5 w-3.5 text-ink-faint') ?>Posted <?= e(fdate($job['published_at'] ?: $job['created_at'])) ?></span>
            <span class="flex items-center gap-1.5"><?= icon('eye', 'h-3.5 w-3.5 text-ink-faint') ?><?= (int) $job['views'] ?> views</span>
            <span class="badge-gray"><?= e($job['code']) ?></span>
          </p>
        </div>
      </div>

      <?php if (!$open): ?>
        <p class="mt-4 flex items-center gap-2 rounded-card border border-line bg-canvas px-4 py-3 text-sm font-medium text-ink-soft">
          <?= icon('info', 'h-4 w-4') ?><?= $expired ? 'Applications closed on ' . e(fdate($job['last_date'])) . '.' : 'This vacancy is no longer open for applications.' ?>
        </p>
      <?php elseif ($closes !== null && $closes <= 7): ?>
        <p class="mt-4 flex items-center gap-2 rounded-card border border-warning/30 bg-warning/5 px-4 py-3 text-sm font-medium text-warning">
          <?= icon('clock', 'h-4 w-4') ?><?= $closes === 0 ? 'Applications close today.' : 'Applications close in ' . $closes . ' day(s), on ' . e(fdate($job['last_date'])) . '.' ?>
        </p>
      <?php endif; ?>
    </div>

    <!-- curation sheet -->
    <div class="card">
      <div class="card-head"><h2 class="card-title">Curation sheet</h2><span class="badge-blue">Published by the employer</span></div>
      <dl class="grid grid-cols-1 gap-px bg-line sm:grid-cols-2">
        <?php
        $sheet = [
          ['Employment type', Lookup::label(Lookup::EMPLOYMENT_TYPES, $job['employment_type']), 'briefcase'],
          ['Work mode', Lookup::label(Lookup::WORK_MODES, $job['work_mode']), 'globe'],
          ['Vacancies', (string) (int) $job['vacancies'], 'users'],
          ['Salary', salary_range($job['salary_min'], $job['salary_max']) . ' ' . Lookup::label(Lookup::SALARY_PERIODS, $job['salary_period'], ''), 'wallet'],
          ['Minimum qualification', Lookup::label(Lookup::JOB_QUALIFICATIONS, $job['min_qualification']), 'graduation'],
          ['Experience', ((float) $job['experience_min'] <= 0 ? 'Freshers can apply' : rtrim(rtrim((string) $job['experience_min'], '0'), '.') . ' year(s)') . ($job['experience_max'] ? ' to ' . rtrim(rtrim((string) $job['experience_max'], '0'), '.') . ' year(s)' : ''), 'chart'],
          ['Age', ($job['age_min'] || $job['age_max']) ? (($job['age_min'] ?: '—') . ' to ' . ($job['age_max'] ?: '—') . ' years') : 'No age limit', 'user'],
          ['Gender', $job['gender_preference'] === 'any' ? 'Open to all' : ucfirst($job['gender_preference']) . ' candidates', 'users'],
          ['Location', $job['job_location'] ?: ($job['district'] ?: 'Kerala'), 'map-pin'],
          ['Last date to apply', $job['last_date'] ? fdate($job['last_date']) : 'Open until filled', 'calendar'],
        ];
        foreach ($sheet as [$label, $value, $ic]): ?>
          <div class="flex items-start gap-3 bg-white px-5 py-3.5">
            <span class="mt-0.5 shrink-0 text-brand-500"><?= icon($ic, 'h-4 w-4') ?></span>
            <div class="min-w-0">
              <dt class="text-xs font-medium uppercase tracking-wide text-ink-faint"><?= e($label) ?></dt>
              <dd class="text-sm font-medium text-ink"><?= e($value) ?></dd>
            </div>
          </div>
        <?php endforeach; ?>
      </dl>
    </div>

    <!-- narrative -->
    <?php foreach ([
      ['About the role', $job['description'], 'document'],
      ['Key responsibilities', $job['responsibilities'], 'clipboard'],
      ['Qualification notes', $job['qualification_note'], 'graduation'],
      ['Selection process', $job['selection_process'], 'target'],
      ['Benefits', $job['benefits'], 'star'],
    ] as [$heading, $body, $ic]): if (!trim((string) $body)) { continue; } ?>
      <div class="card card-pad">
        <h2 class="flex items-center gap-2 card-title"><?= icon($ic, 'h-4 w-4 text-brand-500') ?><?= e($heading) ?></h2>
        <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-ink-soft"><?= e($body) ?></p>
      </div>
    <?php endforeach; ?>

    <?php if ($job['skills_required']): ?>
      <div class="card card-pad">
        <h2 class="flex items-center gap-2 card-title"><?= icon('sparkles', 'h-4 w-4 text-brand-500') ?>Skills required</h2>
        <div class="mt-3 flex flex-wrap gap-2">
          <?php foreach (array_map('trim', explode(',', $job['skills_required'])) as $skill): if (!$skill) { continue; } ?>
            <span class="chip !border-brand-200 !bg-brand-50 !text-brand-700"><?= e($skill) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- employer -->
    <div class="card card-pad">
      <h2 class="card-title">About <?= e($job['company_name']) ?></h2>
      <?php if ($job['company_about']): ?>
        <p class="mt-2 text-sm leading-relaxed text-ink-soft"><?= e($job['company_about']) ?></p>
      <?php endif; ?>
      <dl class="mt-4 grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
        <?php foreach (array_filter([
          'Industry' => $job['industry'],
          'Size'     => $job['employee_range'] ? $job['employee_range'] . ' employees' : null,
          'Location' => trim(($job['company_city'] ?: '') . ($job['company_district'] ? ', ' . $job['company_district'] : ''), ', ') ?: null,
          'Website'  => $job['website'],
        ]) as $k => $v): ?>
          <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-ink-faint"><?= e($k) ?></dt>
            <dd class="truncate font-medium text-ink">
              <?php if ($k === 'Website'): ?><a href="<?= e($v) ?>" target="_blank" rel="noopener noreferrer" class="link">Visit</a>
              <?php else: ?><?= e($v) ?><?php endif; ?>
            </dd>
          </div>
        <?php endforeach; ?>
      </dl>
    </div>
  </div>

  <!-- =========================================================== sidebar -->
  <aside class="space-y-4 lg:sticky lg:top-20 lg:self-start">
    <div class="card card-pad" id="apply">
      <?php if ($application): ?>
        <p class="flex items-center gap-2 text-sm font-semibold text-success"><?= icon('check-circle', 'h-5 w-5') ?>You applied on <?= e(fdate($application['applied_at'])) ?></p>
        <p class="mt-1 text-sm text-ink-soft">Current status:
          <span class="badge-blue"><?= e(Lookup::label(Lookup::APPLICATION_STATUS, $application['status'])) ?></span>
        </p>
        <a href="<?= url('/dashboard/applications') ?>" class="btn-outline btn-block mt-4">Track my applications</a>

      <?php elseif (!$open): ?>
        <p class="text-sm font-semibold text-ink">Applications are closed</p>
        <p class="mt-1 text-sm text-ink-soft">Browse similar vacancies below, or set up your profile so you are ready for the next one.</p>
        <a href="<?= url('/jobs') ?>" class="btn-outline btn-block mt-4">Search other jobs</a>

      <?php elseif (!$loggedIn): ?>
        <p class="text-sm font-semibold text-ink">Apply for this vacancy</p>
        <p class="mt-1 text-sm text-ink-soft">Sign in to apply. We will keep this vacancy in your saved list while you do.</p>
        <button type="button" x-data class="btn-primary btn-block btn-lg mt-4"
                @click="$store.ui.openLogin({ jobId: <?= (int) $job['id'] ?>, title: '<?= e(addslashes($job['title'])) ?>', redirect: '<?= e(url('/jobs/' . $job['id'])) ?>' })">
          <?= icon('send', 'h-4 w-4') ?>Apply now
        </button>
        <a href="<?= url('/register') ?>" class="btn-outline btn-block mt-2">New here? Register</a>

      <?php else: ?>
        <!-- eligibility -->
        <div class="mb-4">
          <div class="flex items-center justify-between">
            <p class="text-sm font-semibold text-ink">Your match</p>
            <span class="<?= $eligibility['eligible'] ? 'badge-green' : 'badge-amber' ?>"><?= (int) $eligibility['score'] ?>%</span>
          </div>
          <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-black/10">
            <div class="h-full rounded-full <?= $eligibility['eligible'] ? 'bg-success' : 'bg-warning' ?>" style="width: <?= (int) $eligibility['score'] ?>%"></div>
          </div>
          <ul class="mt-3 space-y-1.5">
            <?php foreach ($eligibility['checks'] as $c): ?>
              <li class="flex items-start gap-2 text-xs">
                <span class="mt-0.5 shrink-0 <?= $c['ok'] ? 'text-success' : 'text-danger' ?>"><?= icon($c['ok'] ? 'check-circle' : 'x-circle', 'h-3.5 w-3.5') ?></span>
                <span>
                  <span class="block font-medium text-ink"><?= e($c['label']) ?></span>
                  <?php if (!empty($c['note'])): ?><span class="block text-ink-faint"><?= e($c['note']) ?></span><?php endif; ?>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <?php if (!$eligibility['eligible']): ?>
          <div class="rounded-card border border-warning/30 bg-warning/5 p-3">
            <p class="text-xs font-semibold text-warning">You cannot apply yet</p>
            <ul class="mt-1 space-y-1 text-xs text-ink-soft">
              <?php foreach ($eligibility['blocking'] as $b): ?><li>• <?= e(ucfirst($b)) ?></li><?php endforeach; ?>
            </ul>
            <a href="<?= url('/dashboard/profile') ?>" class="btn-outline btn-sm mt-3 btn-block">Update my profile</a>
          </div>
        <?php else: ?>
          <?php foreach ($eligibility['warnings'] as $w): ?>
            <p class="mb-2 flex items-start gap-2 rounded-card bg-canvas px-3 py-2 text-xs text-ink-soft">
              <span class="mt-0.5 shrink-0 text-warning"><?= icon('info', 'h-3.5 w-3.5') ?></span><?= e($w) ?>
            </p>
          <?php endforeach; ?>

          <form method="post" action="<?= url('/jobs/' . $job['id'] . '/apply') ?>" class="space-y-3">
            <?= csrf_field() ?>
            <?php if ($resumes): ?>
              <div>
                <label class="label" for="resume_id">Attach resume</label>
                <select id="resume_id" name="resume_id" class="field">
                  <?php foreach ($resumes as $r): ?>
                    <option value="<?= (int) $r['id'] ?>" <?= $r['is_primary'] ? 'selected' : '' ?>>
                      <?= e($r['title'] ?: $r['file_name']) ?><?= $r['is_primary'] ? ' (primary)' : '' ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            <?php else: ?>
              <p class="rounded-card bg-canvas px-3 py-2 text-xs text-ink-soft">
                No resume on file. <a href="<?= url('/dashboard/resume') ?>" class="link">Upload one</a> to strengthen your application.
              </p>
            <?php endif; ?>
            <div>
              <label class="label" for="cover_note">Message to the employer</label>
              <textarea id="cover_note" name="cover_note" rows="3" maxlength="2000" class="field"
                        placeholder="Optional — why you are a good fit for this role."></textarea>
            </div>
            <button type="submit" class="btn-primary btn-block btn-lg"><?= icon('send', 'h-4 w-4') ?>Submit application</button>
          </form>
        <?php endif; ?>
      <?php endif; ?>

      <!-- save -->
      <div x-data="saveJob(<?= (int) $job['id'] ?>, <?= $saved ? 'true' : 'false' ?>, <?= $loggedIn ? 'true' : 'false' ?>, '<?= e(addslashes($job['title'])) ?>')" class="mt-3">
        <button type="button" @click="toggle()" :disabled="busy" class="btn-ghost btn-block">
          <span x-show="!saved"><?= icon('bookmark', 'h-4 w-4') ?></span>
          <span x-show="saved" x-cloak><?= icon('bookmark', 'h-4 w-4 fill-current text-brand-600') ?></span>
          <span x-text="saved ? 'Saved' : 'Save for later'">Save for later</span>
        </button>
      </div>
    </div>

    <?php if ($job['contact_email'] || $job['contact_mobile']): ?>
      <div class="card card-pad">
        <h2 class="card-title">Employer contact</h2>
        <ul class="mt-3 space-y-2 text-sm text-ink-soft">
          <?php if ($job['contact_email']): ?>
            <li class="flex items-center gap-2"><span class="text-brand-500"><?= icon('mail', 'h-4 w-4') ?></span>
              <a href="mailto:<?= e($job['contact_email']) ?>" class="link truncate"><?= e($job['contact_email']) ?></a></li>
          <?php endif; ?>
          <?php if ($job['contact_mobile']): ?>
            <li class="flex items-center gap-2"><span class="text-brand-500"><?= icon('phone', 'h-4 w-4') ?></span><?= e($job['contact_mobile']) ?></li>
          <?php endif; ?>
        </ul>
        <p class="mt-3 text-xs text-ink-faint">DWMS never asks you to pay a fee for a job. Report any such demand from the <a href="<?= url('/contact') ?>" class="link">contact page</a>.</p>
      </div>
    <?php endif; ?>

    <?php if ($similar): ?>
      <div class="card">
        <div class="card-head"><h2 class="card-title">Similar vacancies</h2></div>
        <ul class="divide-y divide-line">
          <?php foreach ($similar as $s): ?>
            <li class="px-5 py-3">
              <a href="<?= url('/jobs/' . $s['id']) ?>" class="block text-sm font-medium text-ink hover:text-brand-700"><?= e($s['title']) ?></a>
              <p class="truncate text-xs text-ink-faint"><?= e($s['company_name']) ?> · <?= e($s['job_location'] ?: 'Kerala') ?></p>
              <p class="text-xs text-ink-soft"><?= e(salary_range($s['salary_min'], $s['salary_max'])) ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
  </aside>
</section>
