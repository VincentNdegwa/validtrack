<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type BillingFeature, type BillingPlan } from '@/types/models';
import { Head, useForm } from '@inertiajs/vue3';

interface Props {
    plan: BillingPlan;
    features?: BillingFeature[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Billing Plans',
        href: '/billing/plans',
    },
    {
        title: 'Edit',
        href: `/billing/plans/${props.plan.id}/edit`,
    },
];

// Prepare feature values
const prepareFeatures = () => {
    const featureObj: Record<string, any> = {};

    if (props.plan.features) {
        props.plan.features.forEach((feature) => {
            if (feature.pivot && feature.id) {
                const id = String(feature.id);
                // Convert value based on feature type
                if (feature.type === 'boolean') {
                    // Handle boolean values properly
                    const pivotValue = String(feature.pivot.value).toLowerCase();
                    featureObj[id] = pivotValue === 'true' || pivotValue === '1';
                } else if (feature.type === 'number') {
                    featureObj[id] = Number(feature.pivot.value);
                } else {
                    featureObj[id] = String(feature.pivot.value);
                }
            }
        });
    }

    return featureObj;
};

// Initialize form with current plan data
const form = useForm({
    name: props.plan.name,
    description: props.plan.description || '',
    monthly_price: props.plan.monthly_price,
    yearly_price: props.plan.yearly_price,
    is_active: props.plan.is_active,
    is_featured: props.plan.is_featured,
    sort_order: props.plan.sort_order,
    paddle_product_id: props.plan.paddle_product_id || '',
    paddle_monthly_price_id: props.plan.paddle_monthly_price_id || '',
    paddle_yearly_price_id: props.plan.paddle_yearly_price_id || '',
    features: prepareFeatures(),
});

const submit = () => {
    form.put(`/billing/plans/${props.plan.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Billing Plan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Edit Billing Plan</h1>
                    <p class="text-muted-foreground">Update billing plan information</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Plan Information</CardTitle>
                </CardHeader>

                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Plan Name</Label>
                                <Input id="name" v-model="form.name" :error="form.errors.name" placeholder="Enter plan name" />
                                <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="sort_order">Sort Order</Label>
                                <Input type="number" id="sort_order" v-model="form.sort_order" :error="form.errors.sort_order" />
                                <p v-if="form.errors.sort_order" class="text-sm text-red-500">{{ form.errors.sort_order }}</p>
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <Label for="description">Description</Label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="w-full rounded-md border focus:ring-2 focus:ring-primary focus:outline-none"
                                ></textarea>
                                <p v-if="form.errors.description" class="text-sm text-red-500">{{ form.errors.description }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="monthly_price">Monthly Price</Label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-muted-foreground">$</span>
                                    </div>
                                    <Input id="monthly_price" v-model="form.monthly_price" type="number" step="0.01" min="0" class="pl-7" />
                                </div>
                                <p v-if="form.errors.monthly_price" class="text-sm text-red-500">{{ form.errors.monthly_price }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="yearly_price">Yearly Price</Label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-muted-foreground">$</span>
                                    </div>
                                    <Input id="yearly_price" v-model="form.yearly_price" type="number" step="0.01" min="0" class="pl-7" />
                                </div>
                                <p v-if="form.errors.yearly_price" class="text-sm text-red-500">{{ form.errors.yearly_price }}</p>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Switch id="is_active" v-model="form.is_active" />
                                <Label for="is_active">Active</Label>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Switch id="is_featured" v-model="form.is_featured" />
                                <Label for="is_featured">Featured</Label>
                            </div>

                            <!-- Paddle Integration Fields -->
                            <div class="space-y-2 md:col-span-2">
                                <h4 class="mb-3 border-t pt-3 text-lg font-medium">Paddle Integration</h4>
                            </div>

                            <div class="space-y-2">
                                <Label for="paddle_product_id">Paddle Product ID</Label>
                                <Input
                                    id="paddle_product_id"
                                    v-model="form.paddle_product_id"
                                    :error="form.errors.paddle_product_id"
                                    placeholder="Enter Paddle Product ID"
                                />
                                <p v-if="form.errors.paddle_product_id" class="text-sm text-red-500">{{ form.errors.paddle_product_id }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="paddle_monthly_price_id">Paddle Monthly Price ID</Label>
                                <Input
                                    id="paddle_monthly_price_id"
                                    v-model="form.paddle_monthly_price_id"
                                    :error="form.errors.paddle_monthly_price_id"
                                    placeholder="Enter Paddle Monthly Price ID"
                                />
                                <p v-if="form.errors.paddle_monthly_price_id" class="text-sm text-red-500">
                                    {{ form.errors.paddle_monthly_price_id }}
                                </p>
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <Label for="paddle_yearly_price_id">Paddle Yearly Price ID</Label>
                                <Input
                                    id="paddle_yearly_price_id"
                                    v-model="form.paddle_yearly_price_id"
                                    :error="form.errors.paddle_yearly_price_id"
                                    placeholder="Enter Paddle Yearly Price ID"
                                />
                                <p v-if="form.errors.paddle_yearly_price_id" class="text-sm text-red-500">{{ form.errors.paddle_yearly_price_id }}</p>
                            </div>
                        </div>

                        <!-- Features Section -->
                        <div class="mt-6" v-if="props.features && props.features.length > 0">
                            <h4 class="mb-3 text-lg font-medium">Plan Features</h4>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div v-for="feature in props.features" :key="feature.id" class="rounded-md border p-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium">{{ feature.name }}</p>
                                            <p class="text-sm text-muted-foreground">{{ feature.description }}</p>
                                        </div>
                                        <div class="flex items-center">
                                            <template v-if="feature.type === 'boolean'">
                                                <Switch :id="`feature-${feature.id}`" v-model="form.features[String(feature.id)]" />
                                            </template>
                                            <template v-else-if="feature.type === 'number'">
                                                <div class="flex-flex-col">
                                                    <Input
                                                        type="number"
                                                        :id="`feature-${feature.id}`"
                                                        min="-1"
                                                        step="1"
                                                        v-model="form.features[String(feature.id)]"
                                                    />
                                                    <span class="ml-2 text-sm text-muted-foreground">(-1 for unlimited)</span>
                                                </div>
                                            </template>
                                            <template v-else>
                                                <Input :id="`feature-${feature.id}`" v-model="form.features[String(feature.id)]" />
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-2">
                            <Button href="/billing/plans" variant="outline">Cancel</Button>
                            <Button type="submit" :disabled="form.processing" class="bg-primary text-primary-foreground"> Update Plan </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
