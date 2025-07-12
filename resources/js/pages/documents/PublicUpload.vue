<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

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

const selectedDocType = ref<DocumentTypeItem | null>(null);
const formError = ref<string | null>(null);

const form = useForm({
    verification_code: '',
    upload_request_item_id: '',
    file: null as File | null,
    issue_date: new Date().toISOString().substring(0, 10),
    expiry_date: '',
    notes: '',
});

const fileInputRef = ref<HTMLInputElement | null>(null);
const filePreview = ref<string | null>(null);
const timerInterval = ref<number | null>(null);

const onFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        form.file = input.files[0];

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

const selectDocumentType = (docType: DocumentTypeItem) => {
    selectedDocType.value = docType;
    form.upload_request_item_id = docType.id.toString();
    formError.value = null;
};

const isDocTypeCompleted = (docType: DocumentTypeItem): boolean => {
    return docType.status === 'completed';
};

const pendingRequiredCount = computed(() => {
    return props.uploadRequest.documentTypes.filter((dt) => dt.required && dt.status === 'pending').length;
});

const submit = () => {
    formError.value = null;

    if (!form.upload_request_item_id) {
        formError.value = 'Please select a document type to upload';
        return;
    }

    if (!form.file) {
        formError.value = 'Please select a file to upload';
        return;
    }

    form.post(`/document-upload/${props.uploadRequest.token}`, {
        forceFormData: true,
        onSuccess: () => {
            const verificationCode = form.verification_code;
            form.reset();
            form.verification_code = verificationCode;
            selectedDocType.value = null;
            filePreview.value = null;
        },
        onError: () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    });
};

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

onMounted(() => {
    timerInterval.value = window.setInterval(() => {
        timeRemaining.value = computeTimeRemaining();
    }, 60000);
});

onBeforeUnmount(() => {
    if (timerInterval.value) {
        clearInterval(timerInterval.value);
    }
});
</script>

