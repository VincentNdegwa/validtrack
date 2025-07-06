<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Role } from '@/types/models';
import { Head, useForm } from '@inertiajs/vue3';

interface Props {
    roles?: Role[];
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
        title: 'Create',
        href: '/users/create',
    },
];

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    address: '',
    location: '',
    is_active: true,
    roles: [] as number[],
});

const submit = () => {
    form.post('/users');
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
    <Head title="Create User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Create User</h1>
                <p class="text-muted-foreground">Add a new user to your organization</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <Card>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="name">Name</Label>
                                <Input id="name" v-model="form.name" required />
                                <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="email">Email</Label>
                                <Input id="email" type="email" v-model="form.email" required />
                                <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="password">Password</Label>
                                <Input id="password" type="password" v-model="form.password" required />
                                <p v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="password_confirmation">Confirm Password</Label>
                                <Input id="password_confirmation" type="password" v-model="form.password_confirmation" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="phone">Phone Number</Label>
                                <Input id="phone" v-model="form.phone" />
                                <p v-if="form.errors.phone" class="text-xs text-red-500 mt-1">{{ form.errors.phone }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="location">Location</Label>
                                <Input id="location" v-model="form.location" />
                                <p v-if="form.errors.location" class="text-xs text-red-500 mt-1">{{ form.errors.location }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="address">Address</Label>
                            <Input id="address" v-model="form.address" />
                            <p v-if="form.errors.address" class="text-xs text-red-500 mt-1">{{ form.errors.address }}</p>
                        </div>

                        <div class="space-y-3">
                            <Label>Roles</Label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                <div v-for="role in props.roles" :key="role.id" class="flex items-center space-x-2">
                                    <div class="flex items-center h-4">
                                        <input 
                                            type="checkbox"
                                            :id="`role-${role.id}`" 
                                            :checked="form.roles.includes(role.id)"
                                            @change="(e) => updateRole(role.id, (e.target as HTMLInputElement).checked)"
                                            :value="role.id"
                                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary/80" 
                                        />
                                    </div>
                                    <Label :for="`role-${role.id}`" class="cursor-pointer ml-2">
                                        {{ role.display_name || role.name }}
                                    </Label>
                                </div>
                            </div>
                            <p v-if="form.errors.roles" class="text-xs text-red-500 mt-1">{{ form.errors.roles }}</p>
                        </div>

                        <div class="flex flex-col gap-2 justify-between">
                            <div class="flex items-center space-x-2">
                                <Label for="is_active" class="cursor-pointer">User Status</Label>
                                <p v-if="form.errors.is_active" class="text-xs text-red-500 mt-1">{{ form.errors.is_active }}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-muted-foreground">{{ form.is_active ? 'Active' : 'Inactive' }}</span>
                                <Switch
                                    id="is_active"
                                    v-model="form.is_active"
                                />
                            </div>
                        </div>
                    </div>
                </Card>

                <div class="flex justify-end space-x-3">
                    <Button type="button" variant="ghost" href="/users">Cancel</Button>
                    <Button type="submit" class="bg-primary text-primary-foreground hover:bg-primary/90" :disabled="form.processing">
                        <span v-if="form.processing">Creating...</span>
                        <span v-else>Create User</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
