<template>
  <div
    class="fixed z-[100] flex flex-col gap-2 p-4 md:max-w-[520px]"
    :class="positionClasses"
  >
    <TransitionGroup
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-for="toast in toasts" :key="toast.id" class="mb-2">
        <Toast
          :title="toast.title"
          :description="toast.description"
          :variant="toast.variant"
          :onClose="() => removeToast(toast.id)"
        />
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Toast from './Toast.vue';

interface ToastMessage {
  id: string;
  title?: string;
  description: string;
  variant: 'default' | 'success' | 'error' | 'warning' | 'info';
  duration?: number;
}

const props = defineProps<{
  position?: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left' | 'top-center' | 'bottom-center';
}>();

const positionClasses = {
  'top-right': 'top-0 right-0 items-end sm:items-end flex-col',
  'top-left': 'top-0 left-0 items-start sm:items-start flex-col',
  'bottom-right': 'bottom-0 right-0 items-end sm:items-end flex-col-reverse',
  'bottom-left': 'bottom-0 left-0 items-start sm:items-start flex-col-reverse',
  'top-center': 'top-0 inset-x-0 items-center sm:items-center flex-col',
  'bottom-center': 'bottom-0 inset-x-0 items-center sm:items-center flex-col-reverse',
}[props.position || 'top-right'];

const toasts = ref<ToastMessage[]>([]);
const page = usePage();

const addToast = (toast: Omit<ToastMessage, 'id'>) => {
  const id = Math.random().toString(36).substring(2, 9);
  const duration = toast.duration || 5000; 

  toasts.value.push({ ...toast, id });

  if (duration > 0) {
    setTimeout(() => {
      removeToast(id);
    }, duration);
  }
};

const removeToast = (id: string) => {
  const index = toasts.value.findIndex((toast) => toast.id === id);
  if (index !== -1) {
    toasts.value.splice(index, 1);
  }
};

watch(
  () => page.props.flash,
  (newFlash) => {
    if (newFlash?.success) {
      addToast({
        title: 'Success',
        description: newFlash.success,
        variant: 'success',
      });
    }

    if (newFlash?.error) {
      addToast({
        title: 'Error',
        description: newFlash.error,
        variant: 'error',
      });
    }

    if (newFlash?.warning) {
      addToast({
        title: 'Warning',
        description: newFlash.warning,
        variant: 'warning',
      });
    }

    if (newFlash?.info) {
      addToast({
        title: 'Info',
        description: newFlash.info,
        variant: 'info',
      });
    }
  },
  { immediate: true, deep: true }
);

// Expose the addToast and removeToast methods
defineExpose({
  addToast,
  removeToast,
});
</script>
