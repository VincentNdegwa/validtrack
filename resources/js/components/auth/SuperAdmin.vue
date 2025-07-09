<script setup lang="ts">
import { type User } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed<User | undefined>(() => page.props.auth?.user);

const isSuperAdmin = computed(() => {
    if (!user.value || !user.value.roles) return false;
    return user.value.roles.some((role) => role.name === 'super-admin');
});
</script>

<template>
    <template v-if="isSuperAdmin">
        <slot />
    </template>
    <template v-else>
        <slot name="fallback" />
    </template>
</template>
