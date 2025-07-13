<script setup lang="ts">
import { router } from '@inertiajs/vue3';

interface Feature {
    id: number;
    name: string;
    type: string;
    key: string;
    description?: string;
    pivot?: {
        value: string | number;
    };
}

interface Plan {
    id: number;
    name: string;
    description?: string;
    monthly_price: number;
    yearly_price: number;
    is_active: boolean;
    is_featured: boolean;
    features: Feature[];
}

const props = defineProps<{
    plans?: Plan[];
    features?: Feature[];
}>();

const emit = defineEmits<{
    edit: [plan: Plan];
}>();

function editPlan(plan: Plan): void {
    emit('edit', plan);
}

function deletePlan(plan: Plan): void {
    if (confirm(`Are you sure you want to delete the "${plan.name}" plan?`)) {
        router.delete(route('billing.plans.destroy', plan.id));
    }
}
</script>

<template>
    <div>
        <div v-if="!props.plans || props.plans.length === 0" class="py-6 text-center">
            <p class="text-gray-600 dark:text-gray-300">No billing plans found. Create your first plan!</p>
        </div>
        <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                            Monthly Price
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                            Yearly Price
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                            Features
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    <tr v-for="plan in plans" :key="plan.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ plan.name }}
                                    <span
                                        v-if="plan.is_featured"
                                        class="ml-2 inline-flex rounded-full bg-blue-100 px-2 text-xs leading-5 font-semibold text-blue-800"
                                    >
                                        Featured
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-gray-100">${{ plan.monthly_price }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-gray-100">${{ plan.yearly_price }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold"
                                :class="plan.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                            >
                                {{ plan.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 dark:text-gray-100">{{ plan.features.length }} features</div>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                            <button @click="editPlan(plan)" class="mr-3 text-blue-600 hover:text-blue-900">Edit</button>
                            <button @click="deletePlan(plan)" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
