<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import type { User } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { LogOut, Settings, Building, SwitchCamera, Check } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    user: User;
}

const props = defineProps<Props>();

const handleLogout = () => {
    router.flushAll();
};

// Get the page properties including active company context
const page = usePage();
const activeCompanyId = computed(() => page.props.activeCompanyId as number | undefined);

// Check if the user has the super-admin role
const isSuperAdmin = (user: User) => {
    if (!user.roles) return false;
    return user.roles.some(role => role.name === 'super-admin');
};

// Helper to display the active company context
const isCompanyActive = computed(() => {
    return !!activeCompanyId.value;
});

// Clear the active company context
const clearCompanyContext = () => {
    router.post('/companies/switch', {
        company_id: null
    });
};
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    
    <!-- Super Admin Company Management -->
    <template v-if="isSuperAdmin(user)">
        <DropdownMenuGroup>
            <DropdownMenuLabel class="text-xs font-medium text-muted-foreground">Company Management</DropdownMenuLabel>
            
            <!-- Company context indicator -->
            <div v-if="isCompanyActive" class="flex items-center justify-between px-2 py-1.5 text-xs">
                <span>Viewing in company context</span>
                <button 
                    @click="clearCompanyContext"
                    class="ml-2 text-xs text-blue-600 hover:underline"
                >
                    Clear
                </button>
            </div>
            
            <DropdownMenuItem :as-child="true">
                <Link class="block w-full" href="/companies" prefetch as="button">
                    <Building class="mr-2 h-4 w-4" />
                    Manage Companies
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem :as-child="true">
                <Link class="block w-full" href="/companies/create" prefetch as="button">
                    <SwitchCamera class="mr-2 h-4 w-4" />
                    Add Company
                </Link>
            </DropdownMenuItem>
        </DropdownMenuGroup>
        <DropdownMenuSeparator />
    </template>
    
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="route('profile.edit')" prefetch as="button">
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link class="block w-full" method="post" :href="route('logout')" @click="handleLogout" as="button">
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>
