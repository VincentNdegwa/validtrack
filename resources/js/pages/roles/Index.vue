<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Role } from '@/types/models';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Can from '@/components/auth/Can.vue';

interface Props {
    roles?: Role[];
}

const props = defineProps<Props>();
const roles = ref(props.roles || []);
const showDeleteDialog = ref(false);
const roleToDelete = ref<Role | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Roles',
        href: '/roles',
    },
];

const confirmDelete = (role: Role) => {
    roleToDelete.value = role;
    showDeleteDialog.value = true;
};

const cancelDelete = () => {
    showDeleteDialog.value = false;
    roleToDelete.value = null;
};

const deleteRole = () => {
    if (roleToDelete.value) {
        router.delete(`/roles/${roleToDelete.value.slug}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                roleToDelete.value = null;
            }
        });
    }
};
</script>

<template>
    <Head title="Roles" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Roles</h1>
                    <p class="text-muted-foreground">Manage user roles and permissions</p>
                </div>
                <div class="flex gap-2">
                    <Can permission="roles-view">
                        <Link href="/permissions">
                            <Button variant="outline" class="mr-2">Manage Permissions</Button>
                        </Link>
                    </Can>
                    <Can permission="roles-create">
                        <Link href="/roles/create">
                            <Button class="bg-primary text-primary-foreground hover:bg-primary/90">Add Role</Button>
                        </Link>
                    </Can>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-muted/50 text-xs uppercase">
                            <tr>
                                <th scope="col" class="px-6 py-3">Role Name</th>
                                <th scope="col" class="px-6 py-3">Display Name</th>
                                <th scope="col" class="px-6 py-3">Users</th>
                                <th scope="col" class="px-6 py-3">Permissions</th>
                                <th scope="col" class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="roles.length === 0">
                                <td colspan="5" class="px-6 py-4 text-center">No roles found</td>
                            </tr>
                            <tr v-for="role in roles" :key="role.id" class="border-t border-border hover:bg-muted/30">
                                <td class="px-6 py-4 font-medium">{{ role.name }}</td>
                                <td class="px-6 py-4">{{ role.display_name || '-' }}</td>
                                <td class="px-6 py-4">{{ role.users_count || 0 }}</td>
                                <td class="px-6 py-4">{{ role.permissions_count || 0 }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <Can permission="roles-view">
                                            <Link :href="`/roles/${role.slug}`" class="text-blue-600 hover:underline">View</Link>
                                        </Can>
                                        <Can permission="roles-edit">
                                            <Link :href="`/roles/${role.slug}/edit`" class="text-amber-600 hover:underline">Edit</Link>
                                        </Can>
                                        <Can permission="roles-delete">
                                            <button @click="confirmDelete(role)" class="text-red-600 hover:underline">Delete</button>
                                        </Can>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Dialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Role</DialogTitle>
                </DialogHeader>
                <div class="py-4">
                    <p>
                        Are you sure you want to delete role <span class="font-semibold">{{ roleToDelete?.name }}</span>? This
                        action cannot be undone.
                    </p>
                </div>
                <div class="flex justify-end space-x-2">
                    <Button variant="outline" @click="cancelDelete">Cancel</Button>
                    <Button variant="destructive" @click="deleteRole">Delete</Button>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