<template>
    <Head title="Document Upload" />

    <div class="flex min-h-screen flex-col items-center justify-center bg-background p-4">
        <div class="w-full max-w-2xl rounded-xl border border-border bg-card p-6 shadow-lg transition-shadow duration-300 hover:shadow-xl">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-foreground">Document Upload</h1>
                <p class="mt-2 text-muted-foreground">
                    Upload documents for <span class="font-medium text-primary">{{ uploadRequest.subject.name }}</span>
                </p>

                <!-- Form errors -->
                <div v-if="Object.keys(form.errors).length > 0" class="mt-4 rounded-lg border border-destructive/20 bg-destructive/10 p-3 text-left">
                    <div class="flex items-start">
                        <svg
                            class="h-5 w-5 flex-shrink-0 text-destructive"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-destructive">Please fix the following errors:</h3>
                            <ul class="mt-2 list-disc space-y-1 pl-5 text-xs text-destructive">
                                <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Custom form error -->
                <div v-if="formError" class="mt-4 rounded-lg border border-destructive/20 bg-destructive/10 p-3 text-left">
                    <div class="flex items-start">
                        <svg
                            class="h-5 w-5 flex-shrink-0 text-destructive"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-destructive">{{ formError }}</p>
                        </div>
                    </div>
                </div>

                <!-- Timer and required document info -->
                <div class="mt-4 flex flex-col gap-2">
                    <div v-if="pendingRequiredCount > 0" class="rounded-full bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-800">
                        {{ pendingRequiredCount }} required document{{ pendingRequiredCount !== 1 ? 's' : '' }} pending
                    </div>

                    <div class="rounded-full bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-800">
                        {{ timeRemaining }}
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Document Types Selection -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium">Select Document to Upload</label>

                    <div class="rounded-lg border border-border p-3">
                        <div class="mb-2 flex justify-between text-xs font-medium text-muted-foreground">
                            <span>Document Type</span>
                            <span>Status</span>
                        </div>

                        <div class="space-y-2">
                            <div
                                v-for="docType in uploadRequest.documentTypes"
                                :key="docType.id"
                                class="flex cursor-pointer items-center justify-between rounded-md p-3 transition-all duration-200"
                                :class="{
                                    'bg-green-100 text-green-800': isDocTypeCompleted(docType),
                                    'hover:bg-muted/60': !isDocTypeCompleted(docType) && selectedDocType?.id !== docType.id,
                                    'bg-primary/10 ring-1 ring-primary': selectedDocType?.id === docType.id,
                                    'cursor-not-allowed opacity-80': isDocTypeCompleted(docType),
                                }"
                                @click="!isDocTypeCompleted(docType) && selectDocumentType(docType)"
                            >
                                <div class="flex items-center">
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ docType.name }}</span>
                                        <span v-if="docType.required" class="text-xs font-medium text-destructive">Required</span>
                                        <span v-else class="text-xs text-muted-foreground">Optional</span>
                                    </div>
                                </div>
                                <div>
                                    <span
                                        v-if="isDocTypeCompleted(docType)"
                                        class="rounded-full bg-green-200 px-2.5 py-1 text-xs font-medium text-green-800 shadow-sm"
                                    >
                                        Uploaded
                                    </span>
                                    <span v-else class="rounded-full bg-amber-200 px-2.5 py-1 text-xs font-medium text-amber-800 shadow-sm">
                                        Pending
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="verification_code" class="block text-sm font-medium">
                        Verification Code <span class="text-destructive">*</span>
                    </label>
                    <div class="relative">
                        <input
                            id="verification_code"
                            v-model="form.verification_code"
                            type="text"
                            placeholder="Enter the 6-digit code"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                            :class="{ 'border-destructive ring-1 ring-destructive': form.errors.verification_code }"
                            required
                        />
                        <div v-if="form.errors.verification_code" class="absolute top-1/2 right-2 -translate-y-1/2 text-destructive">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="h-4 w-4"
                            >
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                        </div>
                    </div>
                    <p v-if="form.errors.verification_code" class="mt-1 text-xs text-destructive">
                        {{ form.errors.verification_code }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="issue_date" class="block text-sm font-medium"> Issue Date <span class="text-destructive">*</span> </label>
                        <input
                            id="issue_date"
                            v-model="form.issue_date"
                            type="date"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                            :class="{ 'border-destructive ring-1 ring-destructive': form.errors.issue_date }"
                            required
                        />
                        <p v-if="form.errors.issue_date" class="mt-1 text-xs text-destructive">{{ form.errors.issue_date }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="expiry_date" class="block text-sm font-medium">Expiry Date (if applicable)</label>
                        <input
                            id="expiry_date"
                            v-model="form.expiry_date"
                            type="date"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                            :class="{ 'border-destructive ring-1 ring-destructive': form.errors.expiry_date }"
                        />
                        <p v-if="form.errors.expiry_date" class="mt-1 text-xs text-destructive">{{ form.errors.expiry_date }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium">Document File <span class="text-destructive">*</span></label>
                    <div
                        class="relative rounded-lg border-2 border-dashed border-muted-foreground/25 p-6 transition-all duration-200 hover:border-primary/50"
                        :class="{ 'border-destructive/50 bg-destructive/5': form.errors.file }"
                    >
                        <div v-if="!filePreview" class="flex flex-col items-center justify-center text-center">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="mb-2 h-10 w-10 text-muted-foreground/70"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                />
                            </svg>
                            <p class="mb-1 text-sm font-medium">Drop files here or click to upload</p>
                            <p class="text-xs text-muted-foreground">PDF, DOC, DOCX, JPG or PNG (max. 10MB)</p>
                        </div>

                        <div v-else class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="rounded-md bg-primary/10 p-2">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6 text-primary"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p class="max-w-[180px] truncate text-sm font-medium">{{ form.file?.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ form.file ? (form.file.size / 1024).toFixed(2) + ' KB' : '' }}</p>
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="clearFile"
                                class="rounded-full bg-muted p-1.5 text-muted-foreground transition-colors duration-200 hover:bg-muted/80 hover:text-destructive"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <input
                            id="file"
                            ref="fileInputRef"
                            type="file"
                            @change="onFileChange"
                            class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                        />
                    </div>
                    <p v-if="form.errors.file" class="mt-1 text-xs text-destructive">{{ form.errors.file }}</p>

                    <div
                        v-if="filePreview && form.file?.type.includes('image')"
                        class="mt-4 max-h-40 overflow-hidden rounded-md border border-border"
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
                        placeholder="Add any additional information about this document"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                        :class="{ 'border-destructive ring-1 ring-destructive': form.errors.notes }"
                    ></textarea>
                    <p v-if="form.errors.notes" class="mt-1 text-xs text-destructive">{{ form.errors.notes }}</p>
                </div>

                <Button type="submit" class="w-full transition-all duration-300" :disabled="form.processing">
                    <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                    {{ form.processing ? 'Uploading...' : 'Upload Document' }}
                </Button>
            </form>

            <div class="mt-6 text-center text-sm text-muted-foreground">
                <p>This is a secure one-time upload link that will expire after use.</p>
            </div>
        </div>
    </div>
</template>
