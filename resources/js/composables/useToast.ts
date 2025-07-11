import { inject, InjectionKey, ref } from 'vue';

export interface ToastOptions {
    title?: string;
    description: string;
    variant?: 'default' | 'success' | 'error' | 'warning' | 'info';
    duration?: number;
}

interface ToastApi {
    toast: (options: ToastOptions) => void;
}

export const ToastSymbol: InjectionKey<ToastApi> = Symbol('Toast');

// Global toast reference for direct access
const globalToastProvider = ref<{ addToast: (toast: ToastOptions) => void } | null>(null);

export function setToastProvider(provider: any) {
    globalToastProvider.value = provider;
}

export function useToast(): ToastApi {
    const injectedToast = inject(ToastSymbol, null);

    const toast = (options: ToastOptions) => {
        if (injectedToast) {
            injectedToast.toast(options);
        } else if (globalToastProvider.value) {
            globalToastProvider.value.addToast(options);
        } else {
            console.error('Toast provider not found. Make sure ToastProvider is mounted.');
        }
    };

    return { toast };
}

// Helper methods for specific toast types
export function useSuccessToast() {
    const { toast } = useToast();
    return (description: string, title = 'Success') => {
        toast({ title, description, variant: 'success' });
    };
}

export function useErrorToast() {
    const { toast } = useToast();
    return (description: string, title = 'Error') => {
        toast({ title, description, variant: 'error' });
    };
}

export function useWarningToast() {
    const { toast } = useToast();
    return (description: string, title = 'Warning') => {
        toast({ title, description, variant: 'warning' });
    };
}

export function useInfoToast() {
    const { toast } = useToast();
    return (description: string, title = 'Info') => {
        toast({ title, description, variant: 'info' });
    };
}
