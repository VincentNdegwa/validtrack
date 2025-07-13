<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

interface Feature {
    id: number;
    name: string;
    key: string;
    type: string;
    description?: string;
}

interface FeatureForm {
    name: string;
    key: string;
    type: string;
    description: string;
}

const props = defineProps<{
    feature?: Feature;
}>();

const emit = defineEmits<{
    close: [];
}>();

const form = ref<FeatureForm>({
    name: props.feature?.name || '',
    key: props.feature?.key || '',
    type: props.feature?.type || 'boolean',
    description: props.feature?.description || '',
});

const isEditing = !!props.feature;

function submit(): void {
    if (isEditing) {
        router.put(route('billing.features.update', props.feature!.id), form.value, {
            onSuccess: () => emit('close')
        });
    } else {
        router.post(route('billing.features.store'), form.value, {
            onSuccess: () => emit('close')
        });
    }
}
</script>

<template>
    <div class="fixed inset-0 overflow-y-auto bg-gray-500 bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ isEditing ? 'Edit Feature' : 'Create Feature' }}
                </h3>
            </div>

            <form @submit.prevent="submit">
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Name
                        </label>
                        <input type="text" id="name" v-model="form.name"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    </div>

                    <div>
                        <label for="key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Key (Unique Identifier)
                        </label>
                        <input type="text" id="key" v-model="form.key"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Use a slug format like "document-storage" or "max-users"
                        </p>
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Type
                        </label>
                        <select id="type" v-model="form.type"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="boolean">Boolean (On/Off)</option>
                            <option value="number">Number (Quantity)</option>
                        </select>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Description
                        </label>
                        <textarea id="description" v-model="form.description" rows="3"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
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
