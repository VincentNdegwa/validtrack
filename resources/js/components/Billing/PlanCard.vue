<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { BillingPlan } from '@/types/models';


const props = defineProps<{
    plan: BillingPlan;
}>();

const price = computed(() => {
    return {
        monthly: new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(props.plan.monthly_price),
        yearly: new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(props.plan.yearly_price),
    };
});

function subscribeToPlan(billingCycle: 'monthly' | 'yearly'): void {
    router.post(route('billing.subscribe', props.plan.id), {
        billing_cycle: billingCycle
    });
}
</script>

<template>
    <div class="border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow" 
         :class="{ 'ring-2 ring-blue-500 border-blue-500': plan.is_featured }">
        <!-- Plan header -->
        <div class="p-6 bg-gray-50 dark:bg-gray-700 border-b">
            <h3 class="text-xl font-semibold" :class="{ 'text-blue-600': plan.is_featured }">
                {{ plan.name }}
            </h3>
            <p class="text-gray-600 dark:text-gray-300 mt-2">{{ plan.description }}</p>
        </div>

        <!-- Plan pricing -->
        <div class="p-6 flex flex-col items-center">
            <div class="text-center mb-4">
                <div class="text-3xl font-bold">{{ price.monthly }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">per month</div>
            </div>

            <div class="text-center mb-6">
                <div class="text-xl font-semibold">{{ price.yearly }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">billed annually</div>
            </div>

            <!-- Subscribe buttons -->
            <div class="w-full space-y-2">
                <button @click="subscribeToPlan('monthly')" 
                        class="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition">
                    Subscribe Monthly
                </button>
                <button @click="subscribeToPlan('yearly')" 
                        class="w-full py-2 px-4 bg-green-600 hover:bg-green-700 text-white rounded-md transition">
                    Subscribe Yearly
                </button>
            </div>
        </div>

        <!-- Plan features -->
        <div class="p-6 bg-gray-50 dark:bg-gray-700 border-t">
            <h4 class="font-medium mb-3">Features</h4>
            <ul class="space-y-2">
                <li v-for="feature in plan.features" :key="feature.id" 
                    class="flex items-start">
                    <svg class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>
                        {{ feature.name }}
                        <span v-if="feature.type === 'number' && feature.pivot?.value !== 'true'" 
                              class="text-gray-600 dark:text-gray-400">
                            ({{ feature.pivot?.value === '-1' ? 'Unlimited' : feature.pivot?.value }})
                        </span>
                    </span>
                </li>
            </ul>
        </div>
    </div>
</template>
