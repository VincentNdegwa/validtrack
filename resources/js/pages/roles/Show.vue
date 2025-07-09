<script setup lang="ts">
import Can from '@/components/auth/Can.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Role } from '@/types/models';
import { Head, Link } from '@inertiajs/vue3';

interface Props {
    role: Role;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Roles',
        href: '/roles',
    },
    {
        title: props.role.display_name || props.role.name,
        href: `/roles/${props.role.slug}`,
    },
];

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <Head :title="role.display_name || role.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Role Details</h1>
                    <p class="text-muted-foreground">Role permissions and assigned users</p>
                </div>
                <div class="flex gap-2">
                    <Can permission="roles-edit" >
                        <Link :href="`/roles/${role.slug}/edit`">
                            <Button variant="outline">Edit Role</Button>
                        </Link>
                    </Can>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card class="col-span-1">
                    <CardHeader>
                        <CardTitle>Role Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm font-semibold">Name</p>
                                <p class="text-muted-foreground">{{ role.name }}</p>
                            </div>
                            
                            <div v-if="role.display_name">
                                <p class="text-sm font-semibold">Display Name</p>
                                <p class="text-muted-foreground">{{ role.display_name }}</p>
                            </div>
                            
                            <div v-if="role.description">
                                <p class="text-sm font-semibold">Description</p>
                                <p class="text-muted-foreground">{{ role.description }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-semibold">Created</p>
                                <p class="text-muted-foreground">{{ formatDate(role.created_at) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="col-span-1 md:col-span-2">
                    <CardHeader>
                        <CardTitle>Permissions</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="!role.permissions || role.permissions.length === 0" class="text-center text-muted-foreground py-4">
                            No permissions assigned to this role.
                        </div>
                        
                        <div v-else class="space-y-4">
                            <div class="flex flex-wrap gap-2">
                                <div 
                                    v-for="permission in role.permissions" 
                                    :key="permission.id"
                                    class="rounded-md bg-muted px-3 py-1.5 text-sm flex items-center"
                                >
                                    {{ permission.display_name || permission.name }}
                                    <span 
                                        v-if="permission.company_id === null" 
                                        class="ml-2 text-xs rounded-full bg-blue-100 px-2 py-0.5 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                    >
                                        global
                                    </span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <Card class="col-span-1 md:col-span-3">
                    <CardHeader>
                        <CardTitle>Users with this Role</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="!role.users || role.users.length === 0" class="text-center text-muted-foreground py-4">
                            No users have been assigned this role.
                        </div>
                        
                        <div v-else class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-muted/50 text-xs uppercase">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Name</th>
                                        <th scope="col" class="px-6 py-3">Email</th>
                                        <th scope="col" class="px-6 py-3">Status</th>
                                        <th scope="col" class="px-6 py-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="user in role.users" :key="user.id" class="border-t border-border hover:bg-muted/30">
                                        <td class="px-6 py-4 font-medium">{{ user.name }}</td>
                                        <td class="px-6 py-4">{{ user.email }}</td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="inline-flex items-center rounded-full px-2 py-1 text-xs"
                                                :class="user.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                                            >
                                                {{ user.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <Link :href="`/users/${user.slug}`" class="text-blue-600 hover:underline">View User</Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
