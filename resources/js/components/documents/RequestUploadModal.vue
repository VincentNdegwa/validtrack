<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { DocumentType, Subject } from '@/types/models';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    subject: Subject;
    documentTypes?: DocumentType[];
    show: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits(['close']);

// Track selected document types with their requirements
const selectedDocTypes = ref<Array<{ id: number | string; name: string; required: boolean }>>([]);

const form = useForm({
    subject_id: props.subject?.id,
    document_types: [] as Array<{ id: number | string; required: boolean }>,
    email: props.subject?.email || '',
    expiry_hours: 24,
});

// Methods to manage document types
const addDocumentType = (docType: DocumentType) => {
    const alreadyExists = selectedDocTypes.value.some(item => item.id === docType.id);
    if (!alreadyExists) {
        selectedDocTypes.value.push({
            id: docType.id,
            name: docType.name,
            required: true, // Default to required
        });
        updateFormDocTypes();
    }
};

const removeDocumentType = (docTypeId: number | string) => {
    selectedDocTypes.value = selectedDocTypes.value.filter(item => item.id !== docTypeId);
    updateFormDocTypes();
};

const toggleRequired = (docTypeId: number | string) => {
    const docType = selectedDocTypes.value.find(item => item.id === docTypeId);
    if (docType) {
        docType.required = !docType.required;
        updateFormDocTypes();
    }
};

const updateFormDocTypes = () => {
    form.document_types = selectedDocTypes.value.map(item => ({
        id: item.id,
        required: item.required,
    }));
};

// Close modal and reset form
const closeModal = () => {
    emit('close');
    form.reset();
    selectedDocTypes.value = [];
    form.clearErrors();
};

const submit = () => {
    // Make sure we have at least one document type
    if (selectedDocTypes.value.length === 0) {
        alert('Please select at least one document type');
        return;
    }

    form.post('/document-upload-requests', {
        onSuccess: () => {
            closeModal();
        },
    });
};

// Expiry options
const expiryOptions = [
    { value: 24, label: '24 hours' },
    { value: 48, label: '48 hours' },
    { value: 72, label: '3 days' },
    { value: 168, label: '1 week' },
];
</script>

<template>
    <Dialog :open="show" @update:open="closeModal">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Request Document Upload</DialogTitle>
                <DialogDescription>
                    Send a secure document upload link to {{ subject.name }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="mt-4 space-y-4">
                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium">Email Address</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                        :class="{ 'border-red-500': form.errors.email }"
                        placeholder="Enter recipient email"
                        required
                    />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium">Required Documents</label>
                    
                    <!-- Document type selector -->
                    <div class="flex gap-2">
                        <select
                            id="document_type_selector"
                            class="flex-1 rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                            :class="{ 'border-red-500': form.errors.document_types }"
                        >
                            <option value="" disabled selected>Select document type</option>
                            <option
                                v-for="docType in props.documentTypes"
                                :key="docType.id"
                                :value="docType.id"
                                :disabled="selectedDocTypes.some(item => item.id === docType.id)"
                            >
                                {{ docType.name }}
                            </option>
                        </select>
                        <Button 
                            type="button"
                            @click="(e: MouseEvent) => {
                                const select = (e.target as HTMLElement).previousElementSibling as HTMLSelectElement;
                                const docType = props.documentTypes?.find(dt => dt.id.toString() === select.value);
                                if (docType) addDocumentType(docType);
                                select.value = '';
                            }"
                        >
                            Add
                        </Button>
                    </div>
                    
                    <!-- Selected document types -->
                    <div v-if="selectedDocTypes.length > 0" class="mt-3 rounded-md border border-border p-2">
                        <p class="mb-2 text-sm font-medium">Selected Document Types:</p>
                        <ul class="space-y-2">
                            <li 
                                v-for="docType in selectedDocTypes" 
                                :key="docType.id"
                                class="flex items-center justify-between rounded-md bg-muted/30 px-3 py-2 text-sm"
                            >
                                <div class="flex items-center">
                                    <input
                                        :id="`required-${docType.id}`"
                                        type="checkbox"
                                        :checked="docType.required"
                                        class="mr-2 h-4 w-4 cursor-pointer rounded border-gray-300"
                                        @change="toggleRequired(docType.id)"
                                    />
                                    <label :for="`required-${docType.id}`" class="cursor-pointer">
                                        {{ docType.name }} 
                                        <span v-if="docType.required" class="ml-1 text-xs font-medium text-amber-600">(Required)</span>
                                        <span v-else class="ml-1 text-xs font-medium text-gray-500">(Optional)</span>
                                    </label>
                                </div>
                                <button 
                                    type="button" 
                                    class="text-red-500 hover:text-red-700"
                                    @click="removeDocumentType(docType.id)"
                                >
                                    &times;
                                </button>
                            </li>
                        </ul>
                    </div>
                    
                    <p v-if="form.errors.document_types" class="mt-1 text-xs text-red-500">
                        {{ form.errors.document_types }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label for="expiry_hours" class="text-sm font-medium">Link Valid For</label>
                    <select
                        id="expiry_hours"
                        v-model="form.expiry_hours"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option v-for="option in expiryOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>

                <DialogFooter class="mt-6">
                    <Button type="button" variant="outline" @click="closeModal">Cancel</Button>
                    <Button
                        type="submit"
                        class="bg-primary text-primary-foreground hover:bg-primary/90"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Sending...</span>
                        <span v-else>Send Upload Request</span>
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
