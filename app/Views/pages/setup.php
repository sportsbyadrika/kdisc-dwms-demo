<div class="shell max-w-3xl py-10">
  <div class="mb-6 flex items-center gap-3">
    <span class="flex h-11 w-11 items-center justify-center rounded bg-brand-500 text-white"><?= icon('layers', 'h-6 w-6') ?></span>
    <div>
      <h1 class="text-xl font-bold text-ink">DWMS 2.0 — installation</h1>
      <p class="text-sm text-ink-soft">Run this once after uploading the files to your server.</p>
    </div>
  </div>

  <div class="card">
    <div class="card-head"><h2 class="card-title">1. Server requirements</h2></div>
    <ul class="divide-y divide-line">
      <?php foreach ($checks as [$label, $ok, $detail]): ?>
        <li class="flex items-center gap-3 px-5 py-3">
          <span class="<?= $ok ? 'text-success' : 'text-danger' ?>"><?= icon($ok ? 'check-circle' : 'x-circle', 'h-5 w-5') ?></span>
          <span class="flex-1 text-sm font-medium text-ink"><?= e($label) ?></span>
          <span class="text-xs <?= $ok ? 'text-ink-faint' : 'text-danger' ?>"><?= e($detail) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="card mt-5">
    <div class="card-head"><h2 class="card-title">2. Database connection</h2></div>
    <div class="card-pad">
      <?php if ($dbOk): ?>
        <p class="flex items-center gap-2 text-sm font-medium text-success">
          <?= icon('check-circle', 'h-5 w-5') ?>Connected to <code class="rounded bg-black/5 px-1.5 py-0.5 text-xs"><?= e($dbName) ?></code> on <?= e($dbHost) ?>.
        </p>
      <?php else: ?>
        <p class="flex items-start gap-2 text-sm font-medium text-danger">
          <?= icon('x-circle', 'h-5 w-5 shrink-0') ?><span>Could not connect. <?= e($dbError ?: 'Check your credentials.') ?></span>
        </p>
        <div class="mt-4 rounded-card bg-canvas p-4 text-sm text-ink-soft">
          <p class="font-semibold text-ink">Fix it in <code>.env</code> at the project root:</p>
          <pre class="mt-2 overflow-x-auto rounded bg-white p-3 text-xs leading-relaxed"><code>DB_HOST=localhost
DB_NAME=your_database
DB_USER=your_user
DB_PASS="your_password"</code></pre>
          <?php if (!$envExists): ?>
            <p class="mt-3 font-medium text-danger">No <code>.env</code> file was found. Copy <code>.env.example</code> to <code>.env</code> and fill in the four values above.</p>
          <?php endif; ?>
          <p class="mt-3">On cPanel the database is created under <strong>MySQL&reg; Databases</strong>, and both names carry your account prefix
            (for example <code>shooting_dwms</code> and <code>shooting_dwmsuser</code>). Remember to add the user to the database with
            <strong>ALL PRIVILEGES</strong>.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card mt-5">
    <div class="card-head"><h2 class="card-title">3. Install tables and demo data</h2></div>
    <div class="card-pad">
      <?php if ($installed): ?>
        <p class="flex items-center gap-2 text-sm font-medium text-success"><?= icon('check-circle', 'h-5 w-5') ?>Installed. The portal is ready.</p>
        <div class="mt-4 rounded-card border border-line p-4">
          <p class="text-sm font-semibold text-ink">Default sign-ins — change these before going live</p>
          <ul class="mt-2 space-y-1 text-sm text-ink-soft">
            <li><strong>Super admin:</strong> admin@dwms.local / Admin@12345 — <a class="link" href="<?= url('/official/login') ?>">Officials login</a></li>
            <li><strong>Employer:</strong> hr@technova.in / Employer@123 — <a class="link" href="<?= url('/employer/login') ?>">Employer login</a></li>
            <li><strong>Job seeker:</strong> seeker@dwms.local / Seeker@123 — <a class="link" href="<?= url('/login') ?>">Job seeker login</a></li>
          </ul>
        </div>
        <a href="<?= url('/') ?>" class="btn-primary mt-5">Go to the portal <?= icon('arrow-right', 'h-4 w-4') ?></a>
      <?php elseif ($dbOk): ?>
        <p class="text-sm text-ink-soft">This creates every table and loads demo offices, roles, categories, employers, jobs, skilling programmes and career services.</p>
        <form method="post" action="<?= url('/setup/install') ?>" class="mt-4">
          <?= csrf_field() ?>
          <button type="submit" class="btn-primary btn-lg"><?= icon('download', 'h-4 w-4') ?>Install now</button>
        </form>
      <?php else: ?>
        <p class="text-sm text-ink-faint">Fix the database connection above first.</p>
      <?php endif; ?>
    </div>
  </div>

  <p class="mt-6 text-center text-xs text-ink-faint">
    After installation, remove the two <code>/setup</code> routes from <code>app/routes.php</code>,
    and set <code>APP_DEBUG=false</code> and <code>MAIL_DEMO_OTP=false</code> in <code>.env</code>.
  </p>
</div>
