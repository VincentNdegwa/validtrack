<template>
  <div
    :class="[
      'pointer-events-auto flex w-full items-center justify-between rounded-md border p-4 shadow-lg',
      variantStyles[variant],
    ]"
    data-state="open"
  >
    <div class="flex items-center gap-3">
      <div v-if="variant !== 'default'" class="flex-shrink-0">
        <CheckCircle2Icon v-if="variant === 'success'" class="h-5 w-5" />
        <AlertCircleIcon v-if="variant === 'error'" class="h-5 w-5" />
        <InfoIcon v-if="variant === 'info'" class="h-5 w-5" />
        <AlertTriangleIcon v-if="variant === 'warning'" class="h-5 w-5" />
      </div>
      <div class="flex flex-col gap-1">
        <div v-if="title" class="text-sm font-semibold">{{ title }}</div>
        <div v-if="description" class="text-sm opacity-90">{{ description }}</div>
      </div>
    </div>
    <button
      type="button"
      class="ml-4 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-foreground/50 hover:text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-ring"
      @click="onClose"
    >
      <XIcon class="h-4 w-4" />
      <span class="sr-only">Close</span>
    </button>
  </div>
</template>

<script setup lang="ts">
import { XIcon, CheckCircle2Icon, AlertCircleIcon, InfoIcon, AlertTriangleIcon } from 'lucide-vue-next';

interface Props {
  title?: string;
  description?: string;
  variant?: 'default' | 'success' | 'error' | 'warning' | 'info';
  onClose?: () => void;
}

const props = withDefaults(defineProps<Props>(), {
  title: undefined,
  description: undefined,
  variant: 'default',
  onClose: () => {},
});

const variantStyles = {
  default: 'border-border bg-background text-foreground',
  success: 'border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-300',
  error: 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-300',
  warning: 'border-yellow-200 bg-yellow-50 text-yellow-800 dark:border-yellow-800 dark:bg-yellow-950 dark:text-yellow-300',
  info: 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-800 dark:bg-blue-950 dark:text-blue-300',
};
</script>
