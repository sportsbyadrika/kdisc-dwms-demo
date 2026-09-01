<div x-data class="pointer-events-none fixed bottom-4 right-4 z-50 flex w-80 max-w-[calc(100vw-2rem)] flex-col gap-2">
  <template x-for="t in $store.ui.toasts" :key="t.id">
    <div x-transition
         class="pointer-events-auto flex items-start gap-3 rounded-card bg-ink px-4 py-3 text-sm font-medium text-white shadow-pop"
         :class="t.type === 'error' ? '!bg-danger' : (t.type === 'info' ? '!bg-brand-700' : '')">
      <p class="flex-1" x-text="t.message"></p>
      <button type="button" class="opacity-70 hover:opacity-100" @click="$store.ui.dismiss(t.id)" aria-label="Dismiss">
        <?= icon('x', 'h-4 w-4') ?>
      </button>
    </div>
  </template>
</div>
