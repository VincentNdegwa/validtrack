<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

interface CompanyForm {
    [key: string]: string | boolean;
    name: string;
    email: string;
    phone: string;
    address: string;
    website: string;
    description: string;
    is_active: boolean;
    user_name: string;
    user_email: string;
    user_password: string;
    user_phone: string;
    user_address: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Companies', href: '/companies' },
    { title: 'Create', href: '/companies/create' },
];

interface CompanyFormErrors {
    name?: string;
    email?: string;
    phone?: string;
    address?: string;
    website?: string;
    description?: string;
    is_active?: string;
    user_name?: string;
    user_email?: string;
    user_password?: string;
    user_phone?: string;
    user_address?: string;
}

type CompanyFormType = ReturnType<typeof useForm<CompanyForm>> & { errors: CompanyFormErrors };

const form = useForm<CompanyForm>({
    name: '',
    email: '',
    phone: '',
    address: '',
    website: '',
    description: '',
    is_active: true,
    user_name: '',
    user_email: '',
    user_password: '',
    user_phone: '',
    user_address: '',
}) as CompanyFormType;

// function copyField(field: keyof CompanyForm) {
//     form[field] = form[field.replace('user_', '') as keyof CompanyForm] as string || '';
// }

const submit = () => {
    form.post('/companies', {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Create Company" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Create Company</h1>
                    <p class="text-muted-foreground">Add a new company and its owner</p>
                </div>
            </div>
            <Card>
                <CardHeader>
                    <CardTitle>Company Information</CardTitle>
                    <CardDescription>Enter the details for the new company</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Company Name</Label>
                                <Input id="name" v-model="form.name" :error="form.errors.name" placeholder="Enter company name" />
                                <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="email">Company Email</Label>
                                <Input id="email" v-model="form.email" :error="form.errors.email" placeholder="company@example.com" />
                                <p v-if="form.errors.email" class="text-sm text-red-500">{{ form.errors.email }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="phone">Company Phone</Label>
                                <Input id="phone" type="tel" v-model="form.phone" :error="form.errors.phone" placeholder="(123) 456-7890" />
                                <p v-if="form.errors.phone" class="text-sm text-red-500">{{ form.errors.phone }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="website">Website</Label>
                                <Input id="website" v-model="form.website" :error="form.errors.website" placeholder="https://example.com" />
                                <p v-if="form.errors.website" class="text-sm text-red-500">{{ form.errors.website }}</p>
                            </div>
                            <div class="space-y-2 md:col-span-2">
                                <Label for="address">Company Address</Label>
                                <Input id="address" v-model="form.address" :error="form.errors.address" placeholder="Enter company address" />
                                <p v-if="form.errors.address" class="text-sm text-red-500">{{ form.errors.address }}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <Switch id="is_active" v-model="form.is_active" />
                                <Label for="is_active">Active</Label>
                                <p v-if="form.errors.is_active" class="text-sm text-red-500">{{ form.errors.is_active }}</p>
                            </div>
                        </div>
                        <CardHeader>
                            <CardTitle>User Information</CardTitle>
                            <CardDescription>Enter the details for the company owner</CardDescription>
                        </CardHeader>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="user_name">User Name</Label>
                                <Input id="user_name" v-model="form.user_name" :error="form.errors.user_name" placeholder="Owner's name" />
                                <p v-if="form.errors.user_name" class="text-sm text-red-500">{{ form.errors.user_name }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="user_email">User Email</Label>
                                <Input id="user_email" v-model="form.user_email" :error="form.errors.user_email" placeholder="owner@example.com" />
                                <p v-if="form.errors.user_email" class="text-sm text-red-500">{{ form.errors.user_email }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="user_password">User Password</Label>
                                <Input
                                    id="user_password"
                                    type="password"
                                    v-model="form.user_password"
                                    :error="form.errors.user_password"
                                    placeholder="Password"
                                />

                                <p v-if="form.errors.user_password" class="text-sm text-red-500">{{ form.errors.user_password }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="user_phone">User Phone</Label>
                                <Input
                                    id="user_phone"
                                    type="tel"
                                    v-model="form.user_phone"
                                    :error="form.errors.user_phone"
                                    placeholder="(123) 456-7890"
                                />
                                <p v-if="form.errors.user_phone" class="text-sm text-red-500">{{ form.errors.user_phone }}</p>
                            </div>
                            <div class="space-y-2 md:col-span-2">
                                <Label for="user_address">User Address</Label>
                                <Input
                                    id="user_address"
                                    v-model="form.user_address"
                                    :error="form.errors.user_address"
                                    placeholder="Owner's address"
                                />
                                <p v-if="form.errors.user_address" class="text-sm text-red-500">{{ form.errors.user_address }}</p>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <Button :disabled="form.processing" type="submit" class="bg-primary text-primary-foreground">
                                Create Company & User
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
