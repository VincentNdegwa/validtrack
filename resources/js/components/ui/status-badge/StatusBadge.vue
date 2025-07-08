<script setup lang="ts">
defineProps<{
  active: boolean;
  activeText?: string;
  inactiveText?: string;
}>();
</script>

<template>
  <span
    class="inline-flex items-center rounded-full px-2 py-1 text-xs"
    :class="active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
  >
    {{ active ? (activeText || 'Active') : (inactiveText || 'Inactive') }}
  </span>
</template>
