<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDown, ChevronRight } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    items: NavItem[];
}>();

const page = usePage();
const expandedItems = ref<Record<string, boolean>>({});

const toggleExpanded = (title: string) => {
    expandedItems.value[title] = !expandedItems.value[title];
};

const isItemActive = (item: NavItem): boolean => {
    if (item.href && item.href === (page.url as string)) return true;
    
    // Check if any child is active
    if (item.children) {
        return item.children.some(child => isItemActive(child));
    }
    
    return false;
};

const isExpanded = computed(() => (title: string) => {
    // Auto-expand if a child is active
    return expandedItems.value[title] ?? false;
});

// Filter visible children
const getVisibleChildren = (item: NavItem): NavItem[] => {
    if (!item.children) return [];
    return item.children.filter(child => child.show !== false);
};

// Check if item has visible children
const hasVisibleChildren = (item: NavItem): boolean => {
    if (!item.children) return false;
    return item.children.some(child => child.show !== false);
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <!-- Standard menu item -->
                <SidebarMenuItem v-if="!item.children">
                    <SidebarMenuButton 
                        as-child 
                        :is-active="item.href === (page.url as string)" 
                        :tooltip="item.title"
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
                
                <!-- Dropdown menu item -->
                <template v-else-if="hasVisibleChildren(item)">
                    <SidebarMenuItem>
                        <SidebarMenuButton 
                            :is-active="isItemActive(item)"
                            @click="toggleExpanded(item.title)"
                        >
                            <component :is="item.icon" />
                            <span class="flex-1">{{ item.title }}</span>
                            <component 
                                :is="isExpanded(item.title) ? ChevronDown : ChevronRight" 
                                class="h-4 w-4" 
                            />
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    
                    <!-- Submenu items -->
                    <div v-if="isExpanded(item.title)" class="ml-4 space-y-1 pl-2 border-l border-border">
                        <SidebarMenuItem v-for="child in getVisibleChildren(item)" :key="child.title">
                            <SidebarMenuButton 
                                as-child 
                                :is-active="child.href === (page.url as string)"
                                :tooltip="child.title"
                            >
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
