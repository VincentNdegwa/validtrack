<script setup lang="ts">
import { BillingPlan } from '@/types/models';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    plan: BillingPlan;
}>();

const price = computed(() => {
    return {
        monthly: new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(props.plan.monthly_price),
        yearly: new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(props.plan.yearly_price),
    };
});

function subscribeToPlan(billingCycle: 'monthly' | 'yearly'): void {
    router.post(route('billing.subscribe', props.plan.id), {
        billing_cycle: billingCycle,
    });
}
</script>

<template>
    <div
        class="overflow-hidden rounded-lg border shadow-sm transition-shadow hover:shadow-md"
        :class="{ 'border-blue-500 ring-2 ring-blue-500': plan.is_featured }"
    >
        <!-- Plan header -->
        <div class="border-b bg-gray-50 p-6 dark:bg-gray-700">
            <h3 class="text-xl font-semibold" :class="{ 'text-blue-600': plan.is_featured }">
                {{ plan.name }}
            </h3>
            <p class="mt-2 text-gray-600 dark:text-gray-300">{{ plan.description }}</p>
        </div>

        <!-- Plan pricing -->
        <div class="flex flex-col items-center p-6">
            <div class="mb-4 text-center">
                <div class="text-3xl font-bold">{{ price.monthly }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">per month</div>
            </div>

            <div class="mb-6 text-center">
                <div class="text-xl font-semibold">{{ price.yearly }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">billed annually</div>
            </div>

            <!-- Subscribe buttons -->
            <div class="w-full space-y-2">
                <button @click="subscribeToPlan('monthly')" class="w-full rounded-md bg-blue-600 px-4 py-2 text-white transition hover:bg-blue-700">
                    Subscribe Monthly
                </button>
                <button @click="subscribeToPlan('yearly')" class="w-full rounded-md bg-green-600 px-4 py-2 text-white transition hover:bg-green-700">
                    Subscribe Yearly
                </button>
            </div>
        </div>

        <!-- Plan features -->
        <div class="border-t bg-gray-50 p-6 dark:bg-gray-700">
            <h4 class="mb-3 font-medium">Features</h4>
            <ul class="space-y-2">
                <li v-for="feature in plan.features" :key="feature.id" class="flex items-start">
                    <svg class="mr-2 h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>
                        {{ feature.name }}
                        <span v-if="feature.type === 'number' && feature.pivot?.value !== 'true'" class="text-gray-600 dark:text-gray-400">
                            ({{ feature.pivot?.value === '-1' ? 'Unlimited' : feature.pivot?.value }})
                        </span>
                    </span>
                </li>
            </ul>
        </div>
    </div>
</template>
