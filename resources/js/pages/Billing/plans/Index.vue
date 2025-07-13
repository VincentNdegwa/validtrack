<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { ActionMenu, ActionMenuButton } from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type BillingPlan } from '@/types/models';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Plus, Trash } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    plans: BillingPlan[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Billing Plans',
        href: '/billing/plans',
    },
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
        key: 'monthly_price',
        label: 'Monthly Price',
        sortable: true,
    },
    {
        key: 'yearly_price',
        label: 'Yearly Price',
        sortable: true,
    },
    {
        key: 'is_active',
        label: 'Status',
        sortable: true,
    },
    {
        key: 'is_featured',
        label: 'Featured',
        sortable: true,
    },
    {
        key: 'paddle_product_id',
        label: 'Paddle Integration',
        sortable: false,
    },
    {
        key: 'users_count',
        label: 'Users',
        sortable: true,
    },
    { key: '_actions', label: 'Actions' },
]);

const showDeleteDialog = ref(false);
const planToDelete = ref<BillingPlan | null>(null);

const confirmDelete = (plan: BillingPlan) => {
    planToDelete.value = plan;
    showDeleteDialog.value = true;
};

const cancelDelete = () => {
    showDeleteDialog.value = false;
    planToDelete.value = null;
};

const deletePlan = () => {
    if (!planToDelete.value) return;

    router.delete(`/billing/plans/${planToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            planToDelete.value = null;
        },
    });
};

const handleMenuAction = (action: string, planId: string | number) => {
    const plan = props.plans.find((p) => p.id === Number(planId));

    if (!plan) return;

    switch (action) {
        case 'edit':
            router.visit(`/billing/plans/${plan.id}/edit`);
            break;
        case 'delete':
            confirmDelete(plan);
            break;
    }
};
</script>

<template>
    <Head title="Billing Plans" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Billing Plans</h1>
                    <p class="text-muted-foreground">Manage subscription plans for your customers</p>
                </div>
                <div class="flex gap-2">
                    <div class="relative mr-2">
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search plans..."
                            class="rounded-md border px-4 py-2 focus:ring-2 focus:ring-primary focus:outline-none"
                        />
                    </div>

                    <Link href="/billing/features">
                        <Button variant="outline" class="mr-2">Features</Button>
                    </Link>
                    <Link href="/billing/plans/create">
                        <Button class="bg-primary text-primary-foreground hover:bg-primary/90">
                            <Plus class="mr-1 h-4 w-4" />
                            Add Plan
                        </Button>
                    </Link>
                </div>
            </div>

            <div v-if="props.plans.length === 0" class="flex items-center justify-center rounded-lg border p-8">
                <div class="text-center">
                    <p class="text-muted-foreground">No billing plans found. Create your first plan to get started.</p>
                    <Link href="/billing/plans/create" class="mt-4 inline-block">
                        <Button class="bg-primary text-primary-foreground">
                            <Plus class="mr-1 h-4 w-4" />
                            Create Plan
                        </Button>
                    </Link>
                </div>
            </div>

            <div v-else class="overflow-hidden rounded-lg border">
                <DataTable :data="props.plans" :columns="columns" :search="search" empty-message="No billing plans found">
                    <template #is_active="{ item: plan }">
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                            :class="{
                                'bg-green-100 text-green-800': plan.is_active,
                                'bg-gray-100 text-gray-800': !plan.is_active,
                            }"
                        >
                            {{ plan.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </template>

                    <template #is_featured="{ item: plan }">
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                            :class="{
                                'bg-yellow-100 text-yellow-800': plan.is_featured,
                                'bg-gray-100 text-gray-800': !plan.is_featured,
                            }"
                        >
                            {{ plan.is_featured ? 'Featured' : 'Standard' }}
                        </span>
                    </template>

                    <template #users_count="{ item: plan }">
                        {{ plan.users_count || 0 }}
                    </template>

                    <template #actions="{ item: plan }">
                        <ActionMenu :item-id="plan.id" @select="handleMenuAction">
                            <template #menu-items="{ handleAction }">
                                <ActionMenuButton :icon="Edit" text="Edit" @click="(e) => handleAction('edit', e)" />
                                <ActionMenuButton :icon="Trash" text="Delete" variant="destructive" @click="(e) => handleAction('delete', e)" />
                            </template>
                        </ActionMenu>
                    </template>
                </DataTable>
            </div>
        </div>

        <!-- Delete Dialog -->
        <Dialog :open="showDeleteDialog" @update:open="(val) => !val && cancelDelete()">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Plan</DialogTitle>
                </DialogHeader>
                <div class="py-4">
                    <p>
                        Are you sure you want to delete the plan <span class="font-semibold">{{ planToDelete?.name }}</span
                        >?
                    </p>
                    <p class="mt-2 text-sm text-destructive">This action cannot be undone.</p>
                </div>
                <DialogFooter>
                    <Button @click="cancelDelete" variant="outline">Cancel</Button>
                    <Button @click="deletePlan" variant="destructive">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
