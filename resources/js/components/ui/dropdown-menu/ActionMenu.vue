<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, inject, type Ref } from 'vue';
import { MoreVertical } from 'lucide-vue-next';

const props = defineProps<{
  itemId: string | number;
}>();

const emit = defineEmits<{
  (e: 'select', action: string, itemId: string | number): void;
}>();

const activeMenu = inject<Ref<string | number | null>>('activeMenuId');
if (!activeMenu) {
  throw new Error('ActionMenu must be used within AppLayout');
}

const dropdownRef = ref<HTMLElement | null>(null);
const triggerRef = ref<HTMLElement | null>(null);
const dropdownPosition = ref({ top: '0px', left: '0px' });

const isOpen = computed(() => activeMenu.value === props.itemId);

const toggleMenu = (event: Event) => {
  event.stopPropagation();
  if (isOpen.value) {
    activeMenu.value = null;
  } else {
    activeMenu.value = props.itemId;
    updateDropdownPosition();
  }
};

const updateDropdownPosition = () => {
  if (!triggerRef.value) return;
  
  const rect = triggerRef.value.getBoundingClientRect();
  const menuWidth = 192;
  
  let left = rect.right + 4; // Small gap
  
  if (left + menuWidth > window.innerWidth) {
    left = rect.left - menuWidth - 4;
  }
  
  const buttonCenter = rect.top + (rect.height / 2);
  
  dropdownPosition.value = {
    top: `${buttonCenter}px`,
    left: `${left}px`,
  };
};

const getDropdownPosition = () => {
  return dropdownPosition.value;
};

const closeMenu = () => {
  activeMenu.value = null;
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
          class="dropdown-menu fixed z-50 w-48 overflow-hidden rounded-md border border-border bg-card shadow-md transform -translate-y-1/2"
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
