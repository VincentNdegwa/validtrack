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
const triggerRef = ref<HTMLElement | null>(null);
const dropdownPosition = ref({ top: '0px', left: '0px' });

const toggleMenu = (event: Event) => {
  event.stopPropagation();
  isOpen.value = !isOpen.value;
  
  // Update dropdown position when opening
  if (isOpen.value) {
    updateDropdownPosition();
  }
};

// Calculate dropdown position based on trigger button
const updateDropdownPosition = () => {
  if (!triggerRef.value) return;
  
  const rect = triggerRef.value.getBoundingClientRect();
  const windowWidth = window.innerWidth;
  const menuWidth = 192; 
  let left = rect.right;
  
  // Check if the menu would overflow to the right
  if (left + menuWidth > windowWidth) {
    // If it would overflow, position to the left side instead
    left = rect.left - menuWidth;
    
    // If it would still overflow to the left, center it under the trigger
    if (left < 0) {
      left = rect.left - (menuWidth - rect.width) / 2;
      
      // Ensure it doesn't overflow either side
      if (left < 5) left = 5;
      if (left + menuWidth > windowWidth - 5) left = windowWidth - menuWidth - 5;
    }
  }
  
  // Calculate final position
  dropdownPosition.value = {
    top: `${rect.bottom + window.scrollY + 4}px`, // Add small gap
    left: `${left + window.scrollX}px`,
  };
};

// Function to return position styles for the dropdown
const getDropdownPosition = () => {
  return dropdownPosition.value;
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

// Handle window resize to reposition dropdown
const handleResize = () => {
  if (isOpen.value) {
    updateDropdownPosition();
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  window.addEventListener('resize', handleResize);
  window.addEventListener('scroll', handleResize);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  window.removeEventListener('resize', handleResize);
  window.removeEventListener('scroll', handleResize);
});
</script>

<template>
  <!-- Main dropdown container with click-outside detection -->
  <div class="relative flex justify-center" ref="dropdownRef">
    <!-- Trigger button with three dots icon -->
    <button 
      ref="triggerRef"
      @click.stop="toggleMenu"
      class="dropdown-menu-trigger h-8 w-8 flex items-center justify-center rounded-full hover:bg-muted/50"
    >
      <MoreVertical class="h-4 w-4" />
      <span class="sr-only">Open menu</span>
    </button>
    
    <!-- Dropdown menu, positioned adjacent to the trigger using teleport to body -->
    <teleport to="body">
      <transition
        name="dropdown-fade"
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div 
          v-if="isOpen"
          class="dropdown-menu fixed z-50 w-48 overflow-hidden rounded-md border border-border bg-card shadow-md origin-top-right"
          :style="getDropdownPosition()"
        >
          <div class="flex flex-col py-1">
            <slot name="menu-items" :close="closeMenu" :handle-action="handleAction"></slot>
          </div>
        </div>
      </transition>
    </teleport>
  </div>
</template>
