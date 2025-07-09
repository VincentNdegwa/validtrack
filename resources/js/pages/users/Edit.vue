<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Role, type User } from '@/types/models';
import { Head, useForm } from '@inertiajs/vue3';

interface Props {
    user: User;
    roles: Role[];
    userRoles: number[];
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
        title: 'Edit',
        href: `/users/${props.user.slug}/edit`,
    },
];

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    phone: props.user.phone || '',
    address: props.user.address || '',
    location: props.user.location || '',
    is_active: props.user.is_active,
    roles: props.userRoles,
});

const submit = () => {
    form.put(`/users/${props.user.slug}`);
};

const updateRole = (roleId: number, checked: boolean) => {
    if (checked) {
        if (!form.roles.includes(roleId)) {
            form.roles.push(roleId);
        }
    } else {
        const index = form.roles.indexOf(roleId);
        if (index !== -1) {
            form.roles.splice(index, 1);
        }
    }
};
</script>

<template>
    <Head title="Edit User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Edit User</h1>
                <p class="text-muted-foreground">Update user information and role assignments</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <Card>
                    <div class="space-y-4 p-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Name</Label>
                                <Input id="name" v-model="form.name" required />
                                <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="email">Email</Label>
                                <Input id="email" type="email" v-model="form.email" required />
                                <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="password">Password (leave blank to keep unchanged)</Label>
                                <Input id="password" type="password" v-model="form.password" />
                                <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="password_confirmation">Confirm Password</Label>
                                <Input id="password_confirmation" type="password" v-model="form.password_confirmation" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="phone">Phone Number</Label>
                                <Input id="phone" v-model="form.phone" />
                                <p v-if="form.errors.phone" class="mt-1 text-xs text-red-500">{{ form.errors.phone }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="location">Location</Label>
                                <Input id="location" v-model="form.location" />
                                <p v-if="form.errors.location" class="mt-1 text-xs text-red-500">{{ form.errors.location }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="address">Address</Label>
                            <Input id="address" v-model="form.address" />
                            <p v-if="form.errors.address" class="mt-1 text-xs text-red-500">{{ form.errors.address }}</p>
                        </div>

                        <div class="space-y-3">
                            <Label>Roles</Label>
                            <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
                                <div v-for="role in roles" :key="role.id" class="flex items-center space-x-2">
                                    <div class="flex h-4 items-center">
                                        <input
                                            type="checkbox"
                                            :id="`role-${role.id}`"
                                            :checked="form.roles.includes(role.id)"
                                            @change="(e) => updateRole(role.id, (e.target as HTMLInputElement).checked)"
                                            :value="role.id"
                                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary/80"
                                        />
                                    </div>
                                    <Label :for="`role-${role.id}`" class="ml-2 cursor-pointer">
                                        {{ role.display_name || role.name }}
                                    </Label>
                                </div>
                            </div>
                            <p v-if="form.errors.roles" class="mt-1 text-xs text-red-500">{{ form.errors.roles }}</p>
                        </div>

                        <div class="flex flex-col justify-between gap-2">
                            <div class="flex items-center space-x-2">
                                <Label for="is_active" class="cursor-pointer">User Status</Label>
                                <p v-if="form.errors.is_active" class="mt-1 text-xs text-red-500">{{ form.errors.is_active }}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-muted-foreground">{{ form.is_active ? 'Active' : 'Inactive' }}</span>
                                <Switch id="is_active" v-model="form.is_active" />
                            </div>
                        </div>
                    </div>
                </Card>

                <div class="flex justify-end space-x-3">
                    <Button type="button" variant="ghost" href="/users">Cancel</Button>
                    <Button type="submit" class="bg-primary text-primary-foreground hover:bg-primary/90" :disabled="form.processing">
                        <span v-if="form.processing">Saving...</span>
                        <span v-else>Save Changes</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
