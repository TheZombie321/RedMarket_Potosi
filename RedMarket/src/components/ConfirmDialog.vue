<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
  open: boolean
  title: string
  message: string
  confirmText?: string
  cancelText?: string
  variant?: 'danger' | 'warning'
}>()

const emit = defineEmits<{
  confirm: []
  cancel: []
}>()
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="open" class="fixed inset-0 z-modal flex items-center justify-center bg-black/40" role="dialog" aria-modal="true" :aria-label="title" @keydown.escape="emit('cancel')">
        <div class="modal-panel bg-blanco-mercado rounded-xl shadow-floating-high max-w-sm w-full mx-4 overflow-hidden">
          <div class="p-6">
            <h3 class="text-lg font-bold text-ink mb-2">{{ title }}</h3>
            <p class="text-sm text-ink-muted">{{ message }}</p>
          </div>
          <div class="flex justify-end gap-2 px-6 pb-6">
            <button @click="emit('cancel')" class="px-4 py-2 text-sm rounded border border-platform-edge text-ink hover:bg-potos-stone cursor-pointer bg-blanco-mercado font-medium min-h-[44px]">
              {{ cancelText || 'Cancelar' }}
            </button>
            <button @click="emit('confirm')" class="px-4 py-2 text-sm rounded text-white font-semibold cursor-pointer border-none min-h-[44px]" :class="variant === 'warning' ? 'bg-warning hover:bg-warning-dark' : 'bg-rojo-mercado-dark hover:bg-rojo-mercado-darker'">
              {{ confirmText || 'Confirmar' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
