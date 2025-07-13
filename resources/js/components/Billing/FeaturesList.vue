<script setup lang="ts">
import { router } from '@inertiajs/vue3';

interface Feature {
    id: number;
    name: string;
    key: string;
    type: string;
    description?: string;
}

const props = defineProps<{
    features?: Feature[];
}>();

const emit = defineEmits<{
    edit: [feature: Feature];
}>();

function editFeature(feature: Feature): void {
    emit('edit', feature);
}

function deleteFeature(feature: Feature): void {
    if (confirm(`Are you sure you want to delete the "${feature.name}" feature?`)) {
        router.delete(route('billing.features.destroy', feature.id));
    }
}
</script>

<template>
    <div>
        <div v-if="!props.features || props.features.length === 0" class="py-6 text-center">
            <p class="text-gray-600 dark:text-gray-300">No billing features found. Create your first feature!</p>
        </div>
        <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                            Key
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                            Type
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                            Description
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    <tr v-for="feature in features" :key="feature.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ feature.name }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                {{ feature.key }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold"
                                :class="feature.type === 'boolean' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                            >
                                {{ feature.type }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                {{ feature.description }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                            <button @click="editFeature(feature)" class="mr-3 text-blue-600 hover:text-blue-900">Edit</button>
                            <button @click="deleteFeature(feature)" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
