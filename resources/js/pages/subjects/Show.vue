<script setup lang="ts">
import Can from '@/components/auth/Can.vue';
import { Button } from '@/components/ui/button';
import RequestUploadModal from '@/components/documents/RequestUploadModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Document, type DocumentType, type Subject } from '@/types/models';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    subject: Subject;
    documents?: Document[];
    documentTypes?: DocumentType[];
}

const props = defineProps<Props>();

// Upload request modal state
const showUploadRequestModal = ref(false);

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
        title: props.subject?.name || 'Subject Detail',
        href: `/subjects/${props.subject?.id}`,
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
            return 'Active';
        case 2:
            return 'Pending';
        case 3:
            return 'Inactive';
        default:
            return 'Unknown';
    }
};
</script>

<template>

    <Head :title="'Subject: ' + subject.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-foreground">{{ subject.name }}</h1>
                <div class="flex space-x-3">
                    <Can permission="documents-create">
                            <Button variant="outline" size="md" @click="showUploadRequestModal = true">
                                Request Upload
                            </Button>
                    </Can>
                    <Can permission="subjects-edit">
                        <Link :href="`/subjects/${subject.id}/edit`">
                        <Button variant="outline">Edit</Button>
                        </Link>
                    </Can>
                    <Can permission="subjects-view">
                        <Link href="/subjects">
                        <Button variant="ghost">Back to Subjects</Button>
                        </Link>
                    </Can>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <!-- Subject Info Card -->
                <div class="col-span-2 rounded-xl border border-border bg-card p-6">
                    <h2 class="mb-4 text-xl font-semibold">Subject Information</h2>

                    <div class="grid grid-cols-1 gap-y-4 md:grid-cols-2">
                        <div>
                            <p class="text-sm text-muted-foreground">Name</p>
                            <p class="font-medium">{{ subject.name }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">Type</p>
                            <p class="font-medium">{{ subject.subject_type?.name || 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">Email</p>
                            <p class="font-medium">{{ subject.email || 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">Phone</p>
                            <p class="font-medium">{{ subject.phone || 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">Address</p>
                            <p class="font-medium">{{ subject.address || 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">Category</p>
                            <p class="font-medium">{{ subject.category || 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">Status</p>
                            <p class="flex items-center font-medium">
                                <span :class="[getStatusColor(subject.status), 'mr-2 h-2 w-2 rounded-full']"></span>
                                {{ getStatusText(subject.status) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">Created</p>
                            <p class="font-medium">{{ new Date(subject.created_at).toLocaleDateString() }}</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <p class="text-sm text-muted-foreground">Notes</p>
                        <p class="font-medium whitespace-pre-line">{{ subject.notes || 'No notes available' }}</p>
                    </div>
                </div>

                <!-- Associated Documents Card -->
                <div class="rounded-xl border border-border bg-card p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-xl font-semibold">Documents</h2>
                        <div class="flex gap-2">
                            <Can permission="documents-create">
                                <Link :href="`/documents/create?subject_id=${subject.id}`">
                                <Button size="sm" class="bg-primary text-primary-foreground hover:bg-primary/90"> Add
                                    Document </Button>
                                </Link>
                            </Can>
                        </div>
                    </div>

                    <div v-if="documents && documents.length > 0" class="space-y-3">
                        <div v-for="document in documents" :key="document.id"
                            class="rounded-lg border border-border p-2 hover:bg-muted/30">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-medium">{{ document.document_type?.name || 'Document' }}</p>
                                    <p class="text-sm text-muted-foreground">Issued: {{ new
                                        Date(document.issue_date).toLocaleDateString() }}</p>
                                    <p v-if="document.expiry_date" class="text-sm"
                                        :class="new Date(document.expiry_date) < new Date() ? 'text-red-500' : 'text-muted-foreground'">
                                        Expires: {{ new Date(document.expiry_date).toLocaleDateString() }}
                                    </p>
                                </div>
                                <Can permission="documents-view">
                                    <Link :href="`/documents/${document.slug}`"
                                        class="text-sm text-primary hover:underline">View</Link>
                                </Can>
                            </div>
                        </div>
                    </div>
                    <div v-else class="p-4 text-center text-muted-foreground">No documents available</div>
                </div>
            </div>
        </div>

        <RequestUploadModal :subject="subject" :document-types="props.documentTypes" :show="showUploadRequestModal"
            @close="showUploadRequestModal = false" />
    </AppLayout>
</template>
