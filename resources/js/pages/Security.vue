<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Head, Link } from '@inertiajs/vue3';

const companyInfo = {
    legalName: 'Tech360 Systems',
    product: 'ValidTrack',
    email: 'security@tech360.systems',
};
</script>

<template>
    <div>
        <Head title="ValidTrack | Security" />

        <div class="min-h-screen bg-background text-foreground">
            <!-- Header with navigation -->
            <div class="border-b border-border">
                <div class="container mx-auto px-4">
                    <div class="flex items-center justify-between py-4">
                        <Link href="/" class="flex items-center">
                            <AppLogoIcon />

                            <h2 class="text-2xl font-bold text-foreground">ValidTrack</h2>
                        </Link>
                        <Link href="/legal" class="font-medium text-muted-foreground hover:text-primary"> Back to Legal Documents </Link>
                    </div>
                </div>
            </div>

            <!-- Content area -->
            <div class="container mx-auto px-4 py-12">
                <!-- Company information banner -->
                <div class="mb-8 rounded-lg border border-border bg-card p-6">
                    <h3 class="mb-2 text-lg font-medium text-foreground">Company Information</h3>
                    <div class="space-y-1 text-sm text-muted-foreground">
                        <p><strong>Legal Name:</strong> {{ companyInfo.legalName }}</p>
                        <p><strong>Product:</strong> {{ companyInfo.product }}</p>
                        <p><strong>Contact for Security Concerns:</strong> {{ companyInfo.email }}</p>
                    </div>
                </div>

                <!-- Security Policy -->
                <div class="prose max-w-none">
                    <h1>Security Policy</h1>
                    <p class="text-lg">Last updated: June 5, 2025</p>

                    <div class="mb-8 rounded-lg border border-primary bg-primary/10 p-4">
                        <div class="flex items-center">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="mr-3 h-6 w-6 text-primary"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 11c0-1.657-1.343-3-3-3s-3 1.343-3 3c0 1.657 1.343 3 3 3s3-1.343 3-3zm6 0c0-1.657-1.343-3-3-3s-3 1.343-3 3c0 1.657 1.343 3 3 3s3-1.343 3-3z"
                                />
                            </svg>
                            <p class="font-medium text-primary">
                                ValidTrack is secured with SSL encryption. All data transmitted between your browser and our servers is encrypted.
                            </p>
                        </div>
                    </div>

                    <h2>1. Our Commitment to Security</h2>
                    <p>
                        At ValidTrack, we take the security of your data and our platform seriously. We implement industry-standard security measures
                        to protect your information and ensure the integrity of our services.
                    </p>

                    <h2>2. Data Protection</h2>
                    <p>We protect your data through:</p>
                    <ul>
                        <li>
                            <strong>Encryption:</strong> All data transmitted between your browser and our servers is encrypted using TLS/SSL
                            protocols.
                        </li>
                        <li><strong>Secure Storage:</strong> Data at rest is encrypted using AES-256 encryption.</li>
                        <li><strong>Regular Backups:</strong> We perform regular backups of all customer data to prevent loss.</li>
                        <li>
                            <strong>Access Controls:</strong> We implement strict access controls to ensure only authorized personnel can access
                            sensitive data.
                        </li>
                    </ul>

                    <h2>3. Infrastructure Security</h2>
                    <p>Our infrastructure security measures include:</p>
                    <ul>
                        <li><strong>Firewalls:</strong> Advanced firewall protection to prevent unauthorized access.</li>
                        <li><strong>DDoS Protection:</strong> Systems to mitigate distributed denial-of-service attacks.</li>
                        <li><strong>Intrusion Detection:</strong> 24/7 monitoring for suspicious activities.</li>
                        <li><strong>Vulnerability Scanning:</strong> Regular scanning for potential vulnerabilities.</li>
                        <li>
                            <strong>Secure Data Centers:</strong> Our servers are hosted in SOC 2 compliant data centers with physical security
                            measures.
                        </li>
                    </ul>

                    <h2>4. Payment Security</h2>
                    <p>All payment processing is handled securely through our payment processor, Paddle:</p>
                    <ul>
                        <li>We do not store your full credit card details on our servers.</li>
                        <li>Payment processing complies with PCI DSS (Payment Card Industry Data Security Standard).</li>
                        <li>All payment transactions are encrypted with industry-standard protocols.</li>
                    </ul>

                    <h2>5. Account Security</h2>
                    <p>We provide several features to help you secure your account:</p>
                    <ul>
                        <li><strong>Strong Password Requirements:</strong> We enforce minimum password strength requirements.</li>
                        <li><strong>Two-Factor Authentication (2FA):</strong> Additional security layer for account access.</li>
                        <li><strong>Session Management:</strong> Ability to view and terminate active sessions.</li>
                        <li><strong>Login Notifications:</strong> Alerts for suspicious login attempts.</li>
                    </ul>

                    <h2>6. Security Compliance</h2>
                    <p>ValidTrack is committed to complying with relevant security standards and regulations:</p>
                    <ul>
                        <li>GDPR compliance for European users</li>
                        <li>Regular security audits and assessments</li>
                        <li>Industry best practices for web application security</li>
                    </ul>

                    <h2>7. Security Updates</h2>
                    <p>We regularly update our systems and applications to address security vulnerabilities:</p>
                    <ul>
                        <li>Prompt application of security patches</li>
                        <li>Regular updates to third-party dependencies</li>
                        <li>Continuous monitoring of security advisories</li>
                    </ul>

                    <h2>8. Security Incident Response</h2>
                    <p>In the event of a security incident:</p>
                    <ul>
                        <li>We have a dedicated team to respond to security incidents</li>
                        <li>We will promptly notify affected users as required by applicable laws</li>
                        <li>We will work diligently to address and mitigate any security issues</li>
                    </ul>

                    <h2>9. User Responsibilities</h2>
                    <p>To maintain security, we recommend that users:</p>
                    <ul>
                        <li>Use strong, unique passwords for your ValidTrack account</li>
                        <li>Enable two-factor authentication when available</li>
                        <li>Keep your login credentials confidential</li>
                        <li>Log out from shared devices after use</li>
                        <li>Report any suspicious activities to our security team</li>
                    </ul>

                    <h2>10. Security Vulnerability Reporting</h2>
                    <p>
                        If you discover a security vulnerability in our platform, please report it to {{ companyInfo.email }}. We appreciate
                        responsible disclosure and will work with you to address any valid concerns.
                    </p>

                    <h2>11. Contact Information</h2>
                    <p>For any security-related questions or concerns, please contact our security team at {{ companyInfo.email }}.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Additional styling can be added here if needed */
</style>
