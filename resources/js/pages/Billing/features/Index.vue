<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { ActionMenu, ActionMenuButton } from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type BillingFeature } from '@/types/models';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Plus, Trash } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import Can from '@/components/auth/Can.vue';

interface Props {
    features: BillingFeature[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Billing Features',
        href: '/billing/features',
    }
];

const search = ref('');

// Define columns for the DataTable
const columns = computed(() => [
    {
        key: 'name',
        label: 'Name',
        class: 'font-medium',
        sortable: true,
    },
    {
        key: 'key',
        label: 'Key',
        sortable: true,
    },
    {
        key: 'type',
        label: 'Type',
        sortable: true,
    },
    {
        key: 'description',
        label: 'Description',
        sortable: false,
    },
    { key: '_actions', label: 'Actions' },
]);

const showDeleteModal = ref(false);
const featureToDelete = ref<BillingFeature | null>(null);

const confirmDelete = (feature: BillingFeature) => {
    featureToDelete.value = feature;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
};

const deleteFeature = () => {
    if (!featureToDelete.value) return;
    
    router.delete(`/billing/features/${featureToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteModal();
        },
    });
};

const handleMenuAction = (action: string, featureId: string | number) => {
    const feature = props.features.find((f) => f.id === Number(featureId));

    if (!feature) return;

    switch (action) {
        case 'edit':
            router.visit(`/billing/features/${feature.id}/edit`);
            break;
        case 'delete':
            confirmDelete(feature);
            break;
    }
};
</script>

<template>
    <Head title="Billing Features" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Billing Features</h1>
                    <p class="text-muted-foreground">Manage features for your billing plans</p>
                </div>
                <div class="flex gap-2">
                    <div class="relative mr-2">
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search features..."
                            class="rounded-md border px-4 py-2 focus:ring-2 focus:ring-primary focus:outline-none"
                        />
                    </div>

                    <Link href="/billing/plans">
                        <Button variant="outline" class="mr-2">Plans</Button>
                    </Link>

                    <Can permission="billing_features_create">
                        <Link href="/billing/features/create">
                            <Button class="bg-primary text-primary-foreground hover:bg-primary/90">
                                <Plus class="mr-1 h-4 w-4" />
                                Add Feature
                            </Button>
                        </Link>
                    </Can>
                </div>
            </div>

            <div v-if="!props.features || props.features.length === 0" 
                class="flex items-center justify-center p-8 rounded-lg border">
                <div class="text-center">
                    <p class="text-muted-foreground">No billing features found. Create your first feature to get started.</p>
                    <Link href="/billing/features/create" class="inline-block mt-4">
                        <Button class="bg-primary text-primary-foreground">
                            <Plus class="mr-1 h-4 w-4" />
                            Create Feature
                        </Button>
                    </Link>
                </div>
            </div>

            <div v-else class="overflow-hidden rounded-lg border">
                <DataTable
                    :data="props.features"
                    :columns="columns"
                    empty-message="No billing features found"
                >
                    <template #type="{ item: feature }">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                            :class="{
                                'bg-blue-100 text-blue-800': feature.type === 'boolean',
                                'bg-green-100 text-green-800': feature.type === 'number',
                                'bg-purple-100 text-purple-800': feature.type === 'string'
                            }">
                            {{ feature.type }}
                        </span>
                    </template>

                    <template #actions="{ item: feature }">
                        <ActionMenu :item-id="feature.id" @select="handleMenuAction">
                            <template #menu-items="{ handleAction }">
                                <ActionMenuButton :icon="Edit" text="Edit" @click="(e) => handleAction('edit', e)" />
                                <ActionMenuButton :icon="Trash" text="Delete" variant="destructive" @click="(e) => handleAction('delete', e)" />
                            </template>
                        </ActionMenu>
                    </template>
                </DataTable>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Dialog :open="showDeleteModal" @update:open="(val) => !val && closeDeleteModal()">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Feature</DialogTitle>
                </DialogHeader>
                <div class="py-4">
                    <p>Are you sure you want to delete the feature <span class="font-semibold">{{ featureToDelete?.name }}</span>?</p>
                    <p class="mt-2 text-sm text-destructive">This action cannot be undone.</p>
                </div>
                <DialogFooter>
                    <Button @click="closeDeleteModal" variant="outline">Cancel</Button>
                    <Button @click="deleteFeature" variant="destructive">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
