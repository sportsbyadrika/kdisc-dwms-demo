/**
 * DWMS 2.0 — front-end behaviour.
 * Alpine.js supplies the reactivity; this file registers the shared stores,
 * components and helpers used across the portal.
 */
(function () {
  'use strict';

  const meta = (name) => {
    const el = document.querySelector('meta[name="' + name + '"]');
    return el ? el.getAttribute('content') : '';
  };

  window.DWMS = {
    base: meta('dwms-base') || '',
    csrf: meta('csrf-token') || '',
    url(path) {
      return this.base + (path.startsWith('/') ? path : '/' + path);
    },
    async post(path, data) {
      const body = data instanceof FormData ? data : new URLSearchParams(data || {});
      if (body instanceof URLSearchParams) body.append('_csrf', this.csrf);
      else body.append('_csrf', this.csrf);
      const res = await fetch(this.url(path), {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': this.csrf },
        body,
      });
      const text = await res.text();
      try { return JSON.parse(text); } catch (e) { return { ok: false, message: 'Unexpected server response.' }; }
    },
  };

  document.addEventListener('alpine:init', () => {
    /* -------------------------------------------------- global UI store */
    Alpine.store('ui', {
      mobileNav: false,
      loginModal: false,
      loginContext: null,   // { jobId, title } when triggered from "Apply"
      toasts: [],
      openLogin(context) {
        this.loginContext = context || null;
        this.loginModal = true;
        document.body.classList.add('overflow-hidden');
      },
      closeLogin() {
        this.loginModal = false;
        document.body.classList.remove('overflow-hidden');
      },
      toast(message, type = 'success', ttl = 4200) {
        const id = Date.now() + Math.random();
        this.toasts.push({ id, message, type });
        setTimeout(() => this.dismiss(id), ttl);
      },
      dismiss(id) {
        this.toasts = this.toasts.filter((t) => t.id !== id);
      },
    });

    /* --------------------------------------------------- wizard stepper */
    Alpine.data('wizard', (total, start = 1) => ({
      total,
      step: start,
      next() { if (this.step < this.total) { this.step++; this.top(); } },
      prev() { if (this.step > 1) { this.step--; this.top(); } },
      go(n) { this.step = n; this.top(); },
      isDone(n) { return n < this.step; },
      isNow(n) { return n === this.step; },
      top() { window.scrollTo({ top: 0, behavior: 'smooth' }); },
    }));

    /* ------------------------------------------------- OTP entry helper */
    Alpine.data('otpBox', (endpoint, identifier) => ({
      sending: false,
      sent: false,
      seconds: 0,
      code: '',
      demoCode: '',
      message: '',
      error: '',
      timer: null,
      async send() {
        this.sending = true;
        this.error = '';
        this.message = '';
        const res = await window.DWMS.post(endpoint, { identifier });
        this.sending = false;
        if (res.ok) {
          this.sent = true;
          this.message = res.message || 'A one-time password has been sent.';
          this.demoCode = res.demo_code || '';
          this.countdown(res.retry_after || 60);
        } else {
          this.error = res.message || 'Could not send the one-time password.';
        }
      },
      countdown(s) {
        this.seconds = s;
        clearInterval(this.timer);
        this.timer = setInterval(() => {
          this.seconds--;
          if (this.seconds <= 0) clearInterval(this.timer);
        }, 1000);
      },
    }));

    /* ----------------------------------------- image / file preview box */
    Alpine.data('filePicker', (initial = '', accept = 'image') => ({
      preview: initial,
      fileName: '',
      pick(event) {
        const file = event.target.files[0];
        if (!file) return;
        this.fileName = file.name;
        if (accept === 'image' && file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = (e) => (this.preview = e.target.result);
          reader.readAsDataURL(file);
        }
      },
      clear(input) {
        this.preview = '';
        this.fileName = '';
        if (input) input.value = '';
      },
    }));

    /* ------------------------------------------- filter side panel (search) */
    Alpine.data('filterPanel', () => ({
      open: false,
      groups: {},
      toggleGroup(key) { this.groups[key] = !(this.groups[key] ?? true); },
      isOpen(key) { return this.groups[key] ?? true; },
      submit() { this.$refs.form && this.$refs.form.submit(); },
      reset() { window.location = window.location.pathname; },
    }));

    /* ------------------------------------------------ repeatable rows UI */
    Alpine.data('repeater', (rows = []) => ({
      rows: rows.length ? rows : [{}],
      add() { this.rows.push({}); },
      remove(i) { this.rows.splice(i, 1); if (!this.rows.length) this.rows.push({}); },
    }));

    /* ------------------------------------------------- save / wishlist */
    Alpine.data('saveJob', (jobId, saved, loggedIn, title) => ({
      jobId, saved, busy: false,
      async toggle() {
        if (!loggedIn) {
          Alpine.store('ui').openLogin({ jobId, title, intent: 'save' });
          return;
        }
        this.busy = true;
        const res = await window.DWMS.post('/jobs/save/' + this.jobId, {});
        this.busy = false;
        if (res.ok) {
          this.saved = res.saved;
          Alpine.store('ui').toast(res.message, 'success');
        } else {
          Alpine.store('ui').toast(res.message || 'Something went wrong.', 'error');
        }
      },
    }));
  });

  /* -------------------------------------- confirm before destructive posts */
  document.addEventListener('submit', (e) => {
    const form = e.target;
    const msg = form.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) {
      e.preventDefault();
      return;
    }
    const btn = form.querySelector('[type="submit"]:not([data-no-lock])');
    if (btn && !form.hasAttribute('data-no-lock')) {
      setTimeout(() => {
        btn.disabled = true;
        btn.classList.add('opacity-60');
      }, 0);
    }
  });

  document.addEventListener('click', (e) => {
    const el = e.target.closest('[data-confirm-link]');
    if (el && !window.confirm(el.getAttribute('data-confirm-link'))) {
      e.preventDefault();
    }
  });
})();
