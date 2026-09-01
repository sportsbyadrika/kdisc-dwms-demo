<?php
use App\Core\Auth;
// Signed-in seekers never need this modal.
if (Auth::check('seeker')) {
    return;
}
?>
<div x-data x-show="$store.ui.loginModal" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center" role="dialog" aria-modal="true" aria-labelledby="login-modal-title">
  <div x-show="$store.ui.loginModal" x-transition.opacity class="absolute inset-0 bg-black/50" @click="$store.ui.closeLogin()"></div>

  <div x-show="$store.ui.loginModal" x-transition
       class="relative w-full max-w-md rounded-t-card bg-white shadow-pop sm:rounded-card"
       @keydown.escape.window="$store.ui.closeLogin()">
    <button type="button" class="absolute right-3 top-3 rounded-full p-1.5 text-ink-faint hover:bg-black/5 hover:text-ink"
            @click="$store.ui.closeLogin()" aria-label="Close"><?= icon('x') ?></button>

    <div class="px-6 pb-6 pt-7">
      <h2 id="login-modal-title" class="text-xl font-semibold text-ink">Sign in to continue</h2>
      <p class="mt-1 text-sm text-ink-soft"
         x-text="$store.ui.loginContext && $store.ui.loginContext.title
                 ? 'Sign in to apply for ' + $store.ui.loginContext.title + '. We have saved it to your list in the meantime.'
                 : 'Sign in to your job seeker account to continue.'"></p>

      <form method="post" action="<?= url('/login') ?>" class="mt-5 space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="wishlist_job_id" :value="$store.ui.loginContext ? ($store.ui.loginContext.jobId || '') : ''">
        <input type="hidden" name="intended" :value="$store.ui.loginContext ? ($store.ui.loginContext.redirect || '') : ''">

        <div>
          <label class="label" for="modal-email">E-mail address</label>
          <input id="modal-email" name="email" type="email" required autocomplete="username" class="field" placeholder="you@example.com">
        </div>
        <div>
          <label class="label" for="modal-password">Password</label>
          <input id="modal-password" name="password" type="password" required autocomplete="current-password" class="field" placeholder="Your password">
        </div>
        <div class="flex items-center justify-between text-sm">
          <label class="flex items-center gap-2 text-ink-soft">
            <input type="checkbox" name="remember" value="1" class="checkbox"> Remember me
          </label>
          <a href="<?= url('/forgot-password') ?>" class="link">Forgot password?</a>
        </div>
        <button type="submit" class="btn-primary btn-block btn-lg">Sign in</button>
      </form>

      <div class="my-5 flex items-center gap-3 text-xs uppercase tracking-wide text-ink-faint">
        <span class="h-px flex-1 bg-line"></span>New to DWMS?<span class="h-px flex-1 bg-line"></span>
      </div>

      <a href="<?= url('/register') ?>" class="btn-outline btn-block">
        <?= icon('plus', 'h-4 w-4') ?> Create a job seeker account
      </a>
      <p class="mt-4 text-center text-xs text-ink-faint">
        Hiring instead? <a href="<?= url('/employer/login') ?>" class="link">Employer login</a>
      </p>
    </div>
  </div>
</div>
