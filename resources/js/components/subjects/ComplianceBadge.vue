<script setup lang="ts">
import { StatusBadge } from '@/components/ui/status-badge';
import { computed } from 'vue';

interface Props {
    complianceStatus: boolean;
    missingDocuments: Array<{
        id: number;
        name: string;
    }>;
}

const props = defineProps<Props>();

const getTooltipText = computed(() => {
    if (props.complianceStatus) {
        return 'All required documents are present and valid';
    }

    return `Missing documents: ${props.missingDocuments.map((doc) => doc.name).join(', ')}`;
});
</script>

<template>
    <StatusBadge :active="complianceStatus" activeText="Compliant" inactiveText="Missing Docs" class="cursor-help" :title="getTooltipText">
    </StatusBadge>
</template>
