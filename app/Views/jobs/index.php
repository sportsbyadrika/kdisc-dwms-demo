<?php
/** @var array $spec @var array $active @var array $filters @var array $facets @var array $result @var array $saved @var string $sortKey */
use App\Core\Auth;
use App\Core\Search;

$path = '/jobs';
$loggedIn = Auth::check('seeker');
?>
<?php partial('page-hero', [
  'heading' => 'Search jobs',
  'sub'     => 'Every vacancy here is published by a registered employer as a structured curation sheet, so you can compare roles on the same terms.',
  'crumbs'  => ['Jobs' => null],
]); ?>

<!-- search bar -->
<section class="border-b border-line bg-white">
  <div class="shell py-4">
    <form method="get" action="<?= url($path) ?>" class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr,auto]">
      <?php foreach ($filters as $k => $v): if ($k === 'q') { continue; }
          foreach ((array) $v as $vv): ?>
        <input type="hidden" name="<?= e($k) ?><?= is_array($v) ? '[]' : '' ?>" value="<?= e($vv) ?>">
      <?php endforeach; endforeach; ?>
      <?php if ($sortKey !== 'recent'): ?><input type="hidden" name="sort" value="<?= e($sortKey) ?>"><?php endif; ?>
      <div class="relative">
        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-ink-faint"><?= icon('search', 'h-4 w-4') ?></span>
        <label class="sr-only" for="job-q">Search job titles</label>
        <input id="job-q" name="q" type="search" value="<?= e($active['q'] ?? '') ?>"
               placeholder="Search job titles, skills or employers" class="field !py-2.5 pl-9">
      </div>
      <button type="submit" class="btn-primary btn-lg">Search jobs</button>
    </form>
  </div>
</section>

