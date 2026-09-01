<?php /** @var array $resumes */ ?>
<?php partial('dash-header', [
  'title' => 'Resume',
  'sub'   => 'Upload up to five resumes. The one marked primary is attached to every application you send.',
]); ?>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-[1.5fr,1fr]">
  <div class="space-y-4">
    <?php if ($resumes): ?>
      <div class="space-y-3">
        <?php foreach ($resumes as $r): ?>
          <article class="card card-pad flex flex-wrap items-center gap-4">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded bg-brand-50 text-brand-500"><?= icon('document') ?></span>
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-2">
                <h3 class="truncate text-sm font-semibold text-ink"><?= e($r['title'] ?: $r['file_name']) ?></h3>
                <?php if ($r['is_primary']): ?><span class="badge-green"><?= icon('star', 'h-3 w-3') ?>Primary</span><?php endif; ?>
                <?php if ($r['parse_status'] === 'pending'): ?><span class="badge-gray" title="Automatic extraction of resume data is a later phase">Parsing queued</span><?php endif; ?>
              </div>
              <p class="truncate text-xs text-ink-faint">
                <?= e($r['file_name']) ?>
                <?php if ($r['file_size']): ?> · <?= e(number_format($r['file_size'] / 1024, 0)) ?> KB<?php endif; ?>
                · uploaded <?= e(fdate($r['created_at'])) ?>
              </p>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
              <a href="<?= e(upload_url($r['file_path'])) ?>" target="_blank" rel="noopener" class="btn-outline btn-sm"><?= icon('eye', 'h-3.5 w-3.5') ?>View</a>
              <?php if (!$r['is_primary']): ?>
                <form method="post" action="<?= url('/dashboard/resume/' . $r['id'] . '/primary') ?>">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn-ghost btn-sm"><?= icon('star', 'h-3.5 w-3.5') ?>Make primary</button>
                </form>
              <?php endif; ?>
              <form method="post" action="<?= url('/dashboard/resume/' . $r['id'] . '/delete') ?>" data-confirm="Delete this resume?">
                <?= csrf_field() ?>
                <button type="submit" class="rounded p-1.5 text-ink-faint hover:bg-danger/10 hover:text-danger" aria-label="Delete"><?= icon('trash', 'h-4 w-4') ?></button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <?php partial('empty-state', [
        'icon' => 'document', 'title' => 'No resume uploaded',
        'message' => 'Applications with a resume attached are shortlisted noticeably faster.',
      ]); ?>
    <?php endif; ?>

    <?php if (count($resumes) < 5): ?>
      <div class="card">
        <div class="card-head"><h2 class="card-title">Upload a resume</h2></div>
        <form method="post" action="<?= url('/dashboard/resume') ?>" enctype="multipart/form-data" class="card-pad fieldset">
          <?= csrf_field() ?>
          <div>
            <label class="label" for="r-title">Label</label>
            <input id="r-title" name="title" class="field" placeholder="e.g. Technical roles — 2026" maxlength="120">
            <p class="hint">Optional. Useful when you keep different resumes for different kinds of role.</p>
          </div>
          <div>
            <label class="label" for="r-file">Resume file <span class="text-danger">*</span></label>
            <input id="r-file" name="resume" type="file" accept=".pdf,.doc,.docx" required
                   class="block w-full text-sm text-ink-soft file:mr-3 file:rounded-full file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
            <p class="hint">PDF, DOC or DOCX, up to <?= (int) config('security.max_upload_mb', 5) ?> MB.</p>
          </div>
          <button type="submit" class="btn-primary"><?= icon('upload', 'h-4 w-4') ?>Upload resume</button>
        </form>
      </div>
    <?php else: ?>
      <div class="card card-pad text-sm text-ink-soft">You have reached the limit of five resumes. Delete one to upload another.</div>
    <?php endif; ?>
  </div>

  <div class="space-y-4">
    <div class="card card-pad">
      <h2 class="card-title">Writing a resume that gets read</h2>
      <ul class="mt-3 space-y-2.5 text-sm text-ink-soft">
        <?php foreach ([
          'Keep it to one or two pages — panels skim before they read.',
          'Lead with your most recent role or qualification.',
          'Use the same words the job description uses for skills.',
          'Quantify what you did: how many, how much, how quickly.',
          'Export as PDF so the formatting survives.',
        ] as $tip): ?>
          <li class="flex gap-2"><span class="shrink-0 text-brand-500"><?= icon('check', 'h-4 w-4') ?></span><?= e($tip) ?></li>
        <?php endforeach; ?>
      </ul>
      <a href="<?= url('/career-services') ?>" class="btn-outline btn-sm mt-4">Book a resume clinic</a>
    </div>

    <div class="card card-pad border-l-4 border-l-warning">
      <p class="flex items-center gap-2 text-sm font-semibold text-ink"><?= icon('info', 'h-4 w-4 text-warning') ?>Coming soon</p>
      <p class="mt-1 text-sm text-ink-soft">
        Automatic extraction of qualifications, experience and skills from your uploaded resume is planned for a later
        phase. Every upload is queued for parsing, so nothing needs re-uploading when it goes live.
      </p>
    </div>
  </div>
</div>
