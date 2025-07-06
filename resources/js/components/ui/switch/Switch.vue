<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
  modelValue: boolean;
  disabled?: boolean;
  id?: string;
  class?: string;
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false,
  disabled: false,
  id: undefined,
  class: ''
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void;
}>();

const toggle = () => {
  if (!props.disabled) {
    emit('update:modelValue', !props.modelValue);
  }
};

const switchClasses = computed(() => {
  return cn(
    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary',
    props.modelValue ? 'bg-primary' : 'bg-gray-200 dark:bg-gray-700',
    props.disabled ? 'opacity-50 cursor-not-allowed' : '',
    props.class
  );
});

const thumbClasses = computed(() => {
  return cn(
    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
    props.modelValue ? 'translate-x-5' : 'translate-x-0'
  );
});
</script>

<template>
  <button 
    type="button" 
    role="switch" 
    :id="id"
    :aria-checked="modelValue" 
    :disabled="disabled"
    :class="switchClasses" 
    @click="toggle"
  >
    <span class="sr-only">Toggle</span>
    <span :class="thumbClasses"></span>
  </button>
</template>
