<script setup lang="ts">
import Can from '@/components/auth/Can.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Subject, type SubjectType } from '@/types/models';
import { Head, Link } from '@inertiajs/vue3';

interface Props {
    subjectType: SubjectType;
    subjects: Subject[];
}

const props = defineProps<Props>();

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
                    <Can permission="subject-types-edit">
                        <Link :href="`/subject-types/${subjectType.slug}/edit`">
                            <Button variant="outline">Edit</Button>
                        </Link>
                    </Can>
                    <Can permission="subject-types-view">
                        <Link href="/subject-types">
                            <Button variant="ghost">Back to Subject Types</Button>
                        </Link>
                    </Can>
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
                            <tr v-for="subject in subjects" :key="subject.id" class="border-t border-border hover:bg-muted/30">
                                <td class="px-6 py-4 font-medium">{{ subject.name }}</td>
                                <td class="px-6 py-4">{{ subject.email || 'N/A' }}</td>
                                <td class="px-6 py-4">{{ subject.phone || 'N/A' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center">
                                        <span :class="[getStatusColor(subject.status), 'mr-2 h-2 w-2 rounded-full']"></span>
                                        {{ getStatusText(subject.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <Can permission="subjects-view">
                                            <Link :href="`/subjects/${subject.slug}`" class="text-blue-600 hover:underline">View</Link>
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
