<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const companyInfo = {
    legalName: "Tech360 Systems",
    product: "ValidTrack",
    email: "legal@tech360.systems"
};
</script>

<template>
    <div>

        <Head title="ValidTrack | Acceptable Use Policy" />

        <div class="bg-background text-foreground min-h-screen">
            <!-- Header with navigation -->
            <div class="border-b border-border">
                <div class="container mx-auto px-4">
                    <div class="flex justify-between items-center py-4">
                        <Link href="/" class="flex items-center">
                        <AppLogoIcon />
                        <h2 class="text-2xl font-bold text-foreground">ValidTrack</h2>
                        </Link>
                        <Link href="/legal" class="text-muted-foreground hover:text-primary font-medium">
                        Back to Legal Documents
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Content area -->
            <div class="container mx-auto px-4 py-12">
                <!-- Company information banner -->
                <div class="bg-card rounded-lg p-6 mb-8 border border-border">
                    <h3 class="text-lg font-medium text-foreground mb-2">Company Information</h3>
                    <div class="text-sm text-muted-foreground space-y-1">
                        <p><strong>Legal Name:</strong> {{ companyInfo.legalName }}</p>
                        <p><strong>Product:</strong> {{ companyInfo.product }}</p>
                        <p><strong>Contact for Policy Questions:</strong> {{ companyInfo.email }}</p>
                    </div>
                </div>

                <!-- Acceptable Use Policy -->
                <div class="prose max-w-none">
                    <h1>Acceptable Use Policy</h1>
                    <p class="text-lg">Last updated: June 5, 2025</p>

                    <h2>1. Introduction</h2>
                    <p>This Acceptable Use Policy ("AUP") outlines the rules and guidelines for using ValidTrack, a
                        document tracking and compliance platform by Tech360 Systems. By accessing or using our
                        services, you agree to comply with this AUP.</p>

                    <h2>2. Prohibited Activities</h2>
                    <p>When using ValidTrack, you agree NOT to:</p>
                    <ul>
                        <li>Violate any applicable laws, regulations, or third-party rights</li>
                        <li>Upload, share, or transmit any content that is illegal, harmful, threatening, abusive,
                            harassing, defamatory, vulgar, obscene, or otherwise objectionable</li>
                        <li>Impersonate any person or entity, or falsely state or otherwise misrepresent your
                            affiliation with a person or entity</li>
                        <li>Upload or transmit any material that contains viruses, trojan horses, worms, or any other
                            malicious code</li>
                        <li>Attempt to gain unauthorized access to any portion of our services or any other systems or
                            networks connected to our services</li>
                        <li>Use our services to facilitate the sale or distribution of illegal goods or services</li>
                        <li>Use our platform to manage inventory of prohibited items including but not limited to:
                            illegal drugs, weapons, counterfeit goods, or other contraband</li>
                        <li>Interfere with or disrupt our services or servers or networks connected to our services</li>
                        <li>Harvest or collect email addresses or other contact information of other users from our
                            services</li>
                        <li>Use our services to send unsolicited commercial communications (spam)</li>
                    </ul>

                    <h2>3. Prohibited Documents and Services</h2>
                    <p>ValidTrack may not be used to facilitate the management, tracking, or distribution of:</p>
                    <ul>
                        <li>Illegal drugs, controlled substances, or drug paraphernalia</li>
                        <li>Weapons, ammunition, or explosives</li>
                        <li>Counterfeit or unauthorized goods</li>
                        <li>Stolen goods or items infringing upon third-party rights</li>
                        <li>Items that promote hate, violence, racial intolerance, or the financial exploitation of a
                            crime</li>
                        <li>Items that are considered obscene or pornographic</li>
                        <li>Items that involve the exploitation of minors</li>
                        <li>Items that infringe upon an individual's privacy</li>
                        <li>Any products or services that violate applicable laws or regulations</li>
                    </ul>

                    <h2>4. System Integrity and Security</h2>
                    <p>Users must not attempt to:</p>
                    <ul>
                        <li>Probe, scan, or test the vulnerability of our system or network</li>
                        <li>Breach or otherwise circumvent any security or authentication measures</li>
                        <li>Access, tamper with, or use non-public areas of our services</li>
                        <li>Interfere with any user, host, or network, including sending viruses, overloading, flooding,
                            spamming, or mail-bombing</li>
                        <li>Automate access to our services in a manner that sends more requests to our servers than a
                            human could reasonably produce in the same period</li>
                    </ul>

                    <h2>5. Data Usage and Privacy</h2>
                    <p>Users must:</p>
                    <ul>
                        <li>Respect the privacy and data protection rights of other users</li>
                        <li>Only collect, use, and process personal data in accordance with applicable privacy laws</li>
                        <li>Not use data obtained from our services for marketing purposes without explicit consent</li>
                        <li>Not share access credentials with third parties</li>
                    </ul>

                    <h2>6. Compliance with Payment Processor Terms</h2>
                    <p>Users must comply with the terms and conditions of our payment processor, Paddle, including their
                        acceptable use policies and prohibited business categories.</p>

                    <h2>7. Reporting Violations</h2>
                    <p>If you become aware of any violation of this Acceptable Use Policy, please report it immediately
                        to {{ companyInfo.email }}.</p>

                    <h2>8. Consequences of Violation</h2>
                    <p>Violations of this AUP may result in:</p>
                    <ul>
                        <li>Temporary or permanent suspension of access to our services</li>
                        <li>Termination of your account</li>
                        <li>Reporting to law enforcement authorities</li>
                        <li>Legal action</li>
                    </ul>

                    <h2>9. Changes to this Policy</h2>
                    <p>We may update this Acceptable Use Policy from time to time. We will notify users of any
                        significant changes by posting the new policy on our website.</p>

                    <h2>10. Contact Information</h2>
                    <p>If you have any questions about this Acceptable Use Policy, please contact us at {{
                        companyInfo.email }}.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Additional styling can be added here if needed */
</style>
