<script setup lang="ts">
import Can from '@/components/auth/Can.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Document, type DocumentType } from '@/types/models';
import { Head, Link } from '@inertiajs/vue3';

interface Props {
    documentType: DocumentType;
    documents?: Document[];
}

const props = defineProps<Props>();

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
        title: props.documentType.name,
        href: `/document-types/${props.documentType.slug}`,
    },
];

const getStatusLabel = (status: number) => {
    switch (status) {
        case 0:
            return 'Draft';
        case 1:
            return 'Active';
        case 2:
            return 'Expired';
        case 3:
            return 'Archived';
        default:
            return 'Unknown';
    }
};

const getStatusClass = (status: number) => {
    switch (status) {
        case 0:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
        case 1:
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 2:
            return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        case 3:
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
    }
};
</script>

<template>
    <Head :title="documentType.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-foreground">{{ documentType.name }}</h1>
                <div class="flex gap-2">
                    <Can permission="document-types-edit">
                        <Link :href="`/document-types/${documentType.slug}/edit`">
                            <Button variant="outline">Edit</Button>
                        </Link>
                    </Can>
                    <Can permission="document-types-delete">
                        <Button
                            variant="destructive"
                            v-if="(documents?.length || 0) === 0"
                            @click="$inertia.delete(`/document-types/${documentType.id}`)"
                        >
                            Delete
                        </Button>
                    </Can>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-card p-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <h3 class="mb-2 text-lg font-semibold">Details</h3>
                        <div class="grid gap-2">
                            <div>
                                <span class="text-muted-foreground">Name:</span>
                                <span class="ml-2">{{ documentType.name }}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Description:</span>
                                <span class="ml-2">{{ documentType.description || 'No description' }}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Status:</span>
                                <span
                                    class="ml-2 rounded-full px-2 py-1 text-xs"
                                    :class="{
                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': documentType.is_active,
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': !documentType.is_active,
                                    }"
                                >
                                    {{ documentType.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Created:</span>
                                <span class="ml-2">{{ new Date(documentType.created_at).toLocaleString() }}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Last Updated:</span>
                                <span class="ml-2">{{ new Date(documentType.updated_at).toLocaleString() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents List -->
            <div class="rounded-xl border border-border bg-card">
                <div class="border-b border-border p-4">
                    <h2 class="text-lg font-semibold">Documents ({{ documents?.length || 0 }})</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-muted/50 text-xs uppercase">
                            <tr>
                                <th scope="col" class="px-6 py-3">Subject</th>
                                <th scope="col" class="px-6 py-3">File</th>
                                <th scope="col" class="px-6 py-3">Issue Date</th>
                                <th scope="col" class="px-6 py-3">Expiry Date</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!documents || documents.length === 0">
                                <td colspan="6" class="px-6 py-4 text-center">No documents found</td>
                            </tr>
                            <tr v-for="document in documents" :key="document.id" class="border-t border-border hover:bg-muted/30">
                                <td class="px-6 py-4 font-medium">
                                    <Link :href="`/subjects/${document.slug}`" class="text-blue-600 hover:underline">
                                        {{ document.subject?.name || 'Unknown Subject' }}
                                    </Link>
                                </td>
                                <td class="px-6 py-4">
                                    <a :href="document.file_url" target="_blank" class="text-blue-600 hover:underline"> View File </a>
                                </td>
                                <td class="px-6 py-4">{{ new Date(document.issue_date).toLocaleDateString() }}</td>
                                <td class="px-6 py-4">
                                    {{ document.expiry_date ? new Date(document.expiry_date).toLocaleDateString() : 'No expiry' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-2 py-1 text-xs" :class="getStatusClass(document.status)">
                                        {{ getStatusLabel(document.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <Can permission="document-view">
                                            <Link :href="`/documents/${document.slug}`" class="text-blue-600 hover:underline">View</Link>
                                        </Can>
                                        <Can permission="document-edit">
                                            <Link :href="`/documents/${document.slug}/edit`" class="text-amber-600 hover:underline">Edit </Link>
                                        </Can>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
