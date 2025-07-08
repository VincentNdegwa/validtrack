<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    text: string;
    icon: any;
    action?: string;
    variant?: 'default' | 'destructive';
}>();

const emit = defineEmits<{
    (e: 'click', event: MouseEvent): void;
}>();

const buttonClass = computed(() => {
    return props.variant === 'destructive' 
        ? 'text-red-600' 
        : '';
});
</script>

<template>
    <button 
        @click="(e) => emit('click', e)" 
        class="flex items-center px-2 py-1.5 text-sm hover:bg-muted text-left w-full"
        :class="buttonClass"
    >
        <component :is="icon" class="h-4 w-4 mr-2" />
        <span>{{ text }}</span>
    </button>
</template>
