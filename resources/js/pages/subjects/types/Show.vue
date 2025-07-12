<script setup lang="ts">
import Can from '@/components/auth/Can.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { RequiredDocumentType, type Subject, type SubjectType } from '@/types/models';
import { Head, Link, router } from '@inertiajs/vue3';
import { Select } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Checkbox } from '@/components/ui/checkbox';
import { ref, computed } from 'vue';
import ActionMenu from '@/components/ui/dropdown-menu/ActionMenu.vue';
import ActionMenuButton from '@/components/ui/dropdown-menu/ActionMenuButton.vue';
import { Eye, Edit, Trash } from 'lucide-vue-next';

interface Props {
    subjectType: SubjectType;
    subjects: Subject[];
    documentTypes: DocumentType[];
    requiredDocumentTypes: RequiredDocumentType[];
}

interface DocumentType {
    id: number;
    name: string;
    description?: string;
    is_active: boolean;
    company_id: number;
}

const props = defineProps<Props>();

const isDialogOpen = ref(false);
const selectedDocumentId = ref<number | string>('');
const isRequired = ref(true);

const requiredDocumentTypeIds = computed(() => {
    return props.requiredDocumentTypes.map(item => item.document_type_id);
});

const isDocumentLinked = (documentTypeId: number) => {
    return requiredDocumentTypeIds.value.includes(documentTypeId);
};

const saveDocumentRequirement = () => {
    if (selectedDocumentId.value && selectedDocumentId.value !== '') {
        router.post('/required-documents', {
            subject_type_id: props.subjectType.id,
            document_type_id: selectedDocumentId.value,
            is_required: isRequired.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                isDialogOpen.value = false;
                selectedDocumentId.value = '';
            }
        });
    }
};

// Handle removing a document requirement
const removeDocumentRequirement = (requiredDocId: number) => {
    router.delete(`/required-documents/${requiredDocId}`, {
        preserveScroll: true
    });
};

// Toggle a document requirement's required status
const toggleDocumentRequired = (requiredDoc: RequiredDocumentType) => {
    const newRequiredStatus = !requiredDoc.is_required;
    
    router.post('/required-documents', {
        subject_type_id: props.subjectType.id,
        document_type_id: requiredDoc.document_type_id,
        is_required: newRequiredStatus // Toggle the is_required value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // The page will automatically refresh with new data from the server
        }
    });
};

// Handle menu action selection for document types
const handleMenuAction = (action: string, documentId: string | number) => {
    const docId = Number(documentId);
    const requiredDoc = props.requiredDocumentTypes.find(doc => doc.id === docId);
    
    if (!requiredDoc && action !== 'remove') return;
    
    switch (action) {
        case 'edit':
            if (requiredDoc) {
                toggleDocumentRequired(requiredDoc);
            }
            break;
        case 'remove':
            removeDocumentRequirement(docId);
            break;
    }
};

// Handle menu action selection for subjects
const handleSubjectAction = (action: string, subjectId: string | number) => {
    const subject = props.subjects.find((s) => s.id === subjectId);
    
    if (!subject) return;
    
    switch (action) {
        case 'view':
            router.visit(`/subjects/${subject.slug}`);
            break;
    }
};

