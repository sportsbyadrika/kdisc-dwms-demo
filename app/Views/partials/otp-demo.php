<?php /** @var string|null $demoCode */ if (empty($demoCode)) { return; } ?>
<div class="mt-4 flex items-start gap-3 rounded-card border border-warning/30 bg-warning/5 px-4 py-3">
  <span class="mt-0.5 shrink-0 text-warning"><?= icon('info', 'h-4 w-4') ?></span>
  <div class="text-sm">
    <p class="font-semibold text-warning">Demo mode</p>
    <p class="text-ink-soft">Mail delivery is not configured on this server, so the one-time password is shown here:
      <span class="ml-1 rounded bg-white px-2 py-0.5 font-mono text-base font-bold tracking-[0.3em] text-ink"><?= e($demoCode) ?></span>
    </p>
    <p class="mt-1 text-xs text-ink-faint">Set <code>mail.demo_otp</code> to <code>false</code> in <code>app/config.php</code> once e-mail works.</p>
  </div>
</div>
