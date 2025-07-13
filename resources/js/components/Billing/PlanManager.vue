<template>
    <div>
        <div class="border-b border-gray-200 pb-5 sm:flex sm:items-center sm:justify-between">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Subscription Management</h3>
            <div class="mt-3 flex sm:mt-0 sm:ml-4">
                <button
                    type="button"
                    @click="openModal"
                    class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                >
                    Change Plan
                </button>
            </div>
        </div>

        <div class="mt-6">
            <div v-if="!company.billingPlan" class="rounded-md bg-yellow-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">No Subscription</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>This company doesn't have an active subscription. Assign a plan to enable features.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="mt-6 overflow-hidden bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Current Plan: {{ company.billingPlan.name }}</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        {{ company.billingPlan.description }}
                    </p>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Billing Cycle</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ company.billingPlan.pivot.billing_cycle === 'yearly' ? 'Annual' : 'Monthly' }}
                            </dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Price</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{
                                    company.billingPlan.pivot.billing_cycle === 'yearly'
                                        ? '$' + company.billingPlan.yearly_price + '/year'
                                        : '$' + company.billingPlan.monthly_price + '/month'
                                }}
                            </dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Current Period</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ formatDate(company.billingPlan.pivot.current_period_start) }} to
                                {{ formatDate(company.billingPlan.pivot.current_period_end) }}
                            </dd>
                        </div>
                        <div v-if="company.billingPlan.pivot.trial_ends_at" class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500">Trial Ends</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ formatDate(company.billingPlan.pivot.trial_ends_at) }}</dd>
                        </div>
                    </dl>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <div class="text-sm font-medium text-gray-500">Features</div>
                    <div class="mt-2 sm:col-span-2 sm:mt-0">
                        <ul role="list" class="divide-y divide-gray-200 rounded-md border border-gray-200">
                            <li
                                v-for="feature in company.billingPlan.features"
                                :key="feature.id"
                                class="flex items-center justify-between py-3 pr-4 pl-3 text-sm"
                            >
                                <div class="flex w-0 flex-1 items-center">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 flex-shrink-0 text-green-500"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                        v-if="isFeatureActive(feature)"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 flex-shrink-0 text-gray-400"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                        v-else
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <span class="ml-2 w-0 flex-1 truncate">
                                        {{ feature.name }}: <span class="font-medium">{{ formatFeatureValue(feature) }}</span>
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plan Selection Modal -->
        <Dialog v-model:open="showModal">
            <DialogContent class="sm:max-w-md">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900">Assign Subscription Plan</h3>
                    <form @submit.prevent="assignPlan" class="mt-6">
                        <div class="space-y-6">
                            <div>
                                <Label for="plan">Select Plan</Label>
                                <select
                                    id="plan"
                                    v-model="form.plan_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="" disabled>Select a plan</option>
                                    <option v-for="plan in availablePlans" :key="plan.id" :value="plan.id">
                                        {{ plan.name }} (Monthly: ${{ plan.monthly_price }}, Yearly: ${{ plan.yearly_price }})
                                    </option>
                                </select>
                                <InputError :message="form.errors.plan_id" class="mt-2" />
                            </div>

                            <div>
                                <Label for="billing_cycle">Billing Cycle</Label>
                                <select
                                    id="billing_cycle"
                                    v-model="form.billing_cycle"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly (Save 20%)</option>
                                </select>
                                <InputError :message="form.errors.billing_cycle" class="mt-2" />
                            </div>

                            <div>
                                <Label for="trial_days">Trial Period (Days)</Label>
                                <Input id="trial_days" type="number" min="0" class="mt-1 block w-full" v-model="form.trial_days" />
                                <p class="mt-1 text-xs text-gray-500">Leave at 0 for no trial period</p>
                                <InputError :message="form.errors.trial_days" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button
                                type="button"
                                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                                @click="closeModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                                :disabled="form.processing"
                            >
                                Assign Plan
                            </button>
                        </div>
                    </form>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Feature {
    id: number;
    name: string;
    key: string;
    type: string;
    description?: string;
    pivot: {
        value: string | number;
    };
}

interface BillingPlan {
    id: number;
    name: string;
    description?: string;
    monthly_price: number;
    yearly_price: number;
    features: Feature[];
    pivot: {
        billing_cycle: 'monthly' | 'yearly';
        trial_ends_at?: string;
        current_period_start: string;
        current_period_end: string;
    };
}

interface Company {
    id: number;
    name: string;
    billingPlan?: BillingPlan;
}

interface AvailablePlan {
    id: number;
    name: string;
    description?: string;
    monthly_price: number;
    yearly_price: number;
}

const props = defineProps<{
    company: Company;
    availablePlans?: AvailablePlan[];
}>();

const showModal = ref(false);
const form = useForm({
    plan_id: '',
    billing_cycle: 'monthly',
    trial_days: 0,
});

const openModal = (): void => {
    showModal.value = true;
};

const closeModal = (): void => {
    showModal.value = false;
};

const assignPlan = (): void => {
    form.post(route('billing.assign-plan', props.company.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

const formatDate = (dateString?: string): string => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString();
};

const isFeatureActive = (feature: Feature): boolean => {
    if (feature.type === 'boolean') {
        return feature.pivot.value === '1' || feature.pivot.value === 1 || feature.pivot.value === 'true';
    }
    // Need to check for null or empty string
    return feature.pivot.value !== undefined && feature.pivot.value !== null && feature.pivot.value !== '';
};

const formatFeatureValue = (feature: Feature): string => {
    const value = feature.pivot.value;

    if (value === '-1' || value === -1) {
        return 'Unlimited';
    }

    if (feature.type === 'boolean') {
        return isFeatureActive(feature) ? 'Yes' : 'No';
    }

    return String(value) || 'N/A';
};
</script>