// Handle menu action selection for subject type
const handleSubjectTypeAction = (action: string) => {
    // We already have the subject type from props
    switch (action) {
        case 'edit':
            router.visit(`/subject-types/${props.subjectType.slug}/edit`);
            break;
        case 'createSubject':
            router.visit('/subjects/create');
            break;
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Subject Types',
        href: '/subject-types',
    },
    {
        title: props.subjectType.name,
        href: `/subject-types/${props.subjectType.id}`,
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

const getToggleActionText = (doc: RequiredDocumentType) => {
    return doc.is_required ? 'Mark as Optional' : 'Mark as Required';
};
</script>

<template>

    <Head :title="`Subject Type: ${subjectType.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">{{ subjectType.name }}</h1>
                    <p class="text-muted-foreground">{{ subjects.length }} subjects in this category</p>
                </div>
                <div class="flex space-x-3">
                    <Button variant="ghost" as-child>
                        <Link href="/subject-types">Back to Subject Types</Link>
                    </Button>
                    
                    <ActionMenu :item-id="subjectType.id" @select="handleSubjectTypeAction">
                        <template #trigger>
                            <Button variant="outline">Actions</Button>
                        </template>
                        <template #menu-items="{ handleAction }">
                            <Can permission="subject-types-edit">
                                <ActionMenuButton 
                                    :icon="Edit" 
                                    text="Edit" 
                                    @click="(e) => handleAction('edit', e)" 
                                />
                            </Can>
                            <Can permission="subjects-create">
                                <ActionMenuButton 
                                    :icon="Eye" 
                                    text="Create Subject" 
                                    @click="(e) => handleAction('createSubject', e)" 
                                />
                            </Can>
                        </template>
                    </ActionMenu>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-card p-6 mb-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Document Requirements</h2>
                    <Can permission="subject-types-edit">
                        <Button variant="outline" @click="isDialogOpen = true">
                            Link Document Type
                        </Button>
                    </Can>
                </div>

                <div v-if="requiredDocumentTypes.length === 0" class="p-4 text-center text-muted-foreground">
                    No document requirements defined for this subject type yet.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-muted/50 text-xs uppercase">
                            <tr>
                                <th scope="col" class="px-6 py-3">Document Type</th>
                                <th scope="col" class="px-6 py-3">Required</th>
                                <th scope="col" class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="doc in requiredDocumentTypes" :key="doc.id"
                                class="border-t border-border hover:bg-muted/30">
                                <td class="px-6 py-4 font-medium">{{ doc.document_type?.name || 'Unknown Document' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="doc.is_required" class="inline-flex items-center">
                                        <span class="mr-2 h-2 w-2 rounded-full bg-green-500"></span>
                                        Required
                                    </span>
                                    <span v-else class="inline-flex items-center">
                                        <span class="mr-2 h-2 w-2 rounded-full bg-yellow-500"></span>
                                        Optional
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <ActionMenu :item-id="doc.id" @select="handleMenuAction">
                                        <template #menu-items="{ handleAction }">
                                            <Can permission="subject-types-edit">
                                                <ActionMenuButton 
                                                    :icon="Edit" 
                                                    :text="getToggleActionText(doc)"
                                                    @click="(e) => handleAction('edit', e)" 
                                                />
                                                <ActionMenuButton 
                                                    :icon="Trash" 
                                                    text="Remove" 
                                                    variant="destructive"
                                                    @click="(e) => handleAction('remove', e)" 
                                                />
                                            </Can>
                                        </template>
                                    </ActionMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-card p-6">
                <h2 class="mb-4 text-xl font-semibold">Subjects with this Type</h2>

                <div v-if="subjects.length === 0" class="p-4 text-center text-muted-foreground">
                    No subjects with this type yet.
                    <div class="mt-4">
                        <Can permission="subjects-create">
                            <Link href="/subjects/create">
                            <Button size="sm">Create Subject</Button>
                            </Link>
                        </Can>
                    </div>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-muted/50 text-xs uppercase">
                            <tr>
                                <th scope="col" class="px-6 py-3">Name</th>
                                <th scope="col" class="px-6 py-3">Email</th>
                                <th scope="col" class="px-6 py-3">Phone</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="subject in subjects" :key="subject.id"
                                class="border-t border-border hover:bg-muted/30">
                                <td class="px-6 py-4 font-medium">{{ subject.name }}</td>
                                <td class="px-6 py-4">{{ subject.email || 'N/A' }}</td>
                                <td class="px-6 py-4">{{ subject.phone || 'N/A' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center">
                                        <span
                                            :class="[getStatusColor(subject.status), 'mr-2 h-2 w-2 rounded-full']"></span>
                                        {{ getStatusText(subject.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <ActionMenu :item-id="subject.id" @select="handleSubjectAction">
                                        <template #menu-items="{ handleAction }">
                                            <Can permission="subjects-view">
                                                <ActionMenuButton 
                                                    :icon="Eye" 
                                                    text="View" 
                                                    @click="(e) => handleAction('view', e)" 
                                                />
                                            </Can>
                                        </template>
                                    </ActionMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Link Document Type Dialog -->
            <Dialog v-model:open="isDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Link Document Type</DialogTitle>
                        <DialogDescription>
                            Select a document type and set whether it's required for this subject type.
                        </DialogDescription>
                    </DialogHeader>

                    <div class="py-4">
                        <div class="mb-4">
                            <label class="text-sm font-medium mb-2 block">Document Type</label>
                            <Select 
                                v-model="selectedDocumentId"
                                class="w-full"
                            >
                                <option value="" disabled>Select a document type</option>
                                <option v-for="doc in documentTypes" :key="doc.id" :value="doc.id"
                                    :disabled="isDocumentLinked(doc.id)">
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
                        <Button :disabled="!selectedDocumentId" @click="saveDocumentRequirement()">
                            Save
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
