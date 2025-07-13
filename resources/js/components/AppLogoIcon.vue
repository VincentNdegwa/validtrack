<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img src="/logo.svg" alt="ValidTrack Logo" class="size-5 fill-current text-white dark:text-black" v-bind="$attrs" />
</template>
