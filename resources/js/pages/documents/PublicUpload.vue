<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface DocumentTypeItem {
    id: number;
    document_type_id: number;
    name: string;
    required: boolean;
    status: string;
}

interface Props {
    uploadRequest: {
        token: string;
        subject: {
            id: number;
            name: string;
        };
        documentTypes: DocumentTypeItem[];
        expiresAt: string;
    };
}

const props = defineProps<Props>();

// Store selected document type
const selectedDocType = ref<DocumentTypeItem | null>(null);

const form = useForm({
    verification_code: '',
    upload_request_item_id: '',
    file: null as File | null,
    issue_date: new Date().toISOString().substring(0, 10), // Today's date in YYYY-MM-DD format
    expiry_date: '',
    notes: '',
});

const fileInputRef = ref<HTMLInputElement | null>(null);
const filePreview = ref<string | null>(null);

const onFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        form.file = input.files[0];

        // Create preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            filePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(input.files[0]);
    }
};

const clearFile = () => {
    form.file = null;
    filePreview.value = null;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

// Set selected document type
const selectDocumentType = (docType: DocumentTypeItem) => {
    selectedDocType.value = docType;
    form.upload_request_item_id = docType.id.toString();
};

// Check if a document type is completed
const isDocTypeCompleted = (docType: DocumentTypeItem): boolean => {
    return docType.status === 'completed';
};

// Count pending required documents
const pendingRequiredCount = computed(() => {
    return props.uploadRequest.documentTypes.filter(dt => dt.required && dt.status === 'pending').length;
});

const submit = () => {
    // Make sure a document type is selected
    if (!form.upload_request_item_id) {
        alert('Please select a document type to upload');
        return;
    }
    
    form.post(`/document-upload/${props.uploadRequest.token}`, {
        forceFormData: true,
        onSuccess: () => {
            // Reset form but keep verification code
            const verificationCode = form.verification_code;
            form.reset();
            form.verification_code = verificationCode;
            selectedDocType.value = null;
            filePreview.value = null;
        },
    });
};

// Compute time remaining until expiry
const computeTimeRemaining = () => {
    const expiryDate = new Date(props.uploadRequest.expiresAt);
    const now = new Date();
    const diff = expiryDate.getTime() - now.getTime();
    
    if (diff <= 0) return 'Expired';
    
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    
    return `${hours}h ${minutes}m remaining`;
};

const timeRemaining = ref(computeTimeRemaining());

// Update time remaining every minute
setInterval(() => {
    timeRemaining.value = computeTimeRemaining();
}, 60000);
</script>

<template>
    <Head title="Document Upload" />

    <div class="flex min-h-screen flex-col items-center justify-center p-4">
        <div class="w-full max-w-md rounded-xl border border-border bg-card p-6 shadow-lg">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-foreground">Document Upload</h1>
                <p class="mt-2 text-muted-foreground">
                    Upload documents for <span class="font-medium">{{ props.uploadRequest.subject.name }}</span>
                </p>
                
                <div v-if="pendingRequiredCount > 0" class="mt-2 text-sm text-amber-600">
                    {{ pendingRequiredCount }} required document{{ pendingRequiredCount !== 1 ? 's' : '' }} pending
                </div>
                
                <div class="mt-4 rounded-md p-2 text-sm text-amber-700">
                    <p>{{ timeRemaining }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Document Types Selection -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium">Select Document to Upload</label>
                    
                    <div class="rounded-md border border-border p-2">
                        <div class="mb-2 flex justify-between text-xs">
                            <span>Document Type</span>
                            <span>Status</span>
                        </div>
                        
                        <div class="space-y-2">
                            <div 
                                v-for="docType in props.uploadRequest.documentTypes" 
                                :key="docType.id"
                                class="flex cursor-pointer items-center justify-between rounded-md p-2 hover:bg-muted/50"
                                :class="[
                                    isDocTypeCompleted(docType) ? 'bg-green-500' : '',
                                    selectedDocType?.id === docType.id ? 'ring-2 ring-primary' : '',
                                    isDocTypeCompleted(docType) ? 'cursor-not-allowed opacity-70' : ''
                                ]"
                                @click="!isDocTypeCompleted(docType) && selectDocumentType(docType)"
                            >
                                <div class="flex items-center">
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ docType.name }}</span>
                                        <span v-if="docType.required" class="text-xs text-red-500">Required</span>
                                        <span v-else class="text-xs text-muted-foreground">Optional</span>
                                    </div>
                                </div>
                                <div>
                                    <span 
                                        v-if="isDocTypeCompleted(docType)"
                                        class="rounded-full bg-green-300 px-2 py-1 text-xs text-green-800"
                                    >
                                        Uploaded
                                    </span>
                                    <span 
                                        v-else
                                        class="rounded-full bg-amber-400 px-2 py-1 text-xs text-amber-800"
                                    >
                                        Pending
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <p v-if="form.errors.upload_request_item_id" class="mt-1 text-xs text-red-500">
                        {{ form.errors.upload_request_item_id }}
                    </p>
                </div>
                
                <div class="space-y-2">
                    <label for="verification_code" class="block text-sm font-medium">Verification Code</label>
                    <input
                        id="verification_code"
                        v-model="form.verification_code"
                        type="text"
                        placeholder="Enter the 6-digit code"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                        :class="{ 'border-red-500': form.errors.verification_code }"
                        required
                    />
                    <p v-if="form.errors.verification_code" class="mt-1 text-xs text-red-500">
                        {{ form.errors.verification_code }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label for="issue_date" class="block text-sm font-medium">Issue Date</label>
                    <input
                        id="issue_date"
                        v-model="form.issue_date"
                        type="date"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                        :class="{ 'border-red-500': form.errors.issue_date }"
                        required
                    />
                    <p v-if="form.errors.issue_date" class="mt-1 text-xs text-red-500">{{ form.errors.issue_date }}</p>
                </div>

                <div class="space-y-2">
                    <label for="expiry_date" class="block text-sm font-medium">Expiry Date (if applicable)</label>
                    <input
                        id="expiry_date"
                        v-model="form.expiry_date"
                        type="date"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                        :class="{ 'border-red-500': form.errors.expiry_date }"
                    />
                    <p v-if="form.errors.expiry_date" class="mt-1 text-xs text-red-500">{{ form.errors.expiry_date }}</p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium">Document File</label>
                    <div class="flex items-center space-x-2">
                        <label
                            for="file"
                            class="cursor-pointer rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                        >
                            Select File
                        </label>
                        <button
                            v-if="form.file"
                            type="button"
                            @click="clearFile"
                            class="text-sm text-red-500 hover:text-red-700"
                        >
                            Clear
                        </button>
                    </div>
                    <input id="file" ref="fileInputRef" type="file" @change="onFileChange" class="hidden" />
                    <div v-if="form.file" class="mt-2 text-sm">
                        Selected: {{ form.file.name }} ({{ (form.file.size / 1024).toFixed(2) }} KB)
                    </div>
                    <p v-if="form.errors.file" class="mt-1 text-xs text-red-500">{{ form.errors.file }}</p>

                    <div
                        v-if="filePreview"
                        class="mt-4 max-h-32 overflow-hidden rounded-md border border-border"
                    >
                        <img :src="filePreview" alt="File Preview" class="h-auto max-w-full object-contain" />
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="notes" class="block text-sm font-medium">Notes</label>
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="3"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                        :class="{ 'border-red-500': form.errors.notes }"
                    ></textarea>
                    <p v-if="form.errors.notes" class="mt-1 text-xs text-red-500">{{ form.errors.notes }}</p>
                </div>

                <Button
                    type="submit"
                    class="w-full bg-primary text-primary-foreground hover:bg-primary/90"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Uploading...</span>
                    <span v-else>Upload Document</span>
                </Button>
            </form>

            <div class="mt-6 text-center text-sm text-muted-foreground">
                <p>This is a secure upload link that will expire after use.</p>
            </div>
        </div>
    </div>
</template>
