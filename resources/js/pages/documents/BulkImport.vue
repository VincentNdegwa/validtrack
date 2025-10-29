<template>
    <Head title="Bulk Import Documents" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-foreground">Bulk Import Documents</h1>
                <Button variant="outline" as-child>
                    <Link :href="route('documents.index')">Back to Documents</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Instructions</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <ul class="list-disc list-inside space-y-2 text-sm text-muted-foreground">
                            <li>Add document information and upload files</li>
                            <li>Each record requires a document file, subject, and document type</li>
                            <li>Maximum 10 records per import (due to file size limits)</li>
                            <li>Each file size limit: 10MB</li>
                            <li>Supported file types: PDF, Images, Office Documents</li>
                        </ul>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Global Settings</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="bulk_subject">Default Subject</Label>
                                <Select 
                                    id="bulk_subject" 
                                    v-model="bulkSubject"
                                    @change="applyBulkSubject"
                                >
                                    <option value="">Select a subject</option>
                                    <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                        {{ subject.name }}
                                    </option>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <Label for="bulk_document_type">Default Document Type</Label>
                                <Select 
                                    id="bulk_document_type" 
                                    v-model="bulkDocumentType"
                                    @change="applyBulkDocumentType"
                                >
                                    <option value="">Select a type</option>
                                    <option v-for="type in documentTypes" :key="type.id" :value="type.id">
                                        {{ type.name }}
                                    </option>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <Label for="bulk_status">Default Status</Label>
                                <Select 
                                    id="bulk_status" 
                                    v-model="bulkStatus"
                                    @change="applyBulkStatus"
                                >
                                    <option value="1">Active</option>
                                    <option value="2">Pending Review</option>
                                    <option value="3">Expired</option>
                                    <option value="0">Inactive</option>
                                </Select>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <Button 
                                    type="button" 
                                    variant="outline" 
                                    @click="addRecord"
                                    :disabled="records.length >= 10"
                                >
                                    <Plus class="mr-2 h-4 w-4" />
                                    Add Document Record
                                </Button>
                                <div v-if="records.length >= 10" class="mt-2 text-sm text-muted-foreground">
                                    Maximum 10 records allowed per import
                                </div>
                            </div>

                            <div v-for="(record, index) in records" :key="index" class="p-4 border rounded-lg space-y-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium">Document {{ index + 1 }}</h4>
                                    <Button 
                                        variant="ghost" 
                                        size="icon"
                                        @click="removeRecord(index)"
                                    >
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>

                                <div class="grid gap-4 md:grid-cols-3">
                                    <div class="space-y-2">
                                        <Label :for="'subject_' + index">Subject</Label>
                                        <Select 
                                            :id="'subject_' + index"
                                            v-model="record.subject_id"
                                        >
                                            <option value="">Select a subject</option>
                                            <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                                                {{ subject.name }}
                                            </option>
                                        </Select>
                                        <div v-if="getError('records.' + index + '.subject_id')" class="mt-1 text-sm text-destructive">
                                            {{ getError('records.' + index + '.subject_id') }}
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <Label :for="'document_type_' + index">Document Type</Label>
                                        <Select 
                                            :id="'document_type_' + index"
                                            v-model="record.document_type_id"
                                        >
                                            <option value="">Select a type</option>
                                            <option v-for="type in documentTypes" :key="type.id" :value="type.id">
                                                {{ type.name }}
                                            </option>
                                        </Select>
                                        <div v-if="getError('records.' + index + '.document_type_id')" class="mt-1 text-sm text-destructive">
                                            {{ getError('records.' + index + '.document_type_id') }}
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <Label :for="'status_' + index">Status</Label>
                                        <Select 
                                            :id="'status_' + index"
                                            v-model="record.status"
                                        >
                                            <option value="1">Active</option>
                                            <option value="2">Pending Review</option>
                                            <option value="3">Expired</option>
                                            <option value="0">Inactive</option>
                                        </Select>
                                        <div v-if="getError('records.' + index + '.status')" class="mt-1 text-sm text-destructive">
                                            {{ getError('records.' + index + '.status') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label :for="'issue_date_' + index">Issue Date</Label>
                                        <Input 
                                            :id="'issue_date_' + index"
                                            type="date"
                                            v-model="record.issue_date"
                                        />
                                        <div v-if="getError('records.' + index + '.issue_date')" class="mt-1 text-sm text-destructive">
                                            {{ getError('records.' + index + '.issue_date') }}
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <Label :for="'expiry_date_' + index">Expiry Date</Label>
                                        <Input 
                                            :id="'expiry_date_' + index"
                                            type="date"
                                            v-model="record.expiry_date"
                                            :min="record.issue_date"
                                        />
                                        <div v-if="getError('records.' + index + '.expiry_date')" class="mt-1 text-sm text-destructive">
                                            {{ getError('records.' + index + '.expiry_date') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label :for="'file_' + index">Document File</Label>
                                    <Input 
                                        :id="'file_' + index"
                                        type="file"
                                        @change="e => handleFileChange(e, index)"
                                    />
                                    <div v-if="getError('records.' + index + '.file')" class="mt-1 text-sm text-destructive">
                                        {{ getError('records.' + index + '.file') }}
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label :for="'notes_' + index">Notes</Label>
                                    <Textarea 
                                        :id="'notes_' + index"
                                        v-model="record.notes"
                                        rows="2"
                                    />
                                </div>
                            </div>
                        </div>

                        <Button 
                            type="submit" 
                            :disabled="!isValidForSubmit || form.processing"
                            class="bg-primary text-primary-foreground hover:bg-primary/90"
                        >
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <span v-if="form.processing">Processing...</span>
                            <span v-else>Import Documents</span>
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <Card v-if="importResults">
                <CardHeader>
                    <CardTitle>Import Results</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div class="grid gap-2">
                            <div class="text-sm text-green-600 dark:text-green-400">
                                Successfully imported: {{ importResults.success }}
                            </div>
                            <div v-if="importResults.failed > 0" class="text-sm text-destructive">
                                Failed to import: {{ importResults.failed }}
                            </div>
                        </div>

                        <div v-if="importResults.errors?.length" class="mt-4">
                            <h4 class="font-medium mb-2">Errors:</h4>
                            <ul class="list-disc list-inside space-y-1 text-sm text-destructive">
                                <li v-for="error in importResults.errors" :key="error">
                                    {{ error }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Loader2, Plus, X } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface DocumentRecord {
    subject_id: string;
    document_type_id: string;
    file: File | null;
    issue_date: string;
    expiry_date: string | null;
    status: string;
    notes: string | null;
}

const importResults = ref<{
    success: number;
    failed: number;
    errors?: string[];
} | null>(null);

const records = ref<DocumentRecord[]>([]);

const bulkSubject = ref('');
const bulkDocumentType = ref('');
const bulkStatus = ref('1');

const form = useForm({
    records: [] as any[]
});

const props = defineProps<{
    subjects: Array<{
        id: number;
        name: string;
    }>;
    documentTypes: Array<{
        id: number;
        name: string;
    }>;
}>();

const isValidForSubmit = computed(() => {
    if (!records.value.length || form.processing) return false;
    
    // Check if all records have required fields
    return records.value.every(record => 
        record.subject_id && 
        record.document_type_id &&
        record.file &&
        record.issue_date &&
        record.status
    );
});

const getError = (path: string) => {
    return form.errors[path];
};

const addRecord = () => {
    if (records.value.length >= 10) return;

    records.value.push({
        subject_id: bulkSubject.value,
        document_type_id: bulkDocumentType.value,
        file: null,
        issue_date: new Date().toISOString().split('T')[0],
        expiry_date: null,
        status: bulkStatus.value,
        notes: null
    });
};

const removeRecord = (index: number) => {
    records.value.splice(index, 1);
};

const handleFileChange = (e: Event, index: number) => {
    const target = e.target as HTMLInputElement;
    if (target.files?.length) {
        records.value[index].file = target.files[0];
    }
};

const applyBulkSubject = () => {
    if (bulkSubject.value) {
        records.value = records.value.map(record => ({
            ...record,
            subject_id: bulkSubject.value
        }));
    }
};

const applyBulkDocumentType = () => {
    if (bulkDocumentType.value) {
        records.value = records.value.map(record => ({
            ...record,
            document_type_id: bulkDocumentType.value
        }));
    }
};

const applyBulkStatus = () => {
    records.value = records.value.map(record => ({
        ...record,
        status: bulkStatus.value
    }));
};

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
        title: 'Bulk Import',
        href: '/documents/bulk-import',
    },
];

const submitForm = () => {
    const formData = new FormData();
    
    formData.append('record_count', records.value.length.toString());
    
    records.value.forEach((record, index) => {
        if (record.file) {
            formData.append(`records[${index}][file]`, record.file);
        }
        formData.append(`records[${index}][subject_id]`, record.subject_id);
        formData.append(`records[${index}][document_type_id]`, record.document_type_id);
        formData.append(`records[${index}][issue_date]`, record.issue_date);
        if (record.expiry_date) {
            formData.append(`records[${index}][expiry_date]`, record.expiry_date);
        }
        formData.append(`records[${index}][status]`, record.status);
        if (record.notes) {
            formData.append(`records[${index}][notes]`, record.notes);
        }
    });

    const submitData = {
        records: records.value.map(record => ({
            subject_id: record.subject_id,
            document_type_id: record.document_type_id,
            issue_date: record.issue_date,
            expiry_date: record.expiry_date,
            status: record.status,
            notes: record.notes,
            file: record.file
        }))
    };

    router.post(route('documents.bulk-import.store'), submitData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            records.value = [];
            form.reset();
        },
    });
};
</script>