<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { DataTable } from '@/components/ui/data-table';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { BillingFeature, Transaction, type BillingPlan } from '@/types/models';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface BillingPlanWithFeatures extends BillingPlan {
    features?: BillingFeature[];
}

interface UserBillingPlan {
    id: number;
    billing_plan?: BillingPlanWithFeatures;
    billing_cycle: 'monthly' | 'yearly';
    current_period_start: string;
    current_period_end: string;
    trial_ends_at?: string;
    status: string;
    slug: string;
}

interface Props {
    currentPlan?: UserBillingPlan;
    plans?: BillingPlanWithFeatures[];
    transactions: Transaction[];
}

const props = defineProps<Props>();

const planFeatures = computed(() => props.currentPlan?.billing_plan?.features || []);

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
        title: 'Billing',
        href: `/billing`,
    },
];

// UI state management
const showModal = ref(false);
const showCancelDialog = ref(false);

const form = useForm({
    plan_id: '',
    billing_cycle: 'monthly',
});

// Action handlers
const closeModal = (): void => {
    showModal.value = false;
};

const closeCancelDialog = (): void => {
    showCancelDialog.value = false;
};

const assignPlan = (): void => {
    const url = `/billing/${form.plan_id}/subscribe/${form.billing_cycle}`;
    window.location.href = url;
};

const cancelPlan = (): void => {
    router.post(`/billing/cancel`, {
        preserveScroll: true,
    });
};
const formatDate = (dateString: string): string => {
    return dateString;
    // const date = new Date(dateString);
    // return date.toLocaleString();
};
</script>

