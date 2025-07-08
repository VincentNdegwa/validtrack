<script setup lang="ts">

import { ref, onMounted, onUnmounted } from 'vue';
import { MoreVertical } from 'lucide-vue-next';

const props = defineProps<{
  itemId: string | number;
}>();

const emit = defineEmits<{
  (e: 'select', action: string, itemId: string | number): void;
}>();

const isOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const toggleMenu = (event: Event) => {
  event.stopPropagation();
  isOpen.value = !isOpen.value;
};

const closeMenu = () => {
  isOpen.value = false;
};

const handleAction = (action: string, event: Event) => {
  event.stopPropagation();
  event.preventDefault();
  closeMenu();
  emit('select', action, props.itemId);
};

const handleClickOutside = (event: MouseEvent) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    closeMenu();
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
  <!-- Main dropdown container with click-outside detection -->
  <div class="relative flex justify-center" ref="dropdownRef">
    <!-- Trigger button with three dots icon -->
    <button 
      @click.stop="toggleMenu"
      class="dropdown-menu-trigger h-8 w-8 flex items-center justify-center rounded-full hover:bg-muted/50"
    >
      <MoreVertical class="h-4 w-4" />
      <span class="sr-only">Open menu</span>
    </button>
    
    <!-- Dropdown menu, positioned below and to the right -->
    <div 
      v-if="isOpen"
      class="dropdown-menu absolute top-full right-0 z-50 mt-1 w-48 overflow-hidden rounded-md border border-border bg-card shadow-md"
    >
      <div class="flex flex-col py-1">
        <!-- Menu items slot with helper functions passed as slot props -->
        <!-- Use these in your implementation: -->
        <!-- - handleAction: call this with the action name and event -->
        <!-- - close: call this to manually close the menu -->
        <slot name="menu-items" :close="closeMenu" :handle-action="handleAction"></slot>
      </div>
    </div>
  </div>
</template>
