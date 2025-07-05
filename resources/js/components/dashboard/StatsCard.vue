<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { defineProps } from 'vue';
import { type Component } from 'vue';

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
  href: undefined
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
  }
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
  <component :is="href ? 'a' : 'div'" :href="href" :class="['block', href ? 'hover:no-underline' : '']">
    <Card :class="['transition-all hover:scale-[1.02]', href ? 'hover:shadow-md cursor-pointer' : '']">
      <CardHeader :class="['flex flex-row items-center justify-between space-y-0 pb-2']">
        <CardTitle class="text-sm font-medium truncate">{{ title }}</CardTitle>
        <div 
          :class="[
            'flex h-8 w-8 items-center justify-center rounded-full', 
            colorMap[color].bgLight, 
            colorMap[color].bgDark
          ]"
        >
          <component 
            :is="icon" 
            :class="['h-4 w-4', colorMap[color].iconLight, colorMap[color].iconDark]" 
          />
        </div>
      </CardHeader>
      <CardContent class="pt-4">
        <div class="flex items-baseline justify-between">
          <div class="text-2xl font-bold">{{ value }}</div>
          <div v-if="trend !== undefined" class="flex items-center text-xs">
            <span class="font-semibold" :class="trendClass">
              {{ trend > 0 ? '+' : '' }}{{ trend }}%
            </span>
          </div>
        </div>
        <p class="text-xs text-muted-foreground mt-1">
          {{ description }}
        </p>
      </CardContent>
    </Card>
  </component>
</template>
