<script setup lang="ts">
import Can from '@/components/auth/Can.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type User } from '@/types/models';
import { Head, Link } from '@inertiajs/vue3';

interface Props {
    user: User;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Users',
        href: '/users',
    },
    {
        title: props.user.name,
        href: `/users/${props.user.slug}`,
    },
];

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getRoleNames = (user: User) => {
    if (!user.roles || user.roles.length === 0) {
        return 'No roles assigned';
    }
    return user.roles.map((role) => role.display_name || role.name).join(', ');
};
</script>

<template>
    <Head :title="user.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">User Details</h1>
                    <p class="text-muted-foreground">User profile and role information</p>
                </div>
                <div class="flex gap-2">
                    <Can permission="users-edit">
                        <Link :href="`/users/${user.slug}/edit`">
                            <Button variant="outline">Edit User</Button>
                        </Link>
                    </Can>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <Card class="col-span-1">
                    <CardHeader>
                        <CardTitle>User Info</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="mb-6 flex flex-col items-center text-center">
                            <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-primary/20 text-primary">
                                <span class="text-xl font-bold">{{ user.name.charAt(0).toUpperCase() }}</span>
                            </div>
                            <h2 class="text-xl font-bold">{{ user.name }}</h2>
                            <p class="text-muted-foreground">{{ user.email }}</p>
                            <div class="mt-2">
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-1 text-xs"
                                    :class="
                                        user.is_active
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                    "
                                >
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div v-if="user.phone">
                                <p class="text-sm font-semibold">Phone</p>
                                <p class="text-muted-foreground">{{ user.phone }}</p>
                            </div>

                            <div v-if="user.location">
                                <p class="text-sm font-semibold">Location</p>
                                <p class="text-muted-foreground">{{ user.location }}</p>
                            </div>

                            <div v-if="user.address">
                                <p class="text-sm font-semibold">Address</p>
                                <p class="text-muted-foreground">{{ user.address }}</p>
                            </div>

                            <div>
                                <p class="text-sm font-semibold">Member Since</p>
                                <p class="text-muted-foreground">{{ formatDate(user.created_at) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="col-span-1 md:col-span-2">
                    <CardHeader>
                        <CardTitle>Role Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm font-semibold">Assigned Roles</p>
                                <p class="text-muted-foreground">{{ getRoleNames(user) }}</p>
                            </div>

                            <div v-if="user.roles && user.roles.length > 0">
                                <p class="text-sm font-semibold">Role Details</p>
                                <div class="mt-2 space-y-2">
                                    <div v-for="role in user.roles" :key="role.id" class="rounded-lg border border-border p-4">
                                        <div class="mb-2 flex items-center justify-between">
                                            <h3 class="font-semibold">{{ role.display_name || role.name }}</h3>
                                            <Can permission="roles-view">
                                                <Link :href="`/roles/${role.slug}`" class="text-sm text-blue-600 hover:underline"> View Role </Link>
                                            </Can>
                                        </div>
                                        <p v-if="role.description" class="mb-2 text-sm text-muted-foreground">{{ role.description }}</p>

                                        <div v-if="role.permissions && role.permissions.length > 0" class="mt-2">
                                            <p class="mb-1 text-xs font-semibold">Permissions:</p>
                                            <div class="flex flex-wrap gap-1">
                                                <div
                                                    v-for="permission in role.permissions"
                                                    :key="permission.id"
                                                    class="rounded-md bg-muted px-2 py-1 text-xs"
                                                >
                                                    {{ permission.display_name || permission.name }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