<section class="shell grid grid-cols-1 gap-6 py-6 lg:grid-cols-[260px,1fr]">
  <?php partial('filter-panel', ['path' => $path, 'spec' => $spec, 'active' => $active, 'filters' => $filters, 'facets' => $facets]); ?>

  <div class="min-w-0">
    <!-- toolbar -->
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
      <p class="text-sm text-ink-soft">
        <strong class="text-ink"><?= number_format($result['total']) ?></strong>
        <?= $result['total'] === 1 ? 'vacancy' : 'vacancies' ?>
        <?php if (!empty($active['q'])): ?> for “<span class="font-medium text-ink"><?= e($active['q']) ?></span>”<?php endif; ?>
      </p>
      <form method="get" action="<?= url($path) ?>" class="flex items-center gap-2">
        <?php foreach ($filters as $k => $v): foreach ((array) $v as $vv): ?>
          <input type="hidden" name="<?= e($k) ?><?= is_array($v) ? '[]' : '' ?>" value="<?= e($vv) ?>">
        <?php endforeach; endforeach; ?>
        <label for="sort" class="text-xs font-semibold uppercase tracking-wider text-ink-faint">Sort</label>
        <select id="sort" name="sort" class="field !w-auto !py-1.5 text-sm" onchange="this.form.submit()">
          <?php foreach ($spec['sort']['options'] as $k => $label): ?>
            <option value="<?= e($k) ?>" <?= $sortKey === $k ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>

    <?php partial('active-filters', ['path' => $path, 'spec' => $spec, 'filters' => $filters]); ?>

    <?php if (!$result['rows']): ?>
      <?php partial('empty-state', [
        'icon' => 'search', 'title' => 'No vacancies match these filters',
        'message' => 'Try removing a filter, widening the district, or searching for a broader job title.',
        'action' => '<a href="' . url($path) . '" class="btn-primary btn-sm">Clear all filters</a>',
      ]); ?>
    <?php else: ?>
      <ul class="space-y-3">
        <?php foreach ($result['rows'] as $j):
            $isSaved = in_array((int) $j['id'], $saved, true);
            $closes  = $j['last_date'] ? (int) floor((strtotime($j['last_date']) - strtotime('today')) / 86400) : null; ?>
          <li>
            <article class="card card-pad transition hover:shadow-pop">
              <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded bg-brand-50 text-brand-500">
                  <?php if ($j['logo']): ?><img src="<?= e(upload_url($j['logo'])) ?>" alt="" class="h-full w-full object-cover">
                  <?php else: ?><?= icon('building') ?><?php endif; ?>
                </span>

                <div class="min-w-0 flex-1">
                  <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                      <h2 class="text-base font-semibold text-ink">
                        <a href="<?= url('/jobs/' . $j['id']) ?>" class="hover:text-brand-700 hover:underline"><?= e($j['title']) ?></a>
                      </h2>
                      <p class="flex flex-wrap items-center gap-1.5 text-sm text-ink-soft">
                        <?= e($j['company_name']) ?>
                        <?php if ($j['employer_status'] === 'verified'): ?>
                          <span class="text-success" title="Verified employer"><?= icon('shield-check', 'h-3.5 w-3.5') ?></span>
                        <?php endif; ?>
                      </p>
                    </div>
                    <div x-data="saveJob(<?= (int) $j['id'] ?>, <?= $isSaved ? 'true' : 'false' ?>, <?= $loggedIn ? 'true' : 'false' ?>, '<?= e(addslashes($j['title'])) ?>')">
                      <button type="button" @click="toggle()" :disabled="busy"
                              class="rounded-full p-2 transition hover:bg-brand-50"
                              :class="saved ? 'text-brand-600' : 'text-ink-faint hover:text-brand-700'"
                              :aria-label="saved ? 'Remove from saved jobs' : 'Save this job'">
                        <span x-show="!saved"><?= icon('bookmark', 'h-5 w-5') ?></span>
                        <span x-show="saved" x-cloak><?= icon('bookmark', 'h-5 w-5 fill-current') ?></span>
                      </button>
                    </div>
                  </div>

                  <dl class="mt-2.5 flex flex-wrap gap-x-4 gap-y-1.5 text-xs text-ink-soft">
                    <div class="flex items-center gap-1.5"><?= icon('map-pin', 'h-3.5 w-3.5 text-ink-faint') ?><?= e($j['job_location'] ?: $j['district'] ?: 'Kerala') ?></div>
                    <div class="flex items-center gap-1.5"><?= icon('wallet', 'h-3.5 w-3.5 text-ink-faint') ?><?= e(salary_range($j['salary_min'], $j['salary_max'])) ?></div>
                    <div class="flex items-center gap-1.5"><?= icon('briefcase', 'h-3.5 w-3.5 text-ink-faint') ?><?= e(\App\Core\Lookup::label(\App\Core\Lookup::EMPLOYMENT_TYPES, $j['employment_type'])) ?></div>
                    <div class="flex items-center gap-1.5"><?= icon('users', 'h-3.5 w-3.5 text-ink-faint') ?><?= (int) $j['vacancies'] ?> vacancy(s)</div>
                  </dl>

                  <p class="mt-2.5 text-sm leading-relaxed text-ink-soft"><?= e(str_excerpt($j['description'], 170)) ?></p>

                  <?php if ($j['skills_required']): ?>
                    <div class="mt-3 flex flex-wrap gap-1.5">
                      <?php foreach (array_slice(array_map('trim', explode(',', $j['skills_required'])), 0, 5) as $skill): ?>
                        <span class="chip"><?= e($skill) ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>

                  <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-4">
                    <p class="flex flex-wrap items-center gap-2 text-xs">
                      <span class="badge-gray"><?= e($j['code']) ?></span>
                      <?php if ($j['category_name']): ?><span class="badge-blue"><?= e($j['category_name']) ?></span><?php endif; ?>
                      <?php if ($closes !== null): ?>
                        <span class="<?= $closes <= 5 ? 'badge-red' : 'badge-gray' ?>">
                          <?= icon('clock', 'h-3 w-3') ?><?= $closes === 0 ? 'Closes today' : 'Closes in ' . $closes . ' day(s)' ?>
                        </span>
                      <?php endif; ?>
                    </p>
                    <div class="flex gap-2">
                      <a href="<?= url('/jobs/' . $j['id']) ?>" class="btn-outline btn-sm"><?= icon('eye', 'h-3.5 w-3.5') ?>View</a>
                      <?php if ($loggedIn): ?>
                        <a href="<?= url('/jobs/' . $j['id']) ?>#apply" class="btn-primary btn-sm"><?= icon('send', 'h-3.5 w-3.5') ?>Apply</a>
                      <?php else: ?>
                        <button type="button" x-data class="btn-primary btn-sm"
                                @click="$store.ui.openLogin({ jobId: <?= (int) $j['id'] ?>, title: '<?= e(addslashes($j['title'])) ?>', redirect: '<?= e(url('/jobs/' . $j['id'])) ?>' })">
                          <?= icon('send', 'h-3.5 w-3.5') ?>Apply
                        </button>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </article>
          </li>
        <?php endforeach; ?>
      </ul>

      <?php partial('pagination', ['path' => $path, 'filters' => $filters, 'result' => $result]); ?>
    <?php endif; ?>
  </div>
</section>
