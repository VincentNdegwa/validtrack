<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, Dock, FileBadge2Icon, Users, ShieldCheck, Key, Settings, FileText, Building } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const hasPermission = (permission: string): boolean => {
    if (!user.value?.permissions) return false;
    return user.value?.permissions?.includes(permission) || false;
};

const isSuperAdmin = computed((): boolean => {
    if (!user.value?.roles) return false;
    return user.value.roles.some(role => role.name === 'super-admin');
});

const mainNavItems = computed(() => {

    if (isSuperAdmin.value) {
        return [
            {
                title: 'Dashboard',
                href: '/dashboard',
                icon: LayoutGrid,
                show: true
            },
            {
                title: 'Companies',
                href: '/companies',
                icon: Building,
                show: true
            },
            {
                title: 'Users Management',
                icon: Users,
                show: true,
                children: [
                    {
                        title: 'Users',
                        href: '/users',
                        icon: Users,
                        show: true
                    },
                    {
                        title: 'Roles',
                        href: '/roles',
                        icon: ShieldCheck,
                        show: true
                    },
                    {
                        title: 'Permissions',
                        href: '/permissions',
                        icon: Key,
                        show: true
                    }
                ]
            }
        ];
    }
    
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
            show: hasPermission('dashboard-view'),
        },
        {
            title: 'Subjects',
            href: '/subjects',
            icon: Dock,
            show: hasPermission('subjects-view'),
        },
        {
            title: 'Subject Types',
            href: '/subject-types',
            icon: Settings,
            show: hasPermission('subject-types-view'),
        },
        {
            title: 'Documents',
            href: '/documents',
            icon: FileBadge2Icon,
            show: hasPermission('documents-view'),
        },
        {
            title: 'Document Types',
            href: '/document-types',
            icon: FileText,
            show: hasPermission('document-types-view'),
        },
        {
            title: 'Users Management',
            icon: Users,
            show: hasPermission('users-view') || hasPermission('roles-view'),
            children: [
                {
                    title: 'Users',
                    href: '/users',
                    icon: Users,
                    show: hasPermission('users-view'),
                },
                {
                    title: 'Roles',
                    href: '/roles',
                    icon: ShieldCheck,
                    show: hasPermission('roles-view'),
                },
                {
                    title: 'Permissions',
                    href: '/permissions',
                    icon: Key,
                    show: hasPermission('roles-view'),
                }
            ]
        }
    ];
    
    return items.filter(item => item.show === true);
});

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
