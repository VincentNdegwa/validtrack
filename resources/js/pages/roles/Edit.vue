<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Permission, type Role } from '@/types/models';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    role: Role;
    permissions: Permission[];
    rolePermissions: number[];
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
        title: 'Edit',
        href: `/roles/${props.role.slug}/edit`,
    },
];

const form = useForm({
    name: props.role.name,
    display_name: props.role.display_name || '',
    description: props.role.description || '',
    permissions: props.rolePermissions,
});

const submit = () => {
    form.put(`/roles/${props.role.slug}`);
};

// Group permissions by category based on their names
const groupedPermissions = computed(() => {
    if (!props.permissions) return {};
    
    const groups: Record<string, Permission[]> = {};
    
    props.permissions.forEach(permission => {
        // Extract category from permission name (e.g., "users-create" => "users")
        const category = permission.name.split('-')[0] || 'other';
        
        if (!groups[category]) {
            groups[category] = [];
        }
        
        groups[category].push(permission);
    });
    
    return groups;
});
</script>

<template>
    <Head title="Edit Role" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Edit Role</h1>
                <p class="text-muted-foreground">Update role information and permissions</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <Card>
                    <div class="p-6 space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Role Name</Label>
                            <Input 
                                id="name" 
                                v-model="form.name" 
                                required 
                            />
                            <p class="text-xs text-muted-foreground">
                                Use lowercase with no spaces. This is the technical name used internally.
                            </p>
                            <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="display_name">Display Name</Label>
                            <Input 
                                id="display_name" 
                                v-model="form.display_name"
                            />
                            <p class="text-xs text-muted-foreground">
                                User-friendly name displayed in the interface.
                            </p>
                            <p v-if="form.errors.display_name" class="text-xs text-red-500 mt-1">{{ form.errors.display_name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea 
                                id="description" 
                                v-model="form.description"
                                rows="3"
                            />
                            <p v-if="form.errors.description" class="text-xs text-red-500 mt-1">{{ form.errors.description }}</p>
                        </div>

                        <div class="space-y-4 pt-4">
                            <h3 class="text-lg font-semibold border-b border-border pb-2">Assign Permissions</h3>

                            <div v-if="!permissions || permissions.length === 0" class="py-4 text-center text-muted-foreground">
                                No permissions available to assign.
                            </div>
                            
                            <div v-else class="space-y-6">
                                <div v-for="(perms, category) in groupedPermissions" :key="category" class="space-y-2">
                                    <h4 class="font-medium capitalize">{{ category }}</h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                        <div v-for="permission in perms" :key="permission.id" class="flex items-center space-x-2">
                                            <Checkbox 
                                                :id="`permission-${permission.id}`" 
                                                v-model:checked="form.permissions" 
                                                :value="permission.id" 
                                            />
                                            <Label :for="`permission-${permission.id}`" class="cursor-pointer">
                                                {{ permission.display_name || permission.name }}
                                                <span 
                                                    v-if="permission.company_id === null" 
                                                    class="ml-1 text-xs rounded-full bg-blue-100 px-2 py-0.5 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                                >
                                                    global
                                                </span>
                                            </Label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <p v-if="form.errors.permissions" class="text-xs text-red-500 mt-1">{{ form.errors.permissions }}</p>
                        </div>
                    </div>
                </Card>

                <div class="flex justify-end space-x-3">
                    <Button type="button" variant="ghost" href="/roles">Cancel</Button>
                    <Button type="submit" class="bg-primary text-primary-foreground hover:bg-primary/90" :disabled="form.processing">
                        <span v-if="form.processing">Saving...</span>
                        <span v-else>Save Changes</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
