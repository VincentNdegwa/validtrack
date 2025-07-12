<script setup lang="ts">
import Can from '@/components/auth/Can.vue';
import { Button } from '@/components/ui/button';
import RequestUploadModal from '@/components/documents/RequestUploadModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Document, SubjectType, type DocumentType } from '@/types/models';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface CustomSubject {
    id: number;
    name: string;
    company_id: number;
    subject_type_id?: number;
    user_id: number;
    email?: string;
    phone?: string;
    address?: string;
    category?: string;
    notes?: string;
    status: number;
    created_at: string;
    updated_at: string;
    slug: string;
    compliance_status: boolean;
    missing_documents: Array<{id: number, name: string}>;
    subject_type?: SubjectType;
}

// Update the Props interface to use our custom Subject type
interface Props {
    subject: CustomSubject;
    documents?: Document[];
    documentTypes?: DocumentType[];
    requiredDocumentTypes?: any[];
}

interface Props {
    subject: CustomSubject;
    documents?: Document[];
    documentTypes?: DocumentType[];
    requiredDocumentTypes?: any[];
}

const props = defineProps<Props>();

const showUploadRequestModal = ref(false);
const selectedDocTypesForRequest = ref<Array<any>>([]);

const missingDocuments = computed(() => {
    if (!props.subject?.missing_documents || props.subject.missing_documents.length === 0) {
        return [];
    }

    const missingDocs = [];
    
    if (props.requiredDocumentTypes && props.requiredDocumentTypes.length > 0) {
        const missingDocTypeIds = props.subject.missing_documents.map(doc => doc.id);
        
        for (const requiredDoc of props.requiredDocumentTypes) {
            if (missingDocTypeIds.includes(requiredDoc.document_type_id)) {
                missingDocs.push(requiredDoc);
            }
        }
    }
    
    return missingDocs;
});

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

const isDocumentExpired = (document: Document) => {
    if (!document.expiry_date) return false;
    return new Date(document.expiry_date) < new Date();
};

const compliance = computed(() => {
    if (!props.requiredDocumentTypes || props.requiredDocumentTypes.length === 0) {
        return { status: 'Compliant', color: 'bg-green-500', percentage: 100 };
    }
    const isCompliant = props.subject?.compliance_status;
    
    const totalRequired = props.requiredDocumentTypes.length;
    const missingCount = props.subject?.missing_documents?.length || 0;
    const compliantCount = totalRequired - missingCount;
    const percentage = totalRequired > 0 ? Math.round((compliantCount / totalRequired) * 100) : 0;
    
    let status = 'Non-compliant';
    let color = 'bg-red-500';
    
    if (isCompliant) {
        status = 'Compliant';
        color = 'bg-green-500';
    } else if (percentage >= 50) {
        status = 'Partially Compliant';
        color = 'bg-yellow-500';
    }
    
    return { status, color, percentage };
});

