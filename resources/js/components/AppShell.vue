<script setup lang="ts">
import ImpersonationBanner from '@/components/ImpersonationBanner.vue';
import { SidebarProvider } from '@/components/ui/sidebar';
import { ToastProvider } from '@/components/ui/toast';
import { setToastProvider } from '@/composables/useToast';
import { onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const isOpen = usePage().props.sidebarOpen;
const toastProviderRef = ref<InstanceType<typeof ToastProvider> | null>(null);

// Register the toast provider globally
onMounted(() => {
    if (toastProviderRef.value) {
        setToastProvider(toastProviderRef.value);
    }
});
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <ToastProvider ref="toastProviderRef" position="top-right" />
        <ImpersonationBanner />
        <slot />
    </div>
    <SidebarProvider v-else :default-open="isOpen">
        <ToastProvider ref="toastProviderRef" position="top-right" />
        <ImpersonationBanner />
        <slot />
    </SidebarProvider>
</template>