<template>
    <Head :title="`Billing`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Billing</h1>
                    <p class="text-muted-foreground">Manage subscription plans and billing information</p>
                </div>
                <div>
                    <Button @click="showModal = true">
                        {{ props.currentPlan ? 'Change Plan' : 'Subscribe' }}
                    </Button>
                </div>
            </div>

            <!-- Alert for no subscription -->
            <div v-if="!props.currentPlan" class="rounded-lg bg-muted/30 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                            />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium">No Active Subscription</h3>
                        <div class="mt-2 text-sm">
                            <p>This user doesn't have an active subscription. Assign a plan to enable premium features.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription details -->
            <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Subscription summary card -->
                <Card class="lg:col-span-2">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <svg
                                class="h-5 w-5 text-primary"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                />
                            </svg>
                            Current Subscription
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-6">
                            <!-- Plan name & description -->
                            <div>
                                <h3 class="text-lg font-medium">{{ props.currentPlan.billing_plan?.name }}</h3>
                                <p class="text-sm text-muted-foreground">{{ props.currentPlan.billing_plan?.description }}</p>
                            </div>

                            <!-- Plan details grid -->
                            <div class="grid grid-cols-1 gap-4 rounded-lg border p-4 sm:grid-cols-2 md:grid-cols-3">
                                <!-- Billing Cycle -->
                                <div class="flex items-start space-x-3">
                                    <svg
                                        class="h-5 w-5 text-muted-foreground"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                    <div>
                                        <p class="font-medium">Billing Cycle</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ props.currentPlan.billing_cycle === 'yearly' ? 'Annual' : 'Monthly' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Price -->
                                <div class="flex items-start space-x-3">
                                    <svg
                                        class="h-5 w-5 text-muted-foreground"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    <div>
                                        <p class="font-medium">Price</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{
                                                props.currentPlan.billing_cycle === 'yearly'
                                                    ? '$' + props.currentPlan.billing_plan?.yearly_price + '/year'
                                                    : '$' + props.currentPlan.billing_plan?.monthly_price + '/month'
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="flex items-start space-x-3">
                                    <svg
                                        class="h-5 w-5 text-muted-foreground"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    <div>
                                        <p class="font-medium">Status</p>
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            :class="{
                                                'bg-green-100 text-green-800': props.currentPlan.status === 'active',
                                                'bg-yellow-100 text-yellow-800': props.currentPlan.status === 'trial',
                                                'bg-red-100 text-red-800': props.currentPlan.status === 'canceled',
                                            }"
                                        >
                                            {{
                                                props.currentPlan.status
                                                    ? props.currentPlan.status.charAt(0).toUpperCase() + props.currentPlan.status.slice(1)
                                                    : ''
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Start Period -->
                                <div class="flex items-start space-x-3">
                                    <svg
                                        class="h-5 w-5 text-muted-foreground"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                    <div>
                                        <p class="font-medium">Current Period</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ formatDate(props.currentPlan.current_period_start) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- End Period -->
                                <div class="flex items-start space-x-3">
                                    <svg
                                        class="h-5 w-5 text-muted-foreground"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                    <div>
                                        <p class="font-medium">End Period</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ formatDate(props.currentPlan.current_period_end) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Trial Ends (if applicable) -->
                                <div v-if="props.currentPlan.trial_ends_at" class="flex items-start space-x-3">
                                    <svg
                                        class="h-5 w-5 text-muted-foreground"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    <div>
                                        <p class="font-medium">Trial Ends</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ formatDate(props.currentPlan.trial_ends_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end">
                        <Button variant="destructive" @click="showCancelDialog = true"> Cancel Subscription </Button>
                    </CardFooter>
                </Card>

                <!-- Features card -->
                <Card v-if="planFeatures.length > 0">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <svg
                                class="h-5 w-5 text-primary"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Plan Features
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ul class="space-y-3">
                            <li v-for="feature in planFeatures" :key="feature.id" class="flex items-start space-x-3">
                                <div class="mt-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-primary/10">
                                    <svg class="h-4 w-4 text-primary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="flex items-center gap-2">
                                    <p class="font-medium">{{ feature.name }}:</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{
                                            feature.pivot?.value === '-1'
                                                ? 'Unlimited'
                                                : feature.type === 'boolean'
                                                  ? feature.pivot?.value === '1'
                                                      ? 'Yes'
                                                      : 'No'
                                                  : feature.pivot?.value
                                        }}
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </CardContent>
                </Card>
            </div>

            <DataTable
                :data="props.transactions"
                :showPagination="false"
                emptyMessage="No transactions found."
                :columns="[
                    { key: 'id', label: 'ID' },
                    { key: 'invoice_number', label: 'Invoice Number' },
                    { key: 'total', label: 'Amount' },
                    { key: 'status', label: 'Status' },
                    { key: 'billed_at', label: 'Date' },
                ]"
            >
                <template #billed_at="{ item: transaction }">
                    {{ formatDate(transaction.billed_at) }}
                </template>
            </DataTable>
        </div>

        <!-- Plan Selection Modal -->
        <Dialog :open="showModal" @update:open="(val) => !val && closeModal()">
            <DialogContent class="max-h-[95vh] overflow-y-auto sm:max-w-[700px] md:max-w-[800px] lg:max-w-3/4">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                            />
                        </svg>
                        {{ props.currentPlan ? 'Change Subscription' : 'Select a Subscription' }}
                    </DialogTitle>
                </DialogHeader>

                <div class="py-4">
                    <form @submit.prevent="assignPlan">
                        <!-- Billing cycle selector -->
                        <div class="mb-6">
                            <input type="hidden" name="billing_cycle" :value="form.billing_cycle" />

                            <div class="flex justify-center">
                                <div class="inline-flex rounded-md shadow-sm" role="group">
                                    <button
                                        type="button"
                                        @click="form.billing_cycle = 'monthly'"
                                        :class="[
                                            'border px-4 py-2 text-sm font-medium',
                                            form.billing_cycle === 'monthly'
                                                ? 'border-primary bg-primary text-primary-foreground'
                                                : 'border-muted bg-background text-muted-foreground hover:bg-muted/50',
                                        ]"
                                        class="rounded-l-lg"
                                    >
                                        Monthly Billing
                                    </button>
                                    <button
                                        type="button"
                                        @click="form.billing_cycle = 'yearly'"
                                        :class="[
                                            'border px-4 py-2 text-sm font-medium',
                                            form.billing_cycle === 'yearly'
                                                ? 'border-primary bg-primary text-primary-foreground'
                                                : 'border-muted bg-background text-muted-foreground hover:bg-muted/50',
                                        ]"
                                        class="rounded-r-lg"
                                    >
                                        Yearly Billing
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Plans cards -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            <div
                                v-for="plan in props.plans"
                                :key="plan.id"
                                @click="form.plan_id = plan.slug"
                                :class="[
                                    'relative cursor-pointer rounded-lg border p-4 transition-all hover:border-primary hover:shadow-md',
                                    form.plan_id === plan.slug ? 'border-primary shadow-sm ring-2 ring-primary/20' : 'border-muted',
                                ]"
                            >
                                <!-- Selected badge -->
                                <div
                                    v-if="form.plan_id === plan.slug"
                                    class="absolute top-2 right-2 flex h-5 w-5 items-center justify-center rounded-full bg-primary text-primary-foreground"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3">
                                        <path
                                            fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>

                                <!-- Featured badge -->
                                <div
                                    v-if="plan.is_featured"
                                    class="absolute -top-2 -left-2 rounded-full bg-primary px-2 py-1 text-xs font-medium text-white shadow-md"
                                >
                                    Popular
                                </div>

                                <div class="space-y-4">
                                    <!-- Plan header -->
                                    <div>
                                        <h3 class="text-lg font-semibold">{{ plan.name }}</h3>
                                        <p class="text-sm text-muted-foreground">{{ plan.description }}</p>
                                    </div>

                                    <!-- Price -->
                                    <div class="flex items-baseline">
                                        <span class="text-3xl font-bold">
                                            ${{ form.billing_cycle === 'yearly' ? plan.yearly_price : plan.monthly_price }}
                                        </span>
                                        <span class="ml-1 text-sm text-muted-foreground">
                                            /{{ form.billing_cycle === 'yearly' ? 'year' : 'month' }}
                                        </span>
                                    </div>

                                    <!-- Savings calculation -->
                                    <div v-if="form.billing_cycle === 'yearly'" class="text-xs text-green-600">
                                        {{ Math.round((1 - plan.yearly_price / (plan.monthly_price * 12)) * 100) }}% savings vs monthly
                                    </div>

                                    <!-- Features -->
                                    <div class="border-t pt-4">
                                        <p class="mb-2 text-sm font-medium">Included features:</p>
                                        <ul class="space-y-2 text-sm">
                                            <li v-for="feature in plan.features" :key="feature.id" class="flex items-center">
                                                <svg
                                                    class="mr-2 h-4 w-4 text-primary"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                                <span>
                                                    {{ feature.name }}:
                                                    <span class="font-medium">
                                                        {{
                                                            feature.pivot?.value === '-1'
                                                                ? 'Unlimited'
                                                                : feature.type === 'boolean'
                                                                  ? feature.pivot?.value === '1'
                                                                      ? 'Yes'
                                                                      : 'No'
                                                                  : feature.pivot?.value
                                                        }}
                                                    </span>
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="form.errors.plan_id" class="mt-2 text-sm text-destructive">
                            {{ form.errors.plan_id }}
                        </div>
                    </form>
                </div>

                <DialogFooter class="bottom-0 z-10 mt-4 border-t bg-background pt-4">
                    <Button variant="outline" @click="closeModal" class="mr-2">Cancel</Button>
                    <Button @click="assignPlan" :disabled="form.processing || !form.plan_id">
                        {{ props.currentPlan ? 'Change Plan' : 'Subscribe' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Cancel Subscription Dialog -->
        <Dialog :open="showCancelDialog" @update:open="(val) => !val && closeCancelDialog()">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <svg
                            class="h-5 w-5 text-destructive"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                        </svg>
                        Cancel Subscription
                    </DialogTitle>
                </DialogHeader>

                <div class="py-4">
                    <p class="mb-4">
                        Are you sure you want to cancel this subscription? The user will lose access to premium features when the current billing
                        period ends.
                    </p>
                    <div class="rounded-md bg-destructive/10 p-4 text-sm">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg
                                    class="h-5 w-5 text-destructive"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    aria-hidden="true"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="font-medium text-destructive">Important notice</h3>
                                <div class="mt-2 text-destructive/80">
                                    <p>This action cannot be undone. The subscription will be canceled immediately.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="closeCancelDialog" class="mr-2">Keep Subscription</Button>
                    <Button variant="destructive" @click="cancelPlan" :disabled="form.processing"> Cancel Subscription </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
