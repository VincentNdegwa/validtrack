<script setup lang="ts">
import { type Role } from '@/types/models';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface AuthUser {
    id: number;
    name: string;
    email: string;
    roles?: Role[];
    permissions?: string[];
}

interface Props {
    permission?: string | string[];
    role?: string | string[];
    any?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    permission: undefined,
    role: undefined,
    any: false,
});
const page = usePage();

const user = computed<AuthUser | undefined>(() => page.props.auth?.user);

const hasAccess = computed(() => {
    if (!user.value) return false;

    if (!props.permission && !props.role) return true;

    // Check permissions
    if (props.permission) {
        if (!user.value.permissions) return false;

        const permissions = Array.isArray(props.permission) ? props.permission : [props.permission];

        if (props.any) {
            return permissions.some((p) => user.value?.permissions?.includes(p));
        } else {
            return permissions.every((p) => user.value?.permissions?.includes(p));
        }
    }

    // Check roles
    if (props.role) {
        if (!user.value.roles) return false;

        const roles = Array.isArray(props.role) ? props.role : [props.role];

        if (props.any) {
            return roles.some((r) => user.value?.roles?.some((userRole) => userRole.name === r || userRole.id.toString() === r));
        } else {
            return roles.every((r) => user.value?.roles?.some((userRole) => userRole.name === r || userRole.id.toString() === r));
        }
    }

    return false;
});
</script>

<template>
    <template v-if="hasAccess">
        <slot />
    </template>
    <template v-else>
        <slot name="fallback" />
    </template>
</template>
