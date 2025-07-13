<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Billing Features',
        href: '/billing/features',
    },
    {
        title: 'Create',
        href: '/billing/features/create',
    },
];

const form = useForm({
    name: '',
    key: '',
    type: 'string',
    description: '',
});

const featureTypes = [
    { id: 'string', name: 'Text' },
    { id: 'number', name: 'Number' },
    { id: 'boolean', name: 'Boolean (Yes/No)' },
];

const nameChanged = () => {
    if (!form.key) {
        form.key = form.name
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
    }
};

const submit = () => {
    form.post('/billing/features');
};
</script>

<template>
    <Head title="Create Billing Feature" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Create Billing Feature</h1>
                    <p class="text-muted-foreground">Add a new feature for subscription plans</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Feature Information</CardTitle>
                </CardHeader>
                
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Feature Name</Label>
                                <Input 
                                    id="name" 
                                    v-model="form.name" 
                                    type="text" 
                                    @input="nameChanged"
                                    placeholder="Example: Max Storage Space" 
                                    required 
                                />
                                <p v-if="form.errors.name" class="text-sm text-red-500">
                                    {{ form.errors.name }}
                                </p>
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="key">Feature Key</Label>
                                <Input 
                                    id="key" 
                                    v-model="form.key" 
                                    type="text"
                                    placeholder="Example: max_storage_space" 
                                    required 
                                />
                                <p v-if="form.errors.key" class="text-sm text-red-500">
                                    {{ form.errors.key }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    A unique identifier used in code. Use lowercase letters, numbers and underscores only.
                                </p>
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="type">Feature Type</Label>
                                <Select v-model="form.type" class="w-full">
                                    <option value="" disabled>Select feature type</option>
                                    <option v-for="type in featureTypes" :key="type.id" :value="type.id">
                                        {{ type.name }}
                                    </option>
                                </Select>
                                <p v-if="form.errors.type" class="text-sm text-red-500">
                                    {{ form.errors.type }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    Determines how this feature is configured in plans.
                                </p>
                            </div>
                            
                            <div class="space-y-2 md:col-span-2">
                                <Label for="description">Description</Label>
                                <Textarea 
                                    id="description" 
                                    v-model="form.description" 
                                    rows="3" 
                                    placeholder="Explain what this feature provides to users"
                                />
                                <p v-if="form.errors.description" class="text-sm text-red-500">
                                    {{ form.errors.description }}
                                </p>
                            </div>
                        </div>
                    </form>
                </CardContent>
                
                <CardFooter class="flex justify-end space-x-2">
                    <Button variant="outline" href="/billing/features">
                        Cancel
                    </Button>
                    <Button type="submit" class="bg-primary text-primary-foreground" @click="submit" :disabled="form.processing">
                        Create Feature
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
