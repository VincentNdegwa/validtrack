<script setup lang="ts">
import { computed, ref } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    text: string;
    icon: any;
    action?: string;
    variant?: 'default' | 'destructive';
}>();

const emit = defineEmits<{
    (e: 'click', event: MouseEvent): void;
}>();

const buttonClass = computed(() => {
    return props.variant === 'destructive' 
        ? 'text-red-600' 
        : '';
});

const showConfirmDialog = ref(false);

const handleClick = (e: MouseEvent) => {
    if (props.variant === 'destructive') {
        e.preventDefault();
        e.stopPropagation();
        showConfirmDialog.value = true;
    } else {
        emit('click', e);
    }
};

const handleConfirm = (e: MouseEvent) => {
    showConfirmDialog.value = false;
    emit('click', e);
};
</script>

<template>
    <button 
        @click="handleClick" 
        class="flex items-center px-2 py-1.5 text-sm hover:bg-muted text-left w-full"
        :class="buttonClass"
    >
        <component :is="icon" class="h-4 w-4 mr-2" />
        <span>{{ text }}</span>
    </button>

    <Dialog v-model:open="showConfirmDialog">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Confirm Action</DialogTitle>
                <DialogDescription>
                    Are you sure you want to {{ text.toLowerCase() }} this item? This action cannot be undone.
                </DialogDescription>
            </DialogHeader>

            <DialogFooter>
                <Button variant="outline" @click="showConfirmDialog = false">Cancel</Button>
                <Button variant="destructive" @click="handleConfirm">Confirm {{ text }}</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
