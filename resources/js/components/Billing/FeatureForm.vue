<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

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
            onSuccess: () => emit('close'),
        });
    } else {
        router.post(route('billing.features.store'), form.value, {
            onSuccess: () => emit('close'),
        });
    }
}
</script>

<template>
    <div class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-500 p-4">
        <div class="w-full max-w-md rounded-lg bg-white shadow-xl dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ isEditing ? 'Edit Feature' : 'Create Feature' }}
                </h3>
            </div>

            <form @submit.prevent="submit">
                <div class="space-y-4 px-6 py-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Name </label>
                        <input
                            type="text"
                            id="name"
                            v-model="form.name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        />
                    </div>

                    <div>
                        <label for="key" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Key (Unique Identifier) </label>
                        <input
                            type="text"
                            id="key"
                            v-model="form.key"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use a slug format like "document-storage" or "max-users"</p>
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Type </label>
                        <select
                            id="type"
                            v-model="form.type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        >
                            <option value="boolean">Boolean (On/Off)</option>
                            <option value="number">Number (Quantity)</option>
                        </select>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Description </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        ></textarea>
                    </div>
                </div>

                <div class="rounded-b-lg bg-gray-50 px-6 py-4 text-right dark:bg-gray-700">
                    <button
                        type="button"
                        @click="emit('close')"
                        class="mr-2 rounded-md border border-gray-300 bg-white px-4 py-2 font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="rounded-md border border-transparent bg-indigo-600 px-4 py-2 font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                    >
                        {{ isEditing ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
