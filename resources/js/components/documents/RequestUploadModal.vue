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
import { ref, watch } from 'vue';

interface Props {
    subject: Subject;
    documentTypes?: DocumentType[];
    show: boolean;
    preSelectedDocumentTypes?: Array<any>;
}

const props = defineProps<Props>();
const emit = defineEmits(['close']);

const selectedDocTypes = ref<Array<{ id: number | string; name: string; required: boolean }>>([]);
const formError = ref<string | null>(null);

const form = useForm({
    subject_id: props.subject?.id,
    document_types: [] as Array<{ id: number | string; required: boolean }>,
    email: props.subject?.email || '',
    expiry_hours: 24,
});

const addDocumentType = (docType: DocumentType) => {
    const alreadyExists = selectedDocTypes.value.some(item => item.id === docType.id);
    if (!alreadyExists) {
        selectedDocTypes.value.push({
            id: docType.id,
            name: docType.name,
            required: true,
        });
        updateFormDocTypes();
        formError.value = null;
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

const initializeSelectedDocTypes = () => {
    if (props.preSelectedDocumentTypes && props.preSelectedDocumentTypes.length > 0 && props.documentTypes) {
        selectedDocTypes.value = [];
        
        props.preSelectedDocumentTypes.forEach(preSelectedDoc => {
            const docTypeId = preSelectedDoc.document_type_id;
            const matchingDocType = props.documentTypes?.find(dt => dt.id === docTypeId);
            
            if (matchingDocType) {
                const alreadyExists = selectedDocTypes.value.some(item => item.id === matchingDocType.id);
                if (!alreadyExists) {
                    selectedDocTypes.value.push({
                        id: matchingDocType.id,
                        name: matchingDocType.name,
                        required: true,
                    });
                }
            }
        });
        
        updateFormDocTypes();
    }
};

watch(() => props.show, (newVal) => {
    if (newVal && props.preSelectedDocumentTypes?.length) {
        initializeSelectedDocTypes();
    }
}, { immediate: true });

const closeModal = () => {
    emit('close');
    form.reset();
    selectedDocTypes.value = [];
    form.clearErrors();
    formError.value = null;
};

const submit = () => {
    formError.value = null;
    
    if (selectedDocTypes.value.length === 0) {
        formError.value = 'Please select at least one document type';
        return;
    }

    form.post('/document-upload-requests', {
        onSuccess: () => {
            closeModal();
        },
    });
};

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
                <div v-if="formError" class="rounded-md bg-destructive/10 p-3 text-sm text-destructive border border-destructive/20">
                    {{ formError }}
                </div>
            
                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium flex items-center">
                        Email Address
                        <span class="ml-1 text-destructive">*</span>
                    </label>
                    <div class="relative">
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                            :class="{ 'border-destructive ring-1 ring-destructive': form.errors.email }"
                            placeholder="Enter recipient email"
                            required
                        />
                        <div v-if="form.errors.email" class="absolute right-2 top-1/2 -translate-y-1/2 text-destructive">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                        </div>
                    </div>
                    <p v-if="form.errors.email" class="mt-1 text-xs text-destructive">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium flex items-center">
                        Document Types
                        <span class="ml-1 text-destructive">*</span>
                    </label>
                    
                    <div class="flex gap-2">
                        <select
                            id="document_type_selector"
                            class="flex-1 rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                            :class="{ 'border-destructive ring-1 ring-destructive': form.errors.document_types }"
                        >
                            <option value="" disabled selected>Select document type</option>
                            <option
                                v-for="docType in documentTypes"
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
                                const docType = documentTypes?.find(dt => dt.id.toString() === select.value);
                                if (docType) addDocumentType(docType);
                                select.value = '';
                            }"
                        >
                            Add
                        </Button>
                    </div>
                    
                    <div v-if="selectedDocTypes.length > 0" class="mt-3 rounded-md border border-border p-3">
                        <p class="mb-2 text-sm font-medium text-muted-foreground">Selected Documents:</p>
                        <ul class="space-y-2">
                            <li 
                                v-for="docType in selectedDocTypes" 
                                :key="docType.id"
                                class="flex items-center justify-between rounded-md bg-muted/40 px-3 py-2 text-sm shadow-sm"
                            >
                                <div class="flex items-center">
                                    <div class="mr-2 flex h-4 w-4 items-center justify-center">
                                        <input
                                            :id="`required-${docType.id}`"
                                            type="checkbox"
                                            :checked="docType.required"
                                            class="h-4 w-4 cursor-pointer rounded border-gray-300 text-primary focus:ring-primary"
                                            @change="toggleRequired(docType.id)"
                                        />
                                    </div>
                                    <label :for="`required-${docType.id}`" class="cursor-pointer">
                                        {{ docType.name }} 
                                        <span 
                                            class="ml-1 rounded-full px-2 py-0.5 text-xs font-medium"
                                            :class="docType.required ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600'"
                                        >
                                            {{ docType.required ? 'Required' : 'Optional' }}
                                        </span>
                                    </label>
                                </div>
                                <button 
                                    type="button" 
                                    class="rounded-full p-1 text-destructive/70 hover:bg-muted hover:text-destructive transition-colors duration-200"
                                    @click="removeDocumentType(docType.id)"
                                    title="Remove document type"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 6L6 18"></path>
                                        <path d="M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </li>
                        </ul>
                    </div>
                    
                    <p v-if="form.errors.document_types" class="mt-1 text-xs text-destructive">
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
                    <Button type="button" variant="outline" @click="closeModal" class="transition-all duration-200">Cancel</Button>
                    <Button
                        type="submit"
                        class="ml-2 transition-all duration-200"
                        :disabled="form.processing"
                    >
                        <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Sending...' : 'Send Upload Request' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