const requestAllMissingDocumentsUpload = () => {
    selectedDocTypesForRequest.value = missingDocuments.value;
    showUploadRequestModal.value = true;
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
                        <Link :href="`/subjects/${subject.slug}/edit`">
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

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

                <div
                    class="flex flex-col col-span-2 rounded-xl border border-border bg-card p-6 h-[570px] overflow-scroll no-scrollbar ">
                    <h2 class="mb-4 text-xl font-semibold">Subject Information</h2>

                    <!-- Compliance Status Banner -->
                    <div class="mb-6 flex flex-col rounded-lg border p-4" :class="[
                        compliance.percentage === 100 ? 'bg-green-50 border-green-200 dark:bg-green-900/10 dark:border-green-900/20' :
                        compliance.percentage >= 50 ? 'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/10 dark:border-yellow-900/20' :
                        'bg-red-50 border-red-200 dark:bg-red-900/10 dark:border-red-900/20'
                    ]">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span :class="[compliance.color, 'mr-2 h-3 w-3 rounded-full']"></span>
                                <span class="font-semibold" :class="[
                                    compliance.percentage === 100 ? 'text-green-700 dark:text-green-400' :
                                    compliance.percentage >= 50 ? 'text-yellow-700 dark:text-yellow-400' :
                                    'text-red-700 dark:text-red-400'
                                ]">{{ compliance.status }}</span>
                            </div>
                            <span class="text-sm font-medium">{{ compliance.percentage }}% complete</span>
                        </div>

                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                            <div :style="`width: ${compliance.percentage}%`" :class="[
                                compliance.percentage === 100 ? 'bg-green-500' :
                                compliance.percentage >= 50 ? 'bg-yellow-500' :
                                'bg-red-500'
                            ]" class="h-2 rounded-full"></div>
                        </div>

                        <div v-if="missingDocuments.length > 0" class="mt-3">
                            <p class="text-sm font-medium text-muted-foreground">Missing {{ missingDocuments.length
                                }}
                                required document(s)</p>
                            <Can permission="documents-create">
                                <Button variant="outline" size="sm" class="mt-2"
                                    @click="requestAllMissingDocumentsUpload">
                                    Request Missing Documents
                                </Button>
                            </Can>
                        </div>
                    </div>

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

                <div class="flex flex-col gap-4 h-[570px] overflow-scroll no-scrollbar ">
                    <!-- Compliance Card -->
                    <div class="rounded-xl h-full border border-border bg-card p-6"
                        v-if="requiredDocumentTypes && requiredDocumentTypes.length > 0">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-xl font-semibold">Required Documents</h2>
                        </div>

                        <div class="space-y-3">
                            <div v-for="requiredDoc in requiredDocumentTypes" :key="requiredDoc.id"
                                class="rounded-lg border p-2" :class="[
                                    documents && documents.some(doc => doc.document_type_id === requiredDoc.document_type_id) 
                                    ? 'border-green-200 bg-green-50 dark:bg-green-900/10 dark:border-green-900/20' 
                                    : 'border-red-200 bg-red-50 dark:bg-red-900/10 dark:border-red-900/20'
                                ]">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="flex items-center font-medium">
                                            <span class="mr-2 h-2 w-2 rounded-full" :class="[
                                                    documents && documents.some(doc => doc.document_type_id === requiredDoc.document_type_id)
                                                    ? 'bg-green-500'
                                                    : 'bg-red-500'
                                                ]">
                                            </span>
                                            {{ requiredDoc.document_type.name }}
                                        </p>
                                        <p class="text-xs text-muted-foreground mt-1">
                                            {{ documents && documents.some(doc => doc.document_type_id ===
                                            requiredDoc.document_type_id)
                                            ? 'Submitted'
                                            : 'Missing' }}
                                        </p>
                                    </div>

                                    <div
                                        v-if="!documents || !documents.some(doc => doc.document_type_id === requiredDoc.document_type_id)">
                                        <Can permission="documents-create">
                                            <Button size="sm" variant="ghost" class="text-xs" @click="() => {
                                                selectedDocTypesForRequest = [requiredDoc]; 
                                                showUploadRequestModal = true;
                                            }">
                                                Request
                                            </Button>
                                        </Can>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Associated Documents Card -->
            <div class="rounded-xl border border-border bg-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Documents</h2>
                    <div class="flex gap-2">
                        <Can permission="documents-create">
                            <Link :href="`/documents/create?subject_id=${subject.id}`">
                            <Button size="sm" class="bg-primary text-primary-foreground hover:bg-primary/90">
                                Add
                            </Button>
                            </Link>
                        </Can>
                    </div>
                </div>

                <div v-if="documents && documents.length > 0" class="space-y-3">
                    <div v-for="document in documents" :key="document.id"
                        class="rounded-lg border border-border p-3 hover:bg-muted/30"
                        :class="{ 'border-red-200': isDocumentExpired(document) }">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-medium">{{ document.document_type?.name || 'Document' }}</p>
                                <p class="text-sm text-muted-foreground">Issued: {{ new
                                    Date(document.issue_date).toLocaleDateString() }}</p>
                                <p v-if="document.expiry_date" class="text-sm"
                                    :class="isDocumentExpired(document) ? 'text-red-500' : 'text-muted-foreground'">
                                    Expires: {{ new Date(document.expiry_date).toLocaleDateString() }}
                                    <span v-if="isDocumentExpired(document)"
                                        class="ml-1 text-xs font-medium px-2 py-0.5 rounded-full bg-red-100 text-red-700">
                                        Expired
                                    </span>
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

        <RequestUploadModal :subject="subject" :document-types="props.documentTypes" :show="showUploadRequestModal"
            :pre-selected-document-types="selectedDocTypesForRequest" @close="showUploadRequestModal = false" />
    </AppLayout>
</template>
