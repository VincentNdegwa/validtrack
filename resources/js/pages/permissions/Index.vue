<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Permission } from '@/types/models';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Can from '@/components/auth/Can.vue';

interface Props {
    permissions?: Permission[];
}

const props = defineProps<Props>();
const permissions = ref(props.permissions || []);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Permissions',
        href: '/permissions',
    },
];
</script>

<template>
    <Head title="Permissions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Permissions</h1>
                    <p class="text-muted-foreground">Available permissions for assignment to roles</p>
                </div>
                <div class="flex gap-2">
                    <Can permission="roles-view">
                        <Link href="/roles">
                            <Button variant="outline" class="mr-2">Manage Roles</Button>
                        </Link>
                    </Can>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-muted/50 text-xs uppercase">
                            <tr>
                                <th scope="col" class="px-6 py-3">Permission Name</th>
                                <th scope="col" class="px-6 py-3">Display Name</th>
                                <th scope="col" class="px-6 py-3">Description</th>
                                <th scope="col" class="px-6 py-3">Scope</th>
                                <th scope="col" class="px-6 py-3">Roles</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="permissions.length === 0">
                                <td colspan="6" class="px-6 py-4 text-center">No permissions found</td>
                            </tr>
                            <tr v-for="permission in permissions" :key="permission.id" class="border-t border-border hover:bg-muted/30">
                                <td class="px-6 py-4 font-medium">{{ permission.name }}</td>
                                <td class="px-6 py-4">{{ permission.display_name || '-' }}</td>
                                <td class="px-6 py-4">
                                    <span class="line-clamp-1">{{ permission.description || '-' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-1 text-xs"
                                        :class="permission.company_id === null ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'"
                                    >
                                        {{ permission.company_id === null ? 'Global' : 'Company' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ permission.roles_count || 0 }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-muted-foreground text-xs italic">
                                        Managed by system
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
