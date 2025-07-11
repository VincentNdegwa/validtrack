<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Company Settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Company Settings" description="Update your company's name, logo, and preferences" />

                <Separator />

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Company Profile Section -->
                    <div class="space-y-6">
                        <HeadingSmall title="Company Profile" description="Update your company's name and logo" />

                        <div class="grid gap-2">
                            <Label for="name">Company Name</Label>
                            <Input id="name" v-model="form.name" class="mt-1 block w-full" required placeholder="Company name" />
                            <InputError class="mt-2" :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="logo">Company Logo</Label>
                            <div class="flex items-center space-x-4">
                                <img
                                    v-if="logoPreview || settings.logo"
                                    :src="logoPreview || `/storage/${settings.logo}`"
                                    alt="Company Logo"
                                    class="h-16 w-16 rounded-md object-cover"
                                />
                                <div>
                                    <Input id="logo" type="file" accept="image/*" @input="updateLogo" ref="logoInput" />
                                    <InputError class="mt-2" :message="errors.logo" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <Separator />

                    <!-- Preferences Section -->
                    <div class="space-y-6">
                        <HeadingSmall title="Preferences" description="Configure your timezone and reminder settings" />

                        <div class="grid gap-2">
                            <Label for="timezone">Timezone</Label>
                            <Select id="timezone" v-model="form.timezone">
                                <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz }}</option>
                            </Select>
                            <InputError class="mt-2" :message="errors.timezone" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="reminder_default_days">Default Reminder Days</Label>
                            <Input
                                id="reminder_default_days"
                                name="reminder_default_days"
                                type="number"
                                v-model="form.reminder_default_days"
                                class="mt-1 block w-full"
                                min="1"
                                max="365"
                                required
                                placeholder="5"
                            />
                            <p class="text-sm text-muted-foreground">Number of days before deadline when reminders will be sent by default</p>
                            <InputError class="mt-2" :message="errors.reminder_default_days" />
                        </div>
                    </div>

                    <Separator />

                    <!-- Notification Section -->
                    <div class="space-y-6">
                        <HeadingSmall title="Notifications" description="Configure your notification preferences" />
                        <div class="space-y-2">
                            <div class="flex items-center space-x-2">
                                <input
                                    type="checkbox"
                                    id="notification_email_enabled"
                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                    v-model="form.notification_email_enabled"
                                />
                                <Label for="notification_email_enabled" class="cursor-pointer select-none"> Enable email notifications </Label>
                            </div>
                            <InputError :message="errors.notification_email_enabled" />
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <Button type="submit" :disabled="loading">Save Changes</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-green-600">Saved successfully!</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';

// Components
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    settings: {
        name: string;
        logo: string | null;
        timezone: string;
        reminder_default_days: number;
        notification_email_enabled: boolean;
    };
    timezones: string[];
}

const props = defineProps<Props>();
const loading = ref(false);

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Company Settings',
        href: '/settings/company',
    },
];
const errors = ref({
    name: '',
    logo: '',
    timezone: '',
    reminder_default_days: '',
    notification_email_enabled: '',
});
const normalizeBoolean = (value: any): boolean => {
    if (typeof value === 'boolean') return value;
    if (typeof value === 'string') {
        const normalized = value.toLowerCase();
        return normalized === 'true' || normalized === '1' || normalized === 'yes';
    }
    if (typeof value === 'number') return value === 1;

    return false;
};
const form = useForm({
    name: props.settings.name,
    logo: null as File | null,
    timezone: props.settings.timezone,
    reminder_default_days: Number(props.settings.reminder_default_days),
    notification_email_enabled: normalizeBoolean(props.settings.notification_email_enabled),
});

const logoInput = ref<HTMLInputElement | null>(null);
const logoPreview = ref<string | null>(null);

const updateLogo = (e: Event) => {
    const target = e.target as HTMLInputElement;

    if (target.files && target.files.length > 0) {
        form.logo = target.files[0];

        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(form.logo);
    }
};

const submit = () => {
    const formData = new FormData();
    formData.append('_method', 'put');
    formData.append('name', form.name);
    formData.append('timezone', form.timezone);
    formData.append('reminder_default_days', form.reminder_default_days.toString());
    formData.append('notification_email_enabled', form.notification_email_enabled ? '1' : '0');
    if (form.logo) {
        formData.append('logo', form.logo);
    }
    loading.value = true;
    router.post(route('settings.company.update'), formData, {
        preserveScroll: true,
        onSuccess: () => {
            if (logoInput.value) {
                logoInput.value.value = '';
            }

            if (form.logo) {
                logoPreview.value = null;
                form.logo = null;
            }
            form.recentlySuccessful = true;
        },
        onError: (err) => {
            errors.value = {
                name: err?.name ?? '',
                logo: err?.logo ?? '',
                timezone: err?.timezone ?? '',
                reminder_default_days: err?.reminder_default_days ?? '',
                notification_email_enabled: err?.notification_email_enabled ?? '',
            };
        },
        onFinish: () => {
            loading.value = false;
        },
    });
};
</script>
