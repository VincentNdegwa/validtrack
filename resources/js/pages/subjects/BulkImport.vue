<template>
    <Head title="Bulk Import Subjects" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-foreground">Bulk Import Subjects</h1>
                <Button variant="outline" as-child>
                    <Link :href="route('subjects.index')">Back to Subjects</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Instructions</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <ul class="list-inside list-disc space-y-2 text-sm text-muted-foreground">
                            <li>Upload a CSV file with subject data</li>
                            <li>The CSV should have the following columns: name, email, phone, address</li>
                            <li>Each row represents a new subject</li>
                            <li>Maximum 100 records per import</li>
                            <li>File size limit: 2MB</li>
                        </ul>
                        <div class="flex items-center gap-4">
                            <Button variant="outline" size="sm" @click="downloadTemplate">
                                <ArrowDownToLine class="mr-2 h-4 w-4" />
                                Download Template
                            </Button>
                            <span class="text-xs text-muted-foreground">Use this template as a starting point</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Upload File</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <div class="grid gap-4">
                            <div>
                                <Label for="file">CSV File</Label>
                                <Input class="mt-2" id="file" ref="fileInput" type="file" accept=".csv" @change="handleFileChange" />
                                <div v-if="form.errors.file" class="mt-1 text-sm text-destructive">
                                    {{ form.errors.file }}
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="bulk_subject_type">Bulk Subject Type</Label>
                                    <Select id="bulk_subject_type" v-model="bulkSubjectType" :disabled="!showPreview" @change="applyBulkSubjectType">
                                        <option value="">Select a type</option>
                                        <option v-for="type in subjectTypes" :key="type.id" :value="type.id">
                                            {{ type.name }}
                                        </option>
                                    </Select>
                                </div>

                                <div class="space-y-2">
                                    <Label for="bulk_status">Bulk Status</Label>
                                    <Select id="bulk_status" v-model="bulkStatus" :disabled="!showPreview" @change="applyBulkStatus">
                                        <option value="">Select a status</option>
                                        <option value="1">Active</option>
                                        <option value="3">Inactive</option>
                                    </Select>
                                </div>
                            </div>

                            <div v-if="previewError" class="rounded-lg border border-destructive/50 bg-destructive/10 p-4">
                                <div class="flex items-center gap-2 text-sm text-destructive">
                                    <FileWarning class="h-4 w-4" />
                                    {{ previewError }}
                                </div>
                            </div>

                            <Button type="submit" :disabled="!isValidForSubmit" class="bg-primary text-primary-foreground hover:bg-primary/90">
                                <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                <span v-if="form.processing">Processing...</span>
                                <span v-else>Import Subjects</span>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <Card v-if="showPreview">
                <CardHeader>
                    <CardTitle>Preview Data ({{ previewData.length }} records)</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Phone</TableHead>
                                    <TableHead>Address</TableHead>
                                    <TableHead>Subject Type</TableHead>
                                    <TableHead>Status</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="(row, index) in previewData.slice(0, 5)" :key="index">
                                    <TableCell>{{ row.name }}</TableCell>
                                    <TableCell>{{ row.email }}</TableCell>
                                    <TableCell>{{ row.phone }}</TableCell>
                                    <TableCell>{{ row.address }}</TableCell>
                                    <TableCell>
                                        <Select v-model="row.subject_type_id" :disabled="form.processing" @change="form.records = previewData">
                                            <option value="">Select a type</option>
                                            <option v-for="type in subjectTypes" :key="type.id" :value="type.id">
                                                {{ type.name }}
                                            </option>
                                        </Select>
                                    </TableCell>
                                    <TableCell>
                                        <Select v-model="row.status" :disabled="form.processing" @change="form.records = previewData">
                                            <option value="">Select a status</option>
                                            <option value="1">Active</option>
                                            <option value="3">Inactive</option>
                                        </Select>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                        <div v-if="previewData.length > 5" class="border-t p-4 text-center text-sm text-muted-foreground">
                            ... and {{ previewData.length - 5 }} more records
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card v-if="importResults">
                <CardHeader>
                    <CardTitle>Import Results</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div class="grid gap-2">
                            <div class="text-sm text-green-600 dark:text-green-400">Successfully imported: {{ importResults.success }}</div>
                            <div v-if="importResults.failed > 0" class="text-sm text-destructive">Failed to import: {{ importResults.failed }}</div>
                        </div>

                        <div v-if="importResults.errors?.length" class="mt-4">
                            <h4 class="mb-2 font-medium">Errors:</h4>
                            <ul class="list-inside list-disc space-y-1 text-sm text-destructive">
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
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowDownToLine, FileWarning, Loader2 } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

