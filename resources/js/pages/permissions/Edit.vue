<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Permission } from '@/types/models';
import { Head, useForm } from '@inertiajs/vue3';

interface Props {
    permission: Permission;
    isGlobal: boolean;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Permissions',
        href: '/permissions',
    },
    {
        title: 'Edit',
        href: `/permissions/${props.permission.slug}/edit`,
    },
];

const form = useForm({
    name: props.permission.name,
    display_name: props.permission.display_name || '',
    description: props.permission.description || '',
    is_global: props.isGlobal,
});

const submit = () => {
    form.put(`/permissions/${props.permission.slug}`);
};

// Check if this permission is editable (can't modify global permissions scope if not owned by the company)
const canChangeGlobalStatus = computed(() => {
    // If not global or if it's a global permission that belongs to the current company
    return !props.isGlobal || (props.isGlobal && props.permission.company_id !== null);
});

import { computed } from 'vue';
</script>

<template>
    <Head title="Edit Permission" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Edit Permission</h1>
                <p class="text-muted-foreground">Update permission details</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <Card>
                    <div class="space-y-4 p-6">
                        <div class="space-y-2">
                            <Label for="name">Permission Name</Label>
                            <Input id="name" v-model="form.name" required />
                            <p class="text-xs text-muted-foreground">Use lowercase with dashes. Typically follows a 'resource-action' pattern.</p>
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="display_name">Display Name</Label>
                            <Input id="display_name" v-model="form.display_name" />
                            <p class="text-xs text-muted-foreground">User-friendly name displayed in the interface.</p>
                            <p v-if="form.errors.display_name" class="mt-1 text-xs text-red-500">{{ form.errors.display_name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" rows="3" />
                            <p v-if="form.errors.description" class="mt-1 text-xs text-red-500">{{ form.errors.description }}</p>
                        </div>

                        <div class="flex items-center space-x-2 pt-2">
                            <Checkbox id="is_global" v-model:checked="form.is_global" :disabled="!canChangeGlobalStatus" />
                            <Label for="is_global" class="cursor-pointer" :class="{ 'opacity-50': !canChangeGlobalStatus }">
                                Global Permission
                            </Label>
                            <p v-if="form.errors.is_global" class="mt-1 text-xs text-red-500">{{ form.errors.is_global }}</p>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Global permissions apply across all companies. Company-specific permissions are only valid within your company.
                            <span v-if="!canChangeGlobalStatus" class="text-amber-500">
                                You cannot change the scope of system-defined global permissions.
                            </span>
                        </p>
                    </div>
                </Card>

                <div class="flex justify-end space-x-3">
                    <Button type="button" variant="ghost" href="/permissions">Cancel</Button>
                    <Button type="submit" class="bg-primary text-primary-foreground hover:bg-primary/90" :disabled="form.processing">
                        <span v-if="form.processing">Saving...</span>
                        <span v-else>Save Changes</span>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
