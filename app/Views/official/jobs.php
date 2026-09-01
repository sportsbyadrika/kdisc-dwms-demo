<?php /** @var array $jobs @var string|null $q @var string|null $active */
$tone = ['published' => 'badge-green', 'draft' => 'badge-amber', 'closed' => 'badge-gray', 'archived' => 'badge-gray']; ?>
<?php partial('dash-header', ['title' => 'Job titles', 'sub' => 'Every vacancy on the platform, across all employers.']); ?>

<form method="get" action="<?= url('/official/jobs') ?>" class="card card-pad mb-4 grid gap-3 sm:grid-cols-[1fr,200px,auto]">
  <div class="relative">
    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-ink-faint"><?= icon('search', 'h-4 w-4') ?></span>
    <label class="sr-only" for="mj-q">Search</label>
    <input id="mj-q" name="q" type="search" value="<?= e($q) ?>" placeholder="Title, code or employer" class="field pl-9">
  </div>
  <div>
    <label class="sr-only" for="mj-status">Status</label>
    <select id="mj-status" name="status" class="field">
      <option value="">Any status</option>
      <?php foreach (['published', 'draft', 'closed', 'archived'] as $s): ?>
        <option value="<?= $s ?>" <?= $active === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button type="submit" class="btn-primary">Search</button>
</form>

<?php if (!$jobs): ?>
  <?php partial('empty-state', ['icon' => 'briefcase', 'title' => 'No job titles found', 'message' => 'Try a different search term or status.']); ?>
<?php else: ?>
  <div class="card">
    <div class="scroll-x">
      <table class="table">
        <thead><tr><th>Job title</th><th>Employer</th><th>District</th><th class="text-right">Applications</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($jobs as $j): ?>
            <tr>
              <td>
                <a href="<?= url('/jobs/' . $j['id']) ?>" target="_blank" rel="noopener" class="font-medium text-ink hover:text-brand-700"><?= e($j['title']) ?></a>
                <span class="block text-xs text-ink-faint"><?= e($j['code']) ?> · <?= (int) $j['vacancies'] ?> vacancy(s)</span>
              </td>
              <td>
                <span class="flex items-center gap-1.5 text-sm">
                  <?= e($j['company_name']) ?>
                  <?php if ($j['employer_status'] === 'verified'): ?><span class="text-success" title="Verified"><?= icon('shield-check', 'h-3.5 w-3.5') ?></span><?php endif; ?>
                </span>
              </td>
              <td class="text-sm text-ink-soft"><?= e($j['district'] ?: '—') ?></td>
              <td class="text-right font-semibold"><?= (int) $j['applications'] ?></td>
              <td><span class="<?= $tone[$j['status']] ?? 'badge-gray' ?>"><?= e(ucfirst($j['status'])) ?></span></td>
              <td>
                <div class="flex justify-end gap-1">
                  <?php if ($j['status'] === 'published'): ?>
                    <form method="post" action="<?= url('/official/jobs/' . $j['id'] . '/moderate') ?>" data-confirm="Close this vacancy?">
                      <?= csrf_field() ?><input type="hidden" name="action" value="close">
                      <button type="submit" class="btn-ghost btn-sm">Close</button>
                    </form>
                  <?php elseif ($j['status'] === 'closed'): ?>
                    <form method="post" action="<?= url('/official/jobs/' . $j['id'] . '/moderate') ?>">
                      <?= csrf_field() ?><input type="hidden" name="action" value="republish">
                      <button type="submit" class="btn-ghost btn-sm">Republish</button>
                    </form>
                  <?php endif; ?>
                  <?php if ($j['status'] !== 'archived'): ?>
                    <form method="post" action="<?= url('/official/jobs/' . $j['id'] . '/moderate') ?>" data-confirm="Archive this vacancy? It will be removed from public search.">
                      <?= csrf_field() ?><input type="hidden" name="action" value="archive">
                      <button type="submit" class="btn-ghost btn-sm text-danger hover:bg-danger/5">Archive</button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
