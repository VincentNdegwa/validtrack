<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Permission } from '@/types/models';
import { Head, useForm } from '@inertiajs/vue3';

interface Props {
    permissions?: Permission[];
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
        title: 'Create',
        href: '/roles/create',
    },
];

const form = useForm({
    name: '',
    display_name: '',
    description: '',
    permissions: [] as number[],
});

const submit = () => {
    form.post('/roles');
};

// Method to handle permission checkbox changes
const updatePermission = (permissionId: number, checked: boolean) => {
    if (checked) {
        // Add permission if it's not already in the array
        if (!form.permissions.includes(permissionId)) {
            form.permissions.push(permissionId);
        }
    } else {
        // Remove permission from the array
        const index = form.permissions.indexOf(permissionId);
        if (index !== -1) {
            form.permissions.splice(index, 1);
        }
    }
};

// Group permissions by category based on their names
const groupedPermissions = computed(() => {
    if (!props.permissions) return {};

    const groups: Record<string, Permission[]> = {};

    props.permissions.forEach((permission) => {
        // Extract category from permission name (e.g., "users-create" => "users")
        const category = permission.name.split('-')[0] || 'other';

        if (!groups[category]) {
            groups[category] = [];
        }

        groups[category].push(permission);
    });

    return groups;
});

import { computed } from 'vue';
</script>

<template>
    <Head title="Create Role" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Create Role</h1>
                <p class="text-muted-foreground">Add a new role with specific permissions</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <Card>
                    <div class="space-y-4 p-6">
                        <div class="space-y-2">
                            <Label for="name">Role Name</Label>
                            <Input id="name" v-model="form.name" required placeholder="e.g., admin, editor, viewer" />
                            <p class="text-xs text-muted-foreground">Use lowercase with no spaces. This is the technical name used internally.</p>
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="display_name">Display Name</Label>
                            <Input id="display_name" v-model="form.display_name" placeholder="e.g., Administrator, Content Editor, Viewer" />
                            <p class="text-xs text-muted-foreground">User-friendly name displayed in the interface.</p>
                            <p v-if="form.errors.display_name" class="mt-1 text-xs text-red-500">{{ form.errors.display_name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                placeholder="Describe the purpose and capabilities of this role..."
                                rows="3"
                            />
                            <p v-if="form.errors.description" class="mt-1 text-xs text-red-500">{{ form.errors.description }}</p>
                        </div>

                        <div class="space-y-4 pt-4">
                            <h3 class="border-b border-border pb-2 text-lg font-semibold">Assign Permissions</h3>

                            <div v-if="!props.permissions || props.permissions.length === 0" class="py-4 text-center text-muted-foreground">
                                No permissions available to assign.
                            </div>

                            <div v-else class="space-y-6">
                                <div v-for="(permissions, category) in groupedPermissions" :key="category" class="space-y-2">
                                    <h4 class="font-medium capitalize">{{ category }}</h4>

                                    <div class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3">
                                        <div v-for="permission in permissions" :key="permission.id" class="flex items-center space-x-2">
                                            <div class="flex h-4 items-center">
                                                <input
                                                    type="checkbox"
                                                    :id="`permission-${permission.id}`"
                                                    :checked="form.permissions.includes(permission.id)"
                                                    @change="(e) => updatePermission(permission.id, (e.target as HTMLInputElement).checked)"
                                                    :value="permission.id"
                                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary/80"
                                                />
                                            </div>
                                            <Label :for="`permission-${permission.id}`" class="ml-2 cursor-pointer">
                                                {{ permission.display_name || permission.name }}
                                            </Label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p v-if="form.errors.permissions" class="mt-1 text-xs text-red-500">{{ form.errors.permissions }}</p>
                        </div>
                    </div>
                </Card>

                <div class="flex justify-end space-x-3">
                    <Button type="button" variant="ghost" href="/roles">Cancel</Button>
                    <Button type="submit" class="bg-primary text-primary-foreground hover:bg-primary/90" :disabled="form.processing">
                        <span v-if="form.processing">Creating...</span>
                        <span v-else>Create Role</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
