<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type DocumentType, type Subject } from '@/types/models';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    subjects?: Subject[];
    documentTypes?: DocumentType[];
    selectedSubject?: Subject | null;
}

const props = defineProps<Props>();

const form = useForm({
    subject_id: props.selectedSubject?.id?.toString() || '',
    document_type_id: '',
    file: null as File | null,
    issue_date: new Date().toISOString().substring(0, 10), // Today's date in YYYY-MM-DD format
    expiry_date: '',
    status: 1,
    notes: '',
});

const fileInputRef = ref<HTMLInputElement | null>(null);
const filePreview = ref<string | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Documents',
        href: '/documents',
    },
    {
        title: 'Upload Document',
        href: '/documents/create',
    },
];

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

const submit = () => {
    form.post('/documents', {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            filePreview.value = null;
        },
    });
};
</script>

<template>
    <Head title="Upload Document" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-foreground">Upload Document</h1>
            </div>

            <div class="rounded-xl border border-border bg-card p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <label for="subject_id" class="block text-sm font-medium">Subject</label>
                            <select
                                id="subject_id"
                                v-model="form.subject_id"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                                :class="{ 'border-red-500': form.errors.subject_id }"
                                required
                            >
                                <option value="">Select Subject</option>
                                <option v-for="subject in props.subjects" :key="subject.id" :value="subject.id">{{ subject.name }}</option>
                            </select>
                            <p v-if="form.errors.subject_id" class="mt-1 text-xs text-red-500">{{ form.errors.subject_id }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="document_type_id" class="block text-sm font-medium">Document Type</label>
                            <select
                                id="document_type_id"
                                v-model="form.document_type_id"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                                :class="{ 'border-red-500': form.errors.document_type_id }"
                            >
                                <option value="">Select Type</option>
                                <option v-for="type in props.documentTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                            </select>
                            <p v-if="form.errors.document_type_id" class="mt-1 text-xs text-red-500">{{ form.errors.document_type_id }}</p>
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
                            <label for="expiry_date" class="block text-sm font-medium">Expiry Date</label>
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
                            <label for="status" class="block text-sm font-medium">Status</label>
                            <select
                                id="status"
                                v-model="form.status"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                                :class="{ 'border-red-500': form.errors.status }"
                            >
                                <option :value="1">Valid</option>
                                <option :value="2">Pending</option>
                                <option :value="3">Expired</option>
                            </select>
                            <p v-if="form.errors.status" class="mt-1 text-xs text-red-500">{{ form.errors.status }}</p>
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
                                <button v-if="form.file" type="button" @click="clearFile" class="text-sm text-red-500 hover:text-red-700">
                                    Clear
                                </button>
                            </div>
                            <input id="file" ref="fileInputRef" type="file" @change="onFileChange" class="hidden" />
                            <div v-if="form.file" class="mt-2 text-sm">
                                Selected: {{ form.file.name }} ({{ (form.file.size / 1024).toFixed(2) }} KB)
                            </div>
                            <p v-if="form.errors.file" class="mt-1 text-xs text-red-500">{{ form.errors.file }}</p>

                            <div v-if="filePreview" class="mt-4 max-h-32 overflow-hidden rounded-md border border-border">
                                <img :src="filePreview" alt="File Preview" class="h-auto max-w-full object-contain" />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="notes" class="block text-sm font-medium">Notes</label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="4"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:ring-2 focus:ring-primary focus:outline-none"
                            :class="{ 'border-red-500': form.errors.notes }"
                        ></textarea>
                        <p v-if="form.errors.notes" class="mt-1 text-xs text-red-500">{{ form.errors.notes }}</p>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <Button type="button" variant="outline" :href="route('documents.index')">Cancel</Button>
                        <Button type="submit" class="bg-primary text-primary-foreground hover:bg-primary/90" :disabled="form.processing">
                            <span v-if="form.processing">Uploading...</span>
                            <span v-else>Upload Document</span>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
