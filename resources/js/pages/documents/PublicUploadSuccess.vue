<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Head } from '@inertiajs/vue3';

interface Props {
    allCompleted?: boolean;
}

defineProps<Props>();
</script>

<template>
    <Head title="Upload Successful" />

    <div class="flex min-h-screen flex-col items-center justify-center p-4 bg-background">
        <div class="w-full max-w-md rounded-xl border border-border bg-card p-8 shadow-lg text-center transition-all duration-300 hover:shadow-xl">
            <div class="mb-8">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 shadow-sm">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-8 w-8 text-green-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="mt-6 text-2xl font-bold text-foreground">Upload Successful!</h1>
                <p class="mt-3 text-muted-foreground">
                    Thank you for uploading your document. It has been received and will be processed shortly.
                </p>
            </div>

            <div class="rounded-lg border border-muted p-4 mb-6">
                <div v-if="allCompleted" class="flex flex-col items-center">
                    <svg 
                        xmlns="http://www.w3.org/2000/svg" 
                        class="h-6 w-6 text-amber-500 mb-2" 
                        fill="none" 
                        viewBox="0 0 24 24" 
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="text-sm font-medium text-foreground">
                        All required documents have been uploaded
                    </p>
                    <p class="text-xs text-muted-foreground mt-1">
                        This link has now expired and cannot be used again
                    </p>
                </div>
                <div v-else class="flex flex-col items-center">
                    <svg 
                        xmlns="http://www.w3.org/2000/svg" 
                        class="h-6 w-6 text-blue-500 mb-2" 
                        fill="none" 
                        viewBox="0 0 24 24" 
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-medium text-foreground">
                        You may have additional documents to upload
                    </p>
                    <p class="text-xs text-muted-foreground mt-1">
                        Return to the upload page to submit any remaining documents
                    </p>
                </div>
            </div>

            <div>
                <Button
                    v-if="allCompleted"
                    as="a"
                    href="/"
                    class="w-full bg-primary text-primary-foreground hover:bg-primary/90 transition-all duration-200"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Return to Homepage
                </Button>
                <Button
                    v-else
                    as="a"
                    href="javascript:history.back()"
                    class="w-full bg-primary text-primary-foreground hover:bg-primary/90 transition-all duration-200"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                    </svg>
                    Return to Upload Page
                </Button>
            </div>
            
            <p class="mt-6 text-xs text-muted-foreground">
                For security purposes, your upload activity has been logged.
            </p>
        </div>
    </div>
</template>
