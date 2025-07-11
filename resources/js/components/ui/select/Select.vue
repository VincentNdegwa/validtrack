<template>
  <select
    class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
    v-bind="$attrs"
  >
    <slot></slot>
  </select>
</template>

<script setup lang="ts">
defineProps<{
  modelValue?: string | number;
}>();

defineEmits<{
  (e: 'update:modelValue', value: string): void;
}>();
</script>
