<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Head } from '@inertiajs/vue3';

interface Props {
    allCompleted?: boolean;
}

const props = defineProps<Props>();
</script>

<template>
    <Head title="Upload Successful" />

    <div class="flex min-h-screen flex-col items-center justify-center p-4">
        <div class="w-full max-w-md rounded-xl border border-border bg-card p-6 shadow-lg text-center">
            <div class="mb-6">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6 text-green-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="mt-4 text-2xl font-bold text-foreground">Upload Successful!</h1>
                <p class="mt-2 text-muted-foreground">
                    Thank you for uploading your document. It has been received and will be processed.
                </p>
            </div>

            <p class="text-sm text-muted-foreground" v-if="props.allCompleted">
                All required documents have been uploaded. This link has now expired and cannot be used again.
            </p>
            <p class="text-sm text-muted-foreground" v-else>
                You can return to the upload page to submit any additional documents.
            </p>

            <div class="mt-6">
                <Button
                    v-if="props.allCompleted"
                    as="a"
                    href="/"
                    class="w-full bg-primary text-primary-foreground hover:bg-primary/90"
                >
                    Return to Homepage
                </Button>
                <Button
                    v-else
                    as="a"
                    href="javascript:history.back()"
                    class="w-full bg-primary text-primary-foreground hover:bg-primary/90"
                >
                    Return to Upload Page
                </Button>
            </div>
        </div>
    </div>
</template>
