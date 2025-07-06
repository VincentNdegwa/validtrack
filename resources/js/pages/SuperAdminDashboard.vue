<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import StatsCard from '@/components/dashboard/StatsCard.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { 
  Users, 
  FileText, 
  Building, 
  Tags, 
  BookType, 
  PlusCircle
} from 'lucide-vue-next';
import { type Company } from '@/types/models';

interface SuperAdminStats {
  totalCompanies: number;
  totalUsers: number;
  totalSubjects: number;
  totalDocuments: number;
  totalSubjectTypes: number;
  totalDocumentTypes: number;
}

interface Props {
  stats: SuperAdminStats;
  companies: Company[];
}

const props = defineProps<Props>();
const $page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  }
];
</script>

<template>
  <Head title="System Administration Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 p-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-foreground">System Administration Dashboard</h1>
          <p class="text-muted-foreground">Manage the entire ValidTrack platform</p>
        </div>
        
        <div class="flex gap-2">
          <Link href="/companies/create">
            <Button class="bg-primary text-primary-foreground">
              <PlusCircle class="mr-2 h-4 w-4" />
              Add Company
            </Button>
          </Link>
        </div>
      </div>

      <!-- System Stats -->
      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <StatsCard
          title="Total Companies"
          :value="props.stats.totalCompanies"
          :icon="Building"
          href="/companies"
          description="Total registered companies in the system"
        />
        <StatsCard
          title="Total Users"
          :value="props.stats.totalUsers" 
          :icon="Users"
          href="/users"
          description="All users across all companies"
        />
        <StatsCard
          title="Total Subjects"
          :value="props.stats.totalSubjects"
          :icon="BookType"
          href="/subjects"
          description="All subjects being managed"
        />
        <StatsCard
          title="Total Documents"
          :value="props.stats.totalDocuments"
          :icon="FileText" 
          href="/documents"
          description="All documents in the system"
        />
        <StatsCard
          title="Subject Types"
          :value="props.stats.totalSubjectTypes"
          :icon="Tags"
          href="/subject-types"
          description="Available subject type categories"
        />
        <StatsCard
          title="Document Types"
          :value="props.stats.totalDocumentTypes"
          :icon="FileText"
          href="/document-types"
          description="Available document type categories"
        />
      </div>

      <!-- Companies Table -->
      <Card>
        <CardHeader>
          <CardTitle>Companies</CardTitle>
          <CardDescription>
            All companies in the system
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Name</TableHead>
                <TableHead>Email</TableHead>
                <TableHead>Users</TableHead>
                <TableHead>Location</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="props.companies.length === 0">
                <TableCell colspan="5" class="text-center">No companies found</TableCell>
              </TableRow>
              <TableRow v-for="company in props.companies" :key="company.id">
                <TableCell class="font-medium">{{ company.name }}</TableCell>
                <TableCell>{{ company.email }}</TableCell>
                <TableCell>{{ company.users_count }}</TableCell>
                <TableCell>{{ company.location || 'N/A' }}</TableCell>
                <TableCell>
                  <div class="flex space-x-2">
                    <Link :href="`/companies/${company.slug}`" class="text-blue-600 hover:underline">View</Link>
                    <Link :href="`/companies/${company.slug}/edit`" class="text-amber-600 hover:underline">Edit</Link>
                    <form method="post" :action="`/companies/switch`">
                      <input type="hidden" name="_token" :value="$page.props.csrf_token">
                      <input type="hidden" name="company_id" :value="company.id" />
                      <button type="submit" class="text-indigo-600 hover:underline">Switch</button>
                    </form>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
