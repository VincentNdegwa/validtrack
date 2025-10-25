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
                        <HeadingSmall title="Company Profile" description="Update your company's name and contact information" />

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="name">Company Name</Label>
                                <Input id="name" v-model="form.name" class="mt-1 block w-full" required placeholder="Company name" />
                                <InputError class="mt-2" :message="errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="email">Company Email</Label>
                                <Input id="email" type="email" v-model="form.email" class="mt-1 block w-full" placeholder="company@example.com" />
                                <InputError class="mt-2" :message="errors.email" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="phone">Phone Number</Label>
                                <Input id="phone" v-model="form.phone" class="mt-1 block w-full" placeholder="+1 (555) 123-4567" />
                                <InputError class="mt-2" :message="errors.phone" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="website">Website</Label>
                                <Input id="website" v-model="form.website" class="mt-1 block w-full" placeholder="https://yourcompany.com" />
                                <InputError class="mt-2" :message="errors.website" />
                            </div>

                            <div class="grid gap-2 sm:col-span-2">
                                <Label for="address">Address</Label>
                                <Input
                                    id="address"
                                    v-model="form.address"
                                    class="mt-1 block w-full"
                                    placeholder="123 Business Ave, Suite 100, City, State, ZIP"
                                />
                                <InputError class="mt-2" :message="errors.address" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="location">Location</Label>
                                <Input id="location" v-model="form.location" class="mt-1 block w-full" placeholder="City, Country" />
                                <InputError class="mt-2" :message="errors.location" />
                            </div>

                            <div class="grid gap-2 sm:col-span-2">
                                <Label for="logo">Company Logo</Label>
                                <div class="flex items-center space-x-4">
                                    <img
                                        v-if="logoPreview || settings.logo"
                                        :src="logoPreview || `${settings.logo}`"
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

                            <div class="flex flex-col space-y-2">
                                <div v-for="(day, index) in form.reminder_default_days" :key="index" class="flex items-center gap-2">
                                    <Input
                                        :id="`reminder_default_days_${index}`"
                                        v-model="form.reminder_default_days[index]"
                                        type="number"
                                        class="mt-1 block w-full"
                                        min="1"
                                        max="365"
                                        required
                                        placeholder="Days before deadline"
                                    />
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        class="shrink-0"
                                        @click="removeReminderDay(index)"
                                        :disabled="form.reminder_default_days.length <= 1"
                                    >
                                        Remove
                                    </Button>
                                </div>

                                <Button type="button" variant="secondary" class="mt-2" @click="addReminderDay"> + Add Another Reminder </Button>
                            </div>

                            <p class="text-sm text-muted-foreground">Configure multiple reminders before deadline (e.g., 30, 14, 7, 1 days before)</p>

                            <InputError class="mt-2" :message="errors?.['reminder_default_days']" />
                            <InputError v-if="errors?.['reminder_default_days.0']" class="mt-1" :message="errors?.['reminder_default_days.0']" />
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

                    <Separator />

                    <!-- Slack Integration Section -->
                    <div class="space-y-6">
                        <HeadingSmall
                            title="Slack Integration"
                            description="Connect your workspace with Slack to receive notifications and updates"
                        />

                        <div class="space-y-4">
                            <div class="flex items-center justify-between rounded-lg border bg-card p-4">
                                <div class="space-y-1">
                                    <h4 class="text-sm font-medium">
                                        {{ settings.has_slack_integration ? 'Connected to Slack' : 'Connect to Slack' }}
                                    </h4>
                                    <p class="text-sm text-muted-foreground">
                                        {{
                                            settings.has_slack_integration
                                                ? `Connected to workspace: ${settings.slack.team}`
                                                : 'Connect your Slack workspace to receive notifications'
                                        }}
                                    </p>
                                </div>
                                <Can permission="company-settings-edit">
                                    <Button
                                        type="button"
                                        :variant="settings.has_slack_integration ? 'destructive' : 'default'"
                                        @click="settings.has_slack_integration ? disconnectSlack() : connectSlack()"
                                    >
                                        {{ settings.has_slack_integration ? 'Disconnect' : 'Connect' }}
                                    </Button>
                                </Can>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <Can permission="company-settings-edit">
                            <Button type="submit" :disabled="form.processing">Save Changes</Button>
                        </Can>

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
import Can from '@/components/auth/Can.vue';
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
        email: string | null;
        phone: string | null;
        address: string | null;
        website: string | null;
        location: string | null;
        logo: string | null;
        timezone: string;
        reminder_default_days: number[] | number;
        notification_email_enabled: boolean;
        has_slack_integration: boolean;
        slack: {
            channel: string;
            team: string;
        };
    };
    timezones: string[];
}

const props = defineProps<Props>();

const connectSlack = () => {
    window.location.href = route('slack.redirect');
};

const disconnectSlack = () => {
    router.delete(route('slack.disconnect'), {
        preserveScroll: true,
        onSuccess: () => {
            // The page will refresh automatically
        },
    });
};

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Company Settings',
        href: '/settings/company',
    },
];
const errors = ref({
    name: '',
    email: '',
    phone: '',
    address: '',
    website: '',
    location: '',
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
const normalizeReminderDays = (days: number[] | number): number[] => {
    if (Array.isArray(days)) {
        return days;
    }
    return [Number(days)];
};

const form = useForm({
    name: props.settings.name,
    email: props.settings.email || '',
    phone: props.settings.phone || '',
    address: props.settings.address || '',
    website: props.settings.website || '',
    location: props.settings.location || '',
    logo: null as File | null,
    timezone: props.settings.timezone,
    reminder_default_days: normalizeReminderDays(props.settings.reminder_default_days),
    notification_email_enabled: normalizeBoolean(props.settings.notification_email_enabled),
});

const logoInput = ref<HTMLInputElement | null>(null);
const logoPreview = ref<string | null>(null);

const addReminderDay = () => {
    const defaultValue = form.reminder_default_days.length > 0 ? Math.min(...form.reminder_default_days) - 1 : 7;
    const newValue = Math.max(1, defaultValue);
    form.reminder_default_days.push(newValue);
    form.reminder_default_days.sort((a, b) => b - a);
};

const removeReminderDay = (index: number) => {
    if (form.reminder_default_days.length <= 1) {
        return;
    }

    form.reminder_default_days.splice(index, 1);
};

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
    formData.append('email', form.email);
    formData.append('phone', form.phone);
    formData.append('address', form.address);
    formData.append('website', form.website);
    formData.append('location', form.location);
    formData.append('timezone', form.timezone);
    form.reminder_default_days.forEach((day, index) => {
        formData.append(`reminder_default_days[${index}]`, day.toString());
    });
    formData.append('notification_email_enabled', form.notification_email_enabled ? '1' : '0');
    if (form.logo) {
        formData.append('logo', form.logo);
    }
    form.processing = true;
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
                email: err?.email ?? '',
                phone: err?.phone ?? '',
                address: err?.address ?? '',
                website: err?.website ?? '',
                location: err?.location ?? '',
                logo: err?.logo ?? '',
                timezone: err?.timezone ?? '',
                reminder_default_days: err?.reminder_default_days ?? '',
                notification_email_enabled: err?.notification_email_enabled ?? '',
            };
        },
        onFinish: () => {
            form.processing = false;
        },
    });
};
</script>