const fileInput = ref<HTMLInputElement | null>(null);
const importResults = ref<{
    success: number;
    failed: number;
    errors?: string[];
} | null>(null);

interface PreviewRecord {
    [key: string]: string | number | undefined;
    name: string;
    email: string;
    phone: string;
    address: string;
    subject_type_id: string | undefined;
    status: string | undefined;
}

const previewData = ref<PreviewRecord[]>([]);
const showPreview = ref(false);
const previewError = ref<string | null>(null);

const form = useForm({
    file: null as File | null,
    records: [] as PreviewRecord[],
});

const bulkSubjectType = ref('');
const bulkStatus = ref('');

const applyBulkSubjectType = () => {
    if (bulkSubjectType.value) {
        previewData.value = previewData.value.map((record) => ({
            ...record,
            subject_type_id: bulkSubjectType.value,
        }));
        form.records = previewData.value;
    }
};

const applyBulkStatus = () => {
    if (bulkStatus.value) {
        previewData.value = previewData.value.map((record) => ({
            ...record,
            status: bulkStatus.value,
        }));
        form.records = previewData.value;
    }
};

const resetPreview = () => {
    previewData.value = [];
    showPreview.value = false;
    previewError.value = null;
};

defineProps<{
    subjectTypes: Array<{
        id: number;
        name: string;
    }>;
}>();



const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Subjects',
        href: '/subjects',
    },
    {
        title: 'Bulk Import',
        href: '/subjects/bulk-import',
    },
];

const handleFileChange = async (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files?.length) {
        form.file = target.files[0];
        resetPreview();

        try {
            const text = await target.files[0].text();
            const lines = text.split('\n');
            const headers = lines[0].split(',').map((h) => h.trim());

            // Validate required columns
            const requiredColumns = ['name', 'email', 'phone', 'address'];
            const missingColumns = requiredColumns.filter((col) => !headers.includes(col));

            if (missingColumns.length > 0) {
                previewError.value = `Missing required columns: ${missingColumns.join(', ')}`;
                return;
            }

            const data: PreviewRecord[] = lines
                .slice(1)
                .filter((line) => line.trim())
                .map((line) => {
                    const values = line.split(',').map((v) => v.trim());
                    return {
                        name: values[headers.indexOf('name')],
                        email: values[headers.indexOf('email')],
                        phone: values[headers.indexOf('phone')],
                        address: values[headers.indexOf('address')],
                        subject_type_id: undefined,
                        status: undefined,
                    };
                });

            if (data.length > 100) {
                previewError.value = 'Maximum 100 records allowed';
                return;
            }

            previewData.value = data;
            showPreview.value = true;
        } catch (error) {
            console.log(error);

            previewError.value = 'Error reading file. Please make sure it is a valid CSV file.';
        }
    }
};

const downloadTemplate = () => {
    const link = document.createElement('a');
    link.href = '/bulk-uploads/subjects-template.csv';
    link.download = 'subjects-template.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const isValidForSubmit = computed(() => {
    if (!showPreview.value || form.processing || !previewData.value.length) return false;

    return previewData.value.every((record) => record.subject_type_id && record.status !== undefined && record.name);
});

const submitForm = () => {
    form.post(route('subjects.bulk-import.store'), {
        preserveScroll: true,
        onSuccess: (response) => {
            importResults.value = response?.props?.flash?.import_results;
            form.reset();
            if (fileInput.value) {
                fileInput.value.value = '';
            }
            resetPreview();
        },
    });
};
</script>
