<script setup lang="ts">
import Can from '@/components/auth/Can.vue';
import RequestUploadModal from '@/components/documents/RequestUploadModal.vue';
import { Button } from '@/components/ui/button';
import Checkbox from '@/components/ui/checkbox/Checkbox.vue';
import { DataTable } from '@/components/ui/data-table';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { ActionMenu, ActionMenuButton } from '@/components/ui/dropdown-menu';
import { Select } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Document, SubjectType, type DocumentType } from '@/types/models';
import { Head, Link, router } from '@inertiajs/vue3';
import { Download, Edit, Eye, Trash } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';

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
    missing_documents: Array<{ id: number; name: string }>;
    subject_type?: SubjectType;
}

interface DocumentData {
    data: Document[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    subject: CustomSubject;
    documents?: DocumentData;
    documentTypes?: DocumentType[];
    requiredDocumentTypes?: any[];
}

interface Props {
    subject: CustomSubject;
    documents?: DocumentData;
    documentTypes?: DocumentType[];
    requiredDocumentTypes?: any[];
}

const props = defineProps<Props>();
// docs table state
const search = ref('');
const sortField = ref('created_at');
const sortDirection = ref<'asc' | 'desc'>('desc');
const perPage = ref(10);

const pagination = computed(() => {
    if (!props.documents) return null;
    // handle two shapes: direct array (no pagination) or paginated object
    if (Array.isArray(props.documents)) return null;
    return {
        currentPage: props.documents.current_page,
        lastPage: props.documents.last_page,
        perPage: props.documents.per_page,
        total: props.documents.total,
    };
});

const columns = computed(() => [
    { key: 'document_type', label: 'Type', sortable: false },
    { key: 'expiry_date', label: 'Expiry Date', sortable: true },
    { key: 'issue_date', label: 'Issued', sortable: true },
    { key: 'created_at', label: 'Date Added', sortable: true, sortDirection: sortField.value === 'created_at' ? sortDirection.value : null },
    { key: 'status', label: 'Status', sortable: false },
    { key: '_actions', label: 'Actions' },
]);


const handlePageChange = (page: number) => {
    router.get(
        `/subjects/${props.subject.slug}`,
        {
            page: page,
            sort: sortField.value,
            direction: sortDirection.value,
            search: search.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['documents'],
        },
    );
};

const handleSort = (field: string) => {
    if (field === sortField.value) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }

    router.get(
        `/subjects/${props.subject.id}`,
        {
            sort: sortField.value,
            direction: sortDirection.value,
            search: search.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['documents'],
        },
    );
};


const handlePerPageChange = (value: number) => {
    perPage.value = value;
    router.get(
        `/subjects/${props.subject.id}`,
        {
            search: search.value,
            sort: sortField.value,
            direction: sortDirection.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['documents'],
        },
    );
};

const handleMenuAction = (action: string, documentId: string | number) => {
    const document =
        props.documents && !Array.isArray(props.documents)
            ? props.documents.data.find((d) => d.id === documentId)
            : Array.isArray(props.documents)
              ? props.documents.find((d: Document) => d.id === documentId)
              : undefined;
    if (!document) return;
    switch (action) {
        case 'view':
            router.visit(`/documents/${document.slug}`);
            break;
        case 'edit':
            router.visit(`/documents/${document.slug}/edit`);
            break;
        case 'download':
            window.open(route('documents.download', document.id), '_blank');
            break;
        case 'delete':
            router.delete(`/documents/${document.slug}`);
            break;
    }
};

const isDialogOpen = ref(false);
const selectedDocumentId = ref<number | string>('');
const isRequired = ref(true);

const showUploadRequestModal = ref(false);
const selectedDocTypesForRequest = ref<Array<any>>([]);

const missingDocuments = computed(() => {
    if (!props.subject?.missing_documents || props.subject.missing_documents.length === 0) {
        return [];
    }

    const missingDocs = [];

    if (props.requiredDocumentTypes && props.requiredDocumentTypes.length > 0) {
        const missingDocTypeIds = props.subject.missing_documents.map((doc) => doc.id);

        for (const requiredDoc of props.requiredDocumentTypes) {
            if (missingDocTypeIds.includes(requiredDoc.document_type_id)) {
                missingDocs.push(requiredDoc);
            }
        }
    }

    return missingDocs;
});

const formatDate = (dateString: string | undefined) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString).toLocaleDateString();  
    console.log(date);
    return date;
};

