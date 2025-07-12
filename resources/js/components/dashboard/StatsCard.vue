<script setup lang="ts">
import { Card, CardTitle } from '@/components/ui/card';
import { defineProps, type Component } from 'vue';

interface Props {
    title: string;
    value: number | string;
    description: string;
    icon: Component;
    trend?: number;
    color?: 'default' | 'primary' | 'success' | 'warning' | 'danger';
    href?: string;
}

const props = withDefaults(defineProps<Props>(), {
    color: 'default',
    trend: undefined,
    href: undefined,
});

const colorMap = {
    default: {
        bgLight: 'bg-slate-100',
        bgDark: 'dark:bg-slate-800',
        iconLight: 'text-slate-600',
        iconDark: 'dark:text-slate-400',
        accentBg: 'bg-slate-50 dark:bg-slate-900/50',
    },
    primary: {
        bgLight: 'bg-primary/10',
        bgDark: 'dark:bg-primary/20',
        iconLight: 'text-primary',
        iconDark: 'dark:text-primary/90',
        accentBg: 'bg-primary-50 dark:bg-primary-900/30',
    },
    success: {
        bgLight: 'bg-green-100',
        bgDark: 'dark:bg-green-900/20',
        iconLight: 'text-green-600',
        iconDark: 'dark:text-green-500',
        accentBg: 'bg-green-50 dark:bg-green-900/30',
    },
    warning: {
        bgLight: 'bg-amber-100',
        bgDark: 'dark:bg-amber-900/20',
        iconLight: 'text-amber-600',
        iconDark: 'dark:text-amber-500',
        accentBg: 'bg-amber-50 dark:bg-amber-900/30',
    },
    danger: {
        bgLight: 'bg-red-100',
        bgDark: 'dark:bg-red-900/20',
        iconLight: 'text-red-600',
        iconDark: 'dark:text-red-500',
        accentBg: 'bg-red-50 dark:bg-red-900/30',
    },
};

const trendClass = props.trend
    ? props.trend > 0
        ? 'text-green-600 dark:text-green-500'
        : props.trend < 0
          ? 'text-red-600 dark:text-red-500'
          : 'text-slate-600 dark:text-slate-400'
    : '';
</script>

<template>
    <component :is="href ? 'a' : 'div'" :href="href" :class="['block w-full', href ? 'hover:no-underline' : '']">
        <Card :class="[
            'relative overflow-hidden border-l-4 transition-all hover:translate-y-[-2px]', 
            href ? 'cursor-pointer hover:shadow-lg' : '',
            `border-l-${colorMap[color].iconLight.replace('text-', '')}`
        ]">
            <div class="absolute right-0 top-0 opacity-5">
                <component :is="icon" class="h-20 w-20 -rotate-12" />
            </div>

            <div class="p-5">
                <!-- Icon and Title row -->
                <div class="mb-3 flex items-center">
                    <div :class="['mr-3 flex h-9 w-9 items-center justify-center rounded-md', colorMap[color].bgLight, colorMap[color].bgDark]">
                        <component :is="icon" :class="['h-5 w-5', colorMap[color].iconLight, colorMap[color].iconDark]" />
                    </div>
                    <CardTitle class="text-md font-medium">{{ title }}</CardTitle>
                </div>

                <!-- Value and trend -->
                <div class="mb-2 flex items-baseline justify-between">
                    <div class="text-3xl font-bold tracking-tight">{{ value }}</div>
                    <div v-if="trend !== undefined" 
                        :class="['flex items-center rounded-full px-2 py-0.5 text-xs font-medium', 
                        trend > 0 ? 'bg-green-50 dark:bg-green-900/20' : 
                        trend < 0 ? 'bg-red-50 dark:bg-red-900/20' : 
                        'bg-gray-50 dark:bg-gray-800/30']">
                        <span :class="trendClass" class="flex items-center">
                            <svg v-if="trend > 0" class="mr-1 h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5 5 5M7 17l5-5 5 5" />
                            </svg>
                            <svg v-else-if="trend < 0" class="mr-1 h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5-5-5M17 7l-5 5-5-5" />
                            </svg>
                            {{ trend > 0 ? '+' : '' }}{{ trend }}%
                        </span>
                    </div>
                </div>

                <!-- Description -->
                <div class="flex items-center text-sm text-muted-foreground">
                    <span>{{ description }}</span>
                </div>
                
                <!-- Hover indicator for clickable cards -->
                <div v-if="href" class="absolute bottom-2 right-2 opacity-0 transition-opacity group-hover:opacity-70">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </div>
            </div>
        </Card>
    </component>
</template>
