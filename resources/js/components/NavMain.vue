<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDown, ChevronRight } from 'lucide-vue-next';
import { computed, ref } from 'vue';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();

const expandedMenu = ref(localStorage.getItem('nav-expanded') || '');

const toggleExpanded = (title: string) => {
    if (expandedMenu.value === title) {
        expandedMenu.value = '';
        localStorage.removeItem('nav-expanded');
    } else {
        expandedMenu.value = title;
        localStorage.setItem('nav-expanded', title);
    }
};

const isExpanded = computed(() => (title: string) => {
    return expandedMenu.value === title;
});

const isItemActive = (item: NavItem): boolean => {
    if (item.href && item.href === (page.url as string)) return true;

    if (item.children) {
        return item.children.some((child) => isItemActive(child));
    }

    return false;
};

const getVisibleChildren = (item: NavItem): NavItem[] => {
    if (!item.children) return [];
    return item.children.filter((child) => child.show === true);
};

const hasVisibleChildren = (item: NavItem): boolean => {
    if (!item.children) return false;
    return item.children.some((child) => child.show === true);
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <!-- Standard menu item - only show if show property is true or undefined -->
                <SidebarMenuItem v-if="!item.children && item.show !== false && item.href">
                    <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
                        <Link :href="item.href">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>

                <!-- Dropdown menu item - only show if it has visible children -->
                <template v-else-if="hasVisibleChildren(item)">
                    <SidebarMenuItem>
                        <SidebarMenuButton :is-active="isItemActive(item)" @click="toggleExpanded(item.title)">
                            <component :is="item.icon" />
                            <span class="flex-1">{{ item.title }}</span>
                            <component :is="isExpanded(item.title) ? ChevronDown : ChevronRight" class="h-4 w-4" />
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- Submenu items -->
                    <div v-if="isExpanded(item.title)" class="ml-4 space-y-1 border-l border-border pl-2">
                        <SidebarMenuItem v-for="child in getVisibleChildren(item)" :key="child.title">
                            <SidebarMenuButton as-child :is-active="child.href === page.url" :tooltip="child.title" v-if="child.href">
                                <Link :href="child.href">
                                    <component :is="child.icon" class="h-4 w-4" />
                                    <span>{{ child.title }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </div>
                </template>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
