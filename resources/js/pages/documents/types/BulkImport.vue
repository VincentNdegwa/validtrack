<template>
    <Head title="Bulk Import Document Types" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-foreground">Bulk Import Document Types</h1>
                <Button variant="outline" as-child>
                    <Link :href="route('document-types.index')">Back to Document Types</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Instructions</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <ul class="list-disc list-inside space-y-2 text-sm text-muted-foreground">
                            <li>Upload a CSV file with document type data</li>
                            <li>The CSV should have the following columns: name, description</li>
                            <li>Each row represents a new document type</li>
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
                                <Input 
                                    class="mt-2"
                                    id="file"
                                    ref="fileInput"
                                    type="file"
                                    accept=".csv"
                                    @change="handleFileChange"
                                />
                                <div v-if="form.errors.file" class="mt-1 text-sm text-destructive">
                                    {{ form.errors.file }}
                                </div>
                            </div>

                            <div v-if="previewError" class="p-4 border border-destructive/50 rounded-lg bg-destructive/10">
                                <div class="flex items-center gap-2 text-sm text-destructive">
                                    <FileWarning class="h-4 w-4" />
                                    {{ previewError }}
                                </div>
                            </div>

                            <Button 
                                type="submit" 
                                :disabled="!isValidForSubmit"
                                class="bg-primary text-primary-foreground hover:bg-primary/90"
                            >
                                <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                <span v-if="form.processing">Processing...</span>
                                <span v-else>Import Document Types</span>
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
                                    <TableHead>Description</TableHead>
                                    <TableHead>Icon</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="(row, index) in previewData.slice(0, 5)" :key="index">
                                    <TableCell>{{ row.name }}</TableCell>
                                    <TableCell>{{ row.description }}</TableCell>
                                    <TableCell>
                                        <Select 
                                            v-model="row.is_active" 
                                            :disabled="form.processing"
                                            @change="form.records = previewData"
                                        >
                                            <option :value="true">Active</option>
                                            <option :value="false">Inactive</option>
                                        </Select>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                        <div v-if="previewData.length > 5" class="p-4 text-sm text-muted-foreground text-center border-t">
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
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Loader2, ArrowDownToLine, FileWarning } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const fileInput = ref<HTMLInputElement | null>(null);
const importResults = ref<{
    success: number;
    failed: number;
    errors?: string[];
} | null>(null);

interface PreviewRecord {
    [key: string]: string | boolean | undefined;
    name: string;
    description: string | undefined;
    icon: string | undefined;
    is_active: boolean;
}

const previewData = ref<PreviewRecord[]>([]);
const showPreview = ref(false);
const previewError = ref<string | null>(null);

const form = useForm({
    file: null as File | null,
    records: [] as PreviewRecord[]
});

const isValidForSubmit = computed(() => {
    if (!showPreview.value || form.processing || !previewData.value.length) return false;
    
    // Check if all records have required fields
    return previewData.value.every(record => record.name && record.is_active !== undefined);
});

const resetPreview = () => {
    previewData.value = [];
    showPreview.value = false;
    previewError.value = null;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Document Types',
        href: '/document-types',
    },
    {
        title: 'Bulk Import',
        href: '/document-types/bulk-import',
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
            const headers = lines[0].split(',').map(h => h.trim());
            
            // Validate required columns
            const requiredColumns = ['name'];
            const missingColumns = requiredColumns.filter(col => !headers.includes(col));
            
            if (missingColumns.length > 0) {
                previewError.value = `Missing required columns: ${missingColumns.join(', ')}`;
                return;
            }

            const data: PreviewRecord[] = lines
                .slice(1)
                .filter(line => line.trim())
                .map(line => {
                    const values = line.split(',').map(v => v.trim());
                    return {
                        name: values[headers.indexOf('name')],
                        description: values[headers.indexOf('description')] || undefined,
                        icon: values[headers.indexOf('icon')] || undefined,
                        is_active: true
                    };
                });

            if (data.length > 100) {
                previewError.value = 'Maximum 100 records allowed';
                return;
            }

            previewData.value = data;
            form.records = data;
            showPreview.value = true;
        } catch (error) {
            previewError.value = 'Error reading file. Please make sure it is a valid CSV file.';
        }
    }
};

const downloadTemplate = () => {
    const link = document.createElement('a');
    link.href = '/bulk-uploads/document-types-template.csv';
    link.download = 'document-types-template.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const submitForm = () => {
    form.post(route('document-types.bulk-import.store'), {
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