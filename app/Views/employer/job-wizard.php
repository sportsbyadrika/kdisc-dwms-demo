<?php
/** @var array|null $job @var int $step @var array $steps @var array $categories
 *  @var array $quals @var array $types @var array $modes @var array $periods @var array $districts */
$v = static fn(string $f, $d = '') => old($f, $job[$f] ?? $d);
$err = static function (string $f) { $m = error_for($f); return $m ? '<p class="err">' . icon('alert', 'h-3.5 w-3.5') . e($m) . '</p>' : ''; };
$cls = static fn(string $f) => 'field' . (error_for($f) ? ' field-error' : '');
$action = $job ? '/employer/jobs/' . $job['id'] . '/edit' : '/employer/jobs/create';
?>
<?php partial('dash-header', [
  'title' => $job ? $job['title'] : 'Publish a job title',
  'sub'   => 'The curation sheet captures the role in a structure candidates can compare fairly.',
  'actions' => $job
      ? '<span class="badge-gray">' . e($job['code']) . '</span> <span class="' .
        ($job['status'] === 'published' ? 'badge-green' : 'badge-amber') . '">' . e(ucfirst($job['status'])) . '</span>'
      : '<span class="badge-amber">New draft</span>',
]); ?>

<div class="card">
  <div class="card-pad"><?php partial('stepper', ['steps' => $steps, 'step' => $step, 'linkBase' => $job ? '/employer/jobs/' . $job['id'] . '/edit' : null]); ?></div>

  <form method="post" action="<?= url($action) ?>" class="border-t border-line">
    <?= csrf_field() ?>
    <input type="hidden" name="step" value="<?= (int) $step ?>">

    <?php if ($step === 1): ?>
      <div class="card-pad">
        <h2 class="card-title">The role</h2>
        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div class="sm:col-span-2">
            <label class="label" for="j-title">Job title <span class="text-danger">*</span></label>
            <input id="j-title" name="title" required class="<?= $cls('title') ?>" value="<?= e($v('title')) ?>" placeholder="Junior PHP Developer">
            <?= $err('title') ?>
          </div>
          <div>
            <label class="label" for="j-category">Category</label>
            <select id="j-category" name="category_id" class="<?= $cls('category_id') ?>">
              <option value="">Select…</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= (string) $v('category_id') === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="label" for="j-vacancies">Number of vacancies <span class="text-danger">*</span></label>
            <input id="j-vacancies" name="vacancies" type="number" min="1" max="9999" required class="<?= $cls('vacancies') ?>" value="<?= e($v('vacancies', 1)) ?>">
            <?= $err('vacancies') ?>
          </div>
          <div>
            <label class="label" for="j-type">Employment type <span class="text-danger">*</span></label>
            <select id="j-type" name="employment_type" required class="<?= $cls('employment_type') ?>">
              <?php foreach ($types as $k => $label): ?>
                <option value="<?= e($k) ?>" <?= $v('employment_type', 'full_time') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="label" for="j-mode">Work mode <span class="text-danger">*</span></label>
            <select id="j-mode" name="work_mode" required class="<?= $cls('work_mode') ?>">
              <?php foreach ($modes as $k => $label): ?>
                <option value="<?= e($k) ?>" <?= $v('work_mode', 'on_site') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="sm:col-span-2">
            <label class="label" for="j-desc">About the role <span class="text-danger">*</span></label>
            <textarea id="j-desc" name="description" rows="5" required class="<?= $cls('description') ?>"
                      placeholder="What the person will do and the team they will join."><?= e($v('description')) ?></textarea>
            <?= $err('description') ?>
            <p class="hint">At least 30 characters. Candidates read this first.</p>
          </div>
          <div class="sm:col-span-2">
            <label class="label" for="j-resp">Key responsibilities</label>
            <textarea id="j-resp" name="responsibilities" rows="4" class="<?= $cls('responsibilities') ?>"
                      placeholder="One responsibility per line."><?= e($v('responsibilities')) ?></textarea>
            <?= $err('responsibilities') ?>
          </div>
        </div>
      </div>

    <?php elseif ($step === 2): ?>
      <div class="card-pad">
        <h2 class="card-title">Eligibility criteria</h2>
        <p class="mt-1 text-sm text-ink-soft">These are checked against each candidate's profile before they can apply.</p>
        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="label" for="j-qual">Minimum qualification <span class="text-danger">*</span></label>
            <select id="j-qual" name="min_qualification" required class="<?= $cls('min_qualification') ?>">
              <?php foreach ($quals as $k => $label): ?>
                <option value="<?= e($k) ?>" <?= $v('min_qualification', 'any') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="label" for="j-gender">Gender preference <span class="text-danger">*</span></label>
            <select id="j-gender" name="gender_preference" required class="<?= $cls('gender_preference') ?>">
              <option value="any" <?= $v('gender_preference', 'any') === 'any' ? 'selected' : '' ?>>Open to all</option>
              <option value="male" <?= $v('gender_preference') === 'male' ? 'selected' : '' ?>>Male candidates only</option>
              <option value="female" <?= $v('gender_preference') === 'female' ? 'selected' : '' ?>>Female candidates only</option>
            </select>
            <p class="hint">Restrict only where the law permits it.</p>
          </div>
          <div class="sm:col-span-2">
            <label class="label" for="j-qnote">Qualification notes</label>
            <input id="j-qnote" name="qualification_note" class="<?= $cls('qualification_note') ?>" value="<?= e($v('qualification_note')) ?>"
                   placeholder="e.g. B.Tech in CS/IT, or an equivalent diploma with 3 years' experience">
            <?= $err('qualification_note') ?>
          </div>
          <div class="sm:col-span-2">
            <label class="label" for="j-skills">Skills required</label>
            <input id="j-skills" name="skills_required" class="<?= $cls('skills_required') ?>" value="<?= e($v('skills_required')) ?>"
                   placeholder="PHP, MySQL, JavaScript, Git">
            <?= $err('skills_required') ?>
            <p class="hint">Separate with commas. Candidates search on these.</p>
          </div>
          <div>
            <label class="label" for="j-expmin">Minimum experience (years)</label>
            <input id="j-expmin" name="experience_min" type="number" min="0" max="50" step="0.5" class="<?= $cls('experience_min') ?>" value="<?= e($v('experience_min', 0)) ?>">
            <?= $err('experience_min') ?>
            <p class="hint">Enter 0 to invite freshers.</p>
          </div>
          <div>
            <label class="label" for="j-expmax">Maximum experience (years)</label>
            <input id="j-expmax" name="experience_max" type="number" min="0" max="50" step="0.5" class="<?= $cls('experience_max') ?>" value="<?= e($v('experience_max')) ?>">
            <?= $err('experience_max') ?>
          </div>
          <div>
            <label class="label" for="j-agemin">Minimum age</label>
            <input id="j-agemin" name="age_min" type="number" min="14" max="70" class="<?= $cls('age_min') ?>" value="<?= e($v('age_min')) ?>">
            <?= $err('age_min') ?>
          </div>
          <div>
            <label class="label" for="j-agemax">Maximum age</label>
            <input id="j-agemax" name="age_max" type="number" min="14" max="70" class="<?= $cls('age_max') ?>" value="<?= e($v('age_max')) ?>">
            <?= $err('age_max') ?>
          </div>
        </div>
      </div>

    <?php elseif ($step === 3): ?>
      <div class="card-pad">
        <h2 class="card-title">Engagement terms</h2>
        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="label" for="j-smin">Salary from</label>
            <input id="j-smin" name="salary_min" type="number" min="0" step="500" class="<?= $cls('salary_min') ?>" value="<?= e($v('salary_min')) ?>">
            <?= $err('salary_min') ?>
          </div>
          <div>
            <label class="label" for="j-smax">Salary to</label>
            <input id="j-smax" name="salary_max" type="number" min="0" step="500" class="<?= $cls('salary_max') ?>" value="<?= e($v('salary_max')) ?>">
            <?= $err('salary_max') ?>
          </div>
          <div>
            <label class="label" for="j-period">Salary period <span class="text-danger">*</span></label>
            <select id="j-period" name="salary_period" required class="<?= $cls('salary_period') ?>">
              <?php foreach ($periods as $k => $label): ?>
                <option value="<?= e($k) ?>" <?= $v('salary_period', 'monthly') === $k ? 'selected' : '' ?>><?= e(ucfirst(str_replace('per ', '', $label))) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="label" for="j-district">District <span class="text-danger">*</span></label>
            <select id="j-district" name="district" required class="<?= $cls('district') ?>">
              <option value="">Select district</option>
              <?php foreach ($districts as $d): ?>
                <option value="<?= e($d) ?>" <?= $v('district') === $d ? 'selected' : '' ?>><?= e($d) ?></option>
              <?php endforeach; ?>
              <option value="Other" <?= $v('district') === 'Other' ? 'selected' : '' ?>>Outside Kerala</option>
            </select>
            <?= $err('district') ?>
          </div>
          <div class="sm:col-span-2">
            <label class="label" for="j-location">Job location <span class="text-danger">*</span></label>
            <input id="j-location" name="job_location" required class="<?= $cls('job_location') ?>" value="<?= e($v('job_location')) ?>"
                   placeholder="Infopark, Kakkanad, Kochi">
            <?= $err('job_location') ?>
          </div>
          <div>
            <label class="label" for="j-state">State <span class="text-danger">*</span></label>
            <input id="j-state" name="state" required class="<?= $cls('state') ?>" value="<?= e($v('state', 'Kerala')) ?>">
            <?= $err('state') ?>
          </div>
          <div class="sm:col-span-2">
            <label class="label" for="j-benefits">Benefits</label>
            <input id="j-benefits" name="benefits" class="<?= $cls('benefits') ?>" value="<?= e($v('benefits')) ?>"
                   placeholder="PF, ESI, health cover, transport, canteen">
            <?= $err('benefits') ?>
          </div>
        </div>
      </div>

    <?php else: ?>
      <div class="card-pad">
        <h2 class="card-title">Selection process and publishing</h2>
        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div class="sm:col-span-2">
            <label class="label" for="j-process">Selection process</label>
            <input id="j-process" name="selection_process" class="<?= $cls('selection_process') ?>" value="<?= e($v('selection_process')) ?>"
                   placeholder="Written test, technical interview, HR round">
            <?= $err('selection_process') ?>
          </div>
          <div>
            <label class="label" for="j-cemail">Contact e-mail for candidates</label>
            <input id="j-cemail" name="contact_email" type="email" class="<?= $cls('contact_email') ?>" value="<?= e($v('contact_email')) ?>">
            <?= $err('contact_email') ?>
          </div>
          <div>
            <label class="label" for="j-cmobile">Contact mobile</label>
            <div class="flex">
              <span class="inline-flex items-center rounded-l border border-r-0 border-ink/30 bg-black/[0.03] px-3 text-sm text-ink-soft">+91</span>
              <input id="j-cmobile" name="contact_mobile" inputmode="numeric" maxlength="10" class="<?= $cls('contact_mobile') ?> rounded-l-none" value="<?= e($v('contact_mobile')) ?>">
            </div>
            <?= $err('contact_mobile') ?>
          </div>
          <div>
            <label class="label" for="j-last">Last date to apply</label>
            <input id="j-last" name="last_date" type="date" min="<?= date('Y-m-d') ?>" class="<?= $cls('last_date') ?>" value="<?= e($v('last_date')) ?>">
            <?= $err('last_date') ?>
            <p class="hint">Leave blank to keep the vacancy open until you close it.</p>
          </div>
        </div>

        <div class="mt-6 rounded-card border border-line bg-canvas p-4">
          <p class="text-sm font-semibold text-ink">Ready to publish?</p>
          <p class="mt-1 text-sm text-ink-soft">
            Published vacancies appear immediately in the public job search. You can close or edit them at any time.
            <?php if ($job && $job['status'] === 'published'): ?>
              This vacancy is already published — saving will update it.
            <?php endif; ?>
          </p>
        </div>
      </div>
    <?php endif; ?>

    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line px-5 py-4 sm:px-6">
      <?php if ($step > 1 && $job): ?>
        <a href="<?= url('/employer/jobs/' . $job['id'] . '/edit', ['step' => $step - 1]) ?>" class="btn-ghost"><?= icon('arrow-left', 'h-4 w-4') ?>Back</a>
      <?php else: ?><a href="<?= url('/employer/jobs') ?>" class="btn-ghost">Cancel</a><?php endif; ?>

      <div class="flex flex-wrap gap-2">
        <?php if ($step === 4): ?>
          <button type="submit" name="action" value="draft" class="btn-outline">Save as draft</button>
          <button type="submit" name="action" value="publish" class="btn-primary"><?= icon('send', 'h-4 w-4') ?>Publish vacancy</button>
        <?php else: ?>
          <button type="submit" class="btn-primary">Save and continue <?= icon('arrow-right', 'h-4 w-4') ?></button>
        <?php endif; ?>
      </div>
    </div>
  </form>
</div>
