<script setup lang="ts">
import { ref, computed } from 'vue';
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

interface FormFeature {
    id: number;
    name: string;
    type: string;
    value: string | number;
}

interface PlanForm {
    name: string;
    description: string;
    monthly_price: number;
    yearly_price: number;
    is_active: boolean;
    is_featured: boolean;
    features: FormFeature[];
}

const props = defineProps<{
    plan?: Plan;
    features?: Feature[];
}>();

const emit = defineEmits<{
    close: [];
}>();

const form = ref<PlanForm>({
    name: props.plan?.name || '',
    description: props.plan?.description || '',
    monthly_price: props.plan?.monthly_price || 0,
    yearly_price: props.plan?.yearly_price || 0,
    is_active: props.plan?.is_active ?? true,
    is_featured: props.plan?.is_featured ?? false,
    features: [],
});

// Initialize features with values from the plan if editing
if (props.plan && props.features) {
    form.value.features = props.features.map(feature => {
        const planFeature = props.plan!.features.find(pf => pf.id === feature.id);
        return {
            id: feature.id,
            name: feature.name,
            type: feature.type,
            value: planFeature ? planFeature.pivot!.value : 
                   (feature.type === 'boolean' ? 'false' : '0')
        };
    });
} else if (props.features) {
    form.value.features = props.features.map(feature => ({
        id: feature.id,
        name: feature.name,
        type: feature.type,
        value: feature.type === 'boolean' ? 'false' : '0'
    }));
}

const isEditing = computed(() => !!props.plan);

function submit() {
    if (isEditing.value) {
        router.put(route('billing.plans.update', props.plan!.id), form.value, {
            onSuccess: () => emit('close')
        });
    } else {
        router.post(route('billing.plans.store'), form.value, {
            onSuccess: () => emit('close')
        });
    }
}
</script>

<template>
    <div class="fixed inset-0 overflow-y-auto bg-gray-500 bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-3xl">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ isEditing ? 'Edit Plan' : 'Create Plan' }}
                </h3>
            </div>

            <form @submit.prevent="submit">
                <div class="px-6 py-4 space-y-4">
                    <!-- Basic Plan Info -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Name
                        </label>
                        <input type="text" id="name" v-model="form.name"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Description
                        </label>
                        <textarea id="description" v-model="form.description" rows="3"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="monthly_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Monthly Price
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
                                </div>
                                <input type="number" id="monthly_price" v-model="form.monthly_price" step="0.01" min="0"
                                    class="pl-7 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            </div>
                        </div>

                        <div>
                            <label for="yearly_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Yearly Price
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
                                </div>
                                <input type="number" id="yearly_price" v-model="form.yearly_price" step="0.01" min="0"
                                    class="pl-7 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="is_active" v-model="form.is_active"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Active
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="is_featured" v-model="form.is_featured"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="is_featured" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Featured
                            </label>
                        </div>
                    </div>

                    <!-- Features Section -->
                    <div class="mt-6">
                        <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-3">
                            Plan Features
                        </h4>

                        <div v-for="feature in form.features" :key="feature.id" class="mb-2 p-3 border border-gray-200 dark:border-gray-700 rounded-md">
                            <div class="flex justify-between items-center">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ feature.name }}
                                </label>
                                <div class="flex items-center">
                                    <template v-if="feature.type === 'boolean'">
                                        <input type="checkbox" 
                                            :id="`feature-${feature.id}`"
                                            v-model="feature.value"
                                            true-value="true"
                                            false-value="false"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <label :for="`feature-${feature.id}`" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                            Enabled
                                        </label>
                                    </template>
                                    <template v-else>
                                        <input type="number" 
                                            :id="`feature-${feature.id}`"
                                            v-model="feature.value"
                                            class="block w-24 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            min="-1"
                                            step="1">
                                        <label :for="`feature-${feature.id}`" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                            (-1 for unlimited)
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 text-right rounded-b-lg">
                    <button type="button" @click="emit('close')" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-2">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ isEditing ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
