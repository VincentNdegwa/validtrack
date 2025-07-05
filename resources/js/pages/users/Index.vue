<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type User, type Role } from '@/types/models';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Can from '@/components/auth/Can.vue';

interface Props {
    users?: User[];
    roles?: Role[];
}

const props = defineProps<Props>();
const users = ref(props.users || []);
const roles = ref(props.roles || []);
const showDeleteDialog = ref(false);
const userToDelete = ref<User | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Users',
        href: '/users',
    },
];

const getRoleNames = (user: User) => {
    if (!user.roles || user.roles.length === 0) {
        return 'No roles assigned';
    }
    return user.roles.map(role => role.name).join(', ');
};

const confirmDelete = (user: User) => {
    userToDelete.value = user;
    showDeleteDialog.value = true;
};

const cancelDelete = () => {
    showDeleteDialog.value = false;
    userToDelete.value = null;
};

const deleteUser = () => {
    if (userToDelete.value) {
        router.delete(`/users/${userToDelete.value.slug}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                userToDelete.value = null;
            }
        });
    }
};
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Users</h1>
                    <p class="text-muted-foreground">Manage users in your organization</p>
                </div>
                <div class="flex gap-2">
                    <Can permission="roles-view">
                        <Link href="/roles">
                            <Button variant="outline" class="mr-2">Manage Roles</Button>
                        </Link>
                    </Can>
                    <Can permission="roles-view">
                        <Link href="/permissions">
                            <Button variant="outline" class="mr-2">Manage Permissions</Button>
                        </Link>
                    </Can>
                    <Can permission="users-create">
                        <Link href="/users/create">
                            <Button class="bg-primary text-primary-foreground hover:bg-primary/90">Add User</Button>
                        </Link>
                    </Can>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-muted/50 text-xs uppercase">
                            <tr>
                                <th scope="col" class="px-6 py-3">Name</th>
                                <th scope="col" class="px-6 py-3">Email</th>
                                <th scope="col" class="px-6 py-3">Roles</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="users.length === 0">
                                <td colspan="5" class="px-6 py-4 text-center">No users found</td>
                            </tr>
                            <tr v-for="user in users" :key="user.id" class="border-t border-border hover:bg-muted/30">
                                <td class="px-6 py-4 font-medium">{{ user.name }}</td>
                                <td class="px-6 py-4">{{ user.email }}</td>
                                <td class="px-6 py-4">{{ getRoleNames(user) }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-1 text-xs"
                                        :class="user.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                                    >
                                        {{ user.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <Can permission="users-view">
                                            <Link :href="`/users/${user.slug}`" class="text-blue-600 hover:underline">View</Link>
                                        </Can>
                                        <Can permission="users-edit">
                                            <Link :href="`/users/${user.slug}/edit`" class="text-amber-600 hover:underline">Edit</Link>
                                        </Can>
                                        <Can permission="users-delete">
                                            <button @click="confirmDelete(user)" class="text-red-600 hover:underline">Delete</button>
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
                    <DialogTitle>Delete User</DialogTitle>
                </DialogHeader>
                <div class="py-4">
                    <p>
                        Are you sure you want to delete user <span class="font-semibold">{{ userToDelete?.name }}</span>? This
                        action cannot be undone.
                    </p>
                </div>
                <div class="flex justify-end space-x-2">
                    <Button variant="outline" @click="cancelDelete">Cancel</Button>
                    <Button variant="destructive" @click="deleteUser">Delete</Button>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