const saveDocumentRequirement = () => {
    if (selectedDocumentId.value && selectedDocumentId.value !== '') {
        router.post(
            '/required-documents',
            {
                subject_type_id: props.subject.subject_type_id,
                document_type_id: selectedDocumentId.value,
                is_required: isRequired.value,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    isDialogOpen.value = false;
                    selectedDocumentId.value = '';
                },
            },
        );
    }
};
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
    return document.status === 3;
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
const requiredDocumentTypeIds = computed(() => {
    return (props.requiredDocumentTypes ?? []).map((item) => item.document_type_id);
});
const isDocumentLinked = (documentTypeId: number) => {
    return requiredDocumentTypeIds.value.includes(documentTypeId);
};

const getDocumentStatusClasses = (status: string) => {
    switch (status) {
        case 'Submitted':
            return {
                containerClass: 'border-green-200 bg-green-50 dark:border-green-900/20 dark:bg-green-900/10',
                dotClass: 'bg-green-500'
            };
        case 'Pending':
            return {
                containerClass: 'border-yellow-200 bg-yellow-50 dark:border-yellow-900/20 dark:bg-yellow-900/10',
                dotClass: 'bg-yellow-500'
            };
        case 'Expired':
            return {
                containerClass: 'border-red-200 bg-red-50 dark:border-red-900/20 dark:bg-red-900/10',
                dotClass: 'bg-red-500'
            };
        case 'Missing':
        default:
            return {
                containerClass: 'border-red-200 bg-red-50 dark:border-red-900/20 dark:bg-red-900/10',
                dotClass: 'bg-red-500'
            };
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
                        <Button variant="outline" size="md" @click="showUploadRequestModal = true"> Request Upload </Button>
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
                <div class="no-scrollbar col-span-2 flex h-[570px] flex-col overflow-scroll rounded-xl border border-border bg-card p-6">
                    <h2 class="mb-4 text-xl font-semibold">Subject Information</h2>

                    <!-- Compliance Status Banner -->
                    <div
                        class="mb-6 flex flex-col rounded-lg border p-4"
                        :class="[
                            compliance.percentage === 100
                                ? 'border-green-200 bg-green-50 dark:border-green-900/20 dark:bg-green-900/10'
                                : compliance.percentage >= 50
                                  ? 'border-yellow-200 bg-yellow-50 dark:border-yellow-900/20 dark:bg-yellow-900/10'
                                  : 'border-red-200 bg-red-50 dark:border-red-900/20 dark:bg-red-900/10',
                        ]"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span :class="[compliance.color, 'mr-2 h-3 w-3 rounded-full']"></span>
                                <span
                                    class="font-semibold"
                                    :class="[
                                        compliance.percentage === 100
                                            ? 'text-green-700 dark:text-green-400'
                                            : compliance.percentage >= 50
                                              ? 'text-yellow-700 dark:text-yellow-400'
                                              : 'text-red-700 dark:text-red-400',
                                    ]"
                                    >{{ compliance.status }}</span
                                >
                            </div>
                            <span class="text-sm font-medium">{{ compliance.percentage }}% complete</span>
                        </div>

                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                            <div
                                :style="`width: ${compliance.percentage}%`"
                                :class="[
                                    compliance.percentage === 100 ? 'bg-green-500' : compliance.percentage >= 50 ? 'bg-yellow-500' : 'bg-red-500',
                                ]"
                                class="h-2 rounded-full"
                            ></div>
                        </div>

                        <div v-if="missingDocuments.length > 0" class="mt-3">
                            <p class="text-sm font-medium text-muted-foreground">Missing {{ missingDocuments.length }} required document(s)</p>
                            <Can permission="documents-create">
                                <Button variant="outline" size="sm" class="mt-2" @click="requestAllMissingDocumentsUpload">
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

                <div class="no-scrollbar flex h-[570px] flex-col gap-4 overflow-scroll">
                    <!-- Compliance Card -->
                    <div class="h-full rounded-xl border border-border bg-card p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-xl font-semibold">Required Documents</h2>
                            <Can permission="subject-types-edit">
                                <Button size="sm" class="bg-primary text-primary-foreground hover:bg-primary/90" @click="isDialogOpen = true">
                                    Add
                                </Button>
                            </Can>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="requiredDoc in requiredDocumentTypes"
                                :key="requiredDoc.id"
                                class="rounded-lg border p-2"
                                :class="[
                                    getDocumentStatusClasses(requiredDoc.computed_status).containerClass
                                ]"
                            >
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="flex items-center font-medium">
                                            <span
                                                class="mr-2 h-2 w-2 rounded-full"
                                                :class="[getDocumentStatusClasses(requiredDoc.computed_status).dotClass]"
                                            >
                                            </span>
                                            {{ requiredDoc.document_type.name }}
                                        </p>
                                        <p class="mt-1 text-xs text-muted-foreground">
                                            {{ requiredDoc.computed_status }}
                                        </p>
                                    </div>

                                    <div v-if="requiredDoc.computed_status === 'Missing'">
                                        <Can permission="documents-create">
                                            <Button
                                                size="sm"
                                                variant="ghost"
                                                class="text-xs"
                                                @click="
                                                    () => {
                                                        selectedDocTypesForRequest = [requiredDoc];
                                                        showUploadRequestModal = true;
                                                    }
                                                "
                                            >
                                                Request
                                            </Button>
                                        </Can>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col" v-if="!requiredDocumentTypes || requiredDocumentTypes.length == 0">
                                <p class="mb-10 w-full text-sm text-muted-foreground">No required documents for this subject type.</p>
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
                                <Button size="sm" class="bg-primary text-primary-foreground hover:bg-primary/90"> Add </Button>
                            </Link>
                        </Can>
                    </div>
                </div>

                <DataTable
                    :data="props.documents?.data || []"
                    :columns="columns"
                    :pagination="pagination || undefined"
                    :show-pagination="!!pagination"
                    empty-message="No documents found"
                    @page-change="handlePageChange"
                    @sort="handleSort"
                    @per-page-change="handlePerPageChange"
                >
                    <template #subject="{ item: document }">
                        <div>{{ document.subject?.name || 'N/A' }}</div>
                    </template>

                    <template #document_type="{ item: document }">
                        <div>{{ document.document_type?.name || 'N/A' }}</div>
                    </template>

                    <template #expiry_date="{ item: document }">
                        <div>
                            {{ document.expiry_date ? formatDate(document.expiry_date) : 'N/A' }}
                        </div>
                    </template>

                    <template #issue_date="{item:document}" >
                        <div>
                            {{ document.issue_date? formatDate(document.issue_date): 'N/A' }}
                        </div>
                    </template>

                    <template #created_at="{ item: document }">
                        <div>{{ formatDate(document.created_at) }}</div>
                    </template>

                    <template #status="{ item: document }">
                        <span class="inline-flex items-center">
                            <span :class="[getStatusColor(document.status), 'mr-2 h-2 w-2 rounded-full']"></span>
                            {{ getStatusText(document.status) }}
                        </span>
                    </template>

                    <template #actions="{ item: document }">
                        <ActionMenu :item-id="document.id" @select="handleMenuAction">
                            <template #menu-items="{ handleAction }">
                                <Can permission="documents-view">
                                    <ActionMenuButton :icon="Eye" text="View" @click="(e) => handleAction('view', e)" />
                                </Can>
                                <Can permission="documents-edit">
                                    <ActionMenuButton :icon="Edit" text="Edit" @click="(e) => handleAction('edit', e)" />
                                </Can>
                                <Can permission="documents-view">
                                    <ActionMenuButton :icon="Download" text="Download" @click="(e) => handleAction('download', e)" />
                                </Can>
                                <Can permission="documents-delete">
                                    <ActionMenuButton :icon="Trash" text="Delete" variant="destructive" @click="(e) => handleAction('delete', e)" />
                                </Can>
                            </template>
                        </ActionMenu>
                    </template>
                </DataTable>
            </div>
        </div>

        <RequestUploadModal
            :subject="subject"
            :document-types="props.documentTypes"
            :show="showUploadRequestModal"
            :pre-selected-document-types="selectedDocTypesForRequest"
            @close="showUploadRequestModal = false"
        />
        <Dialog v-model:open="isDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Link Document Type</DialogTitle>
                    <DialogDescription> Select a document type and set whether it's required for this subject type. </DialogDescription>
                </DialogHeader>

                <div class="py-4">
                    <div class="mb-4">
                        <label class="mb-2 block text-sm font-medium">Document Type</label>
                        <Select v-model="selectedDocumentId" class="w-full">
                            <option value="" disabled>Select a document type</option>
                            <option v-for="doc in documentTypes" :key="doc.id" :value="doc.id" :disabled="isDocumentLinked(doc.id)">
                                {{ doc.name }} {{ isDocumentLinked(doc.id) ? '(Already linked)' : '' }}
                            </option>
                        </Select>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Checkbox id="isRequired" v-model="isRequired" />
                        <label for="isRequired" class="text-sm font-medium">Mark as required for compliance</label>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="isDialogOpen = false">Cancel</Button>
                    <Button :disabled="!selectedDocumentId" @click="saveDocumentRequirement()"> Save </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
