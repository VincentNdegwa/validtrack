<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { AlertTriangle } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from './ui/button';

const page = usePage();
const isImpersonating = computed(() => page.props.impersonating as boolean);

const stopImpersonation = () => {
    router.post('/impersonate/stop');
};
</script>

<template>
    <div v-if="isImpersonating" class="absolute top-5 left-3/5 z-50">
        <div class="container flex items-center justify-between gap-2 px-4 py-2">
            <div class="flex items-center">
                <AlertTriangle class="mr-2 h-4 w-4 text-amber-700 dark:text-amber-500" />
                <span class="text-sm text-amber-800 dark:text-amber-300"> You are currently impersonating another user </span>
            </div>
            <Button variant="outline" size="sm" @click="stopImpersonation" class="text-xs"> Return to your account </Button>
        </div>
    </div>
</template>
