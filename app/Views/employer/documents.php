<?php /** @var array $documents @var array $types */ ?>
<?php partial('dash-header', [
  'title' => 'Documents',
  'sub'   => 'Attach scanned copies of your statutory documents — verification is faster with them.',
]); ?>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-[1.4fr,1fr]">
  <div class="space-y-3">
    <?php if ($documents): ?>
      <?php foreach ($documents as $d): ?>
        <article class="card card-pad flex flex-wrap items-center gap-4">
          <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded bg-brand-50 text-brand-500"><?= icon('document') ?></span>
          <div class="min-w-0 flex-1">
            <h3 class="text-sm font-semibold text-ink"><?= e($types[$d['doc_type']] ?? $d['doc_type']) ?></h3>
            <p class="truncate text-xs text-ink-faint">
              <?= e($d['label'] ?: basename($d['file_path'])) ?> · uploaded <?= e(fdate($d['created_at'])) ?>
            </p>
          </div>
          <div class="flex shrink-0 gap-2">
            <a href="<?= e(upload_url($d['file_path'])) ?>" target="_blank" rel="noopener" class="btn-outline btn-sm"><?= icon('eye', 'h-3.5 w-3.5') ?>View</a>
            <form method="post" action="<?= url('/employer/documents/' . $d['id'] . '/delete') ?>" data-confirm="Delete this document?">
              <?= csrf_field() ?>
              <button type="submit" class="rounded p-1.5 text-ink-faint hover:bg-danger/10 hover:text-danger" aria-label="Delete"><?= icon('trash', 'h-4 w-4') ?></button>
            </form>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <?php partial('empty-state', [
        'icon' => 'document', 'title' => 'No documents uploaded',
        'message' => 'Upload your PAN, GST certificate and registration documents so the verification desk can check them.',
      ]); ?>
    <?php endif; ?>

    <div class="card">
      <div class="card-head"><h2 class="card-title">Upload a document</h2></div>
      <form method="post" action="<?= url('/employer/documents') ?>" enctype="multipart/form-data" class="card-pad fieldset">
        <?= csrf_field() ?>
        <div class="form-grid">
          <div>
            <label class="label" for="d-type">Document type <span class="text-danger">*</span></label>
            <select id="d-type" name="doc_type" required class="field">
              <option value="">Select…</option>
              <?php foreach ($types as $k => $label): ?><option value="<?= e($k) ?>"><?= e($label) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="label" for="d-label">Label</label>
            <input id="d-label" name="label" maxlength="150" class="field" placeholder="Optional short description">
          </div>
        </div>
        <div>
          <label class="label" for="d-file">File <span class="text-danger">*</span></label>
          <input id="d-file" name="document" type="file" accept=".pdf,.jpg,.jpeg,.png" required
                 class="block w-full text-sm text-ink-soft file:mr-3 file:rounded-full file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
          <p class="hint">PDF, JPG or PNG, up to <?= (int) config('security.max_upload_mb', 5) ?> MB.</p>
        </div>
        <button type="submit" class="btn-primary"><?= icon('upload', 'h-4 w-4') ?>Upload</button>
      </form>
    </div>
  </div>

  <div class="card card-pad">
    <h2 class="card-title">What the desk looks for</h2>
    <ul class="mt-3 space-y-2.5 text-sm text-ink-soft">
      <?php foreach ([
        'The organisation name on every document should match the registered name in your profile.',
        'Scans must be legible — a clear phone photograph is fine.',
        'PAN is required; GST and incorporation documents speed up approval.',
        'Do not upload documents containing the personal Aadhaar of any individual.',
      ] as $tip): ?>
        <li class="flex gap-2"><span class="shrink-0 text-brand-500"><?= icon('check', 'h-4 w-4') ?></span><?= e($tip) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
