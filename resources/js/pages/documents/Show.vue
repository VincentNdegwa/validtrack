<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Document } from '@/types/models';
import { Head, Link } from '@inertiajs/vue3';

interface Props {
    document: Document;
    fileUrl: string;
}

const props = defineProps<Props>();

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
        title: props.document.document_type?.name || 'Document Detail',
        href: `/documents/${props.document?.slug}`,
    },
];

const getStatusColor = (status: number) => {
    switch (status) {
        case 1:
            return 'bg-green-500';
        case 2:
            return 'bg-yellow-500';
        case 3:
            return 'bg-red-500';
        default:
            return 'bg-gray-500';
    }
};

const getStatusText = (status: number) => {
    switch (status) {
        case 1:
            return 'Valid';
        case 2:
            return 'Pending';
        case 3:
            return 'Expired';
        default:
            return 'Unknown';
    }
};

const formatDate = (dateString: string | undefined) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString();
};

const isExpired = (dateString: string | undefined) => {
    if (!dateString) return false;
    return new Date(dateString) < new Date();
};

const getFileType = (url: string) => {
    const extension = url.split('.').pop()?.toLowerCase();

    if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(extension || '')) {
        return 'image';
    }

    if (['pdf'].includes(extension || '')) {
        return 'pdf';
    }

    return 'other';
};
</script>

<template>
    <Head :title="'Document: ' + (document.document_type?.name || 'Document')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-foreground">
                    {{ document.document_type?.name || 'Document' }}
                </h1>
                <div class="flex space-x-3">
                    <Link :href="`/documents/${document.slug}/edit`">
                        <Button variant="outline">Edit</Button>
                    </Link>
                    <Link href="/documents">
                        <Button variant="ghost">Back to Documents</Button>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <!-- Document Preview -->
                <div class="rounded-xl border border-border bg-card p-6 lg:col-span-2">
                    <h2 class="mb-4 text-xl font-semibold">Document Preview</h2>

                    <div class="h-[500px] w-full overflow-hidden rounded-md border border-border">
                        <!-- PDF Preview -->
                        <template v-if="getFileType(fileUrl) === 'pdf'">
                            <iframe :src="fileUrl" class="h-full w-full" frameborder="0"></iframe>
                        </template>

                        <!-- Image Preview -->
                        <template v-else-if="getFileType(fileUrl) === 'image'">
                            <img :src="fileUrl" class="h-full w-full object-contain" alt="Document preview" />
                        </template>

                        <!-- Other files -->
                        <template v-else>
                            <div class="flex h-full w-full flex-col items-center justify-center">
                                <div class="mb-4 text-4xl">📄</div>
                                <p class="text-muted-foreground">Preview not available</p>
                                <a :href="fileUrl" target="_blank" class="mt-4 text-primary hover:underline">Download File</a>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4">
                        <a
                            :href="fileUrl"
                            target="_blank"
                            download
                            class="inline-flex items-center rounded-md bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90"
                        >
                            Download Document
                        </a>
                    </div>
                </div>

                <!-- Document Info Card -->
                <div class="rounded-xl border border-border bg-card p-6">
                    <h2 class="mb-4 text-xl font-semibold">Document Information</h2>

                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Subject</p>
                            <Link :href="`/subjects/${document.subject?.id}`" class="font-medium text-primary hover:underline">
                                {{ document.subject?.name || 'N/A' }}
                            </Link>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">Document Type</p>
                            <p class="font-medium">{{ document.document_type?.name || 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">Issue Date</p>
                            <p class="font-medium">{{ formatDate(document.issue_date) }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">Expiry Date</p>
                            <p class="font-medium" :class="{ 'text-red-500': isExpired(document.expiry_date) }">
                                {{ formatDate(document.expiry_date) }}
                                <span v-if="isExpired(document.expiry_date)" class="ml-2 text-xs">(Expired)</span>
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">Status</p>
                            <p class="flex items-center font-medium">
                                <span :class="[getStatusColor(document.status), 'mr-2 h-2 w-2 rounded-full']"></span>
                                {{ getStatusText(document.status) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">Uploaded By</p>
                            <p class="font-medium">{{ document.uploader?.name || 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">Upload Date</p>
                            <p class="font-medium">{{ formatDate(document.created_at) }}</p>
                        </div>

                        <div v-if="document.notes">
                            <p class="text-sm text-muted-foreground">Notes</p>
                            <p class="font-medium whitespace-pre-line">{{ document.notes }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
