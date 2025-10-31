<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigureSeo
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Configure SEO based on the current route
        $this->configurePageSeo($request);

        return $next($request);
    }

    /**
     * Configure SEO for specific pages
     */
    protected function configurePageSeo(Request $request): void
    {
        $route = $request->route();
        
        if (!$route) {
            return;
        }

        $routeName = $route->getName();

        match ($routeName) {
            'home' => $this->configureHomeSeo(),
            'legal' => $this->configureLegalSeo(),
            'acceptable-use' => $this->configureAcceptableUseSeo(),
            'security' => $this->configureSecuritySeo(),
            default => null,
        };
    }

    /**
     * Configure SEO for homepage
     */
    protected function configureHomeSeo(): void
    {
        seo()
            ->title('ValidTrack - Never Miss a Compliance Deadline Again', template: false)
            ->description('Track, manage, and ensure compliance for documents of employees, vendors, contractors, and any entity. Automated expiry reminders, real-time tracking, and comprehensive reporting.')
            ->keywords(
                'document compliance',
                'document tracking system',
                'compliance management software',
                'employee document tracking',
                'vendor compliance management',
                'contractor document management',
                'asset compliance tracking',
                'document expiry reminders',
                'regulatory compliance tool',
                'automated compliance notifications',
                'document management system',
                'compliance dashboard',
                'audit trail software',
                'certificate tracking',
                'license management'
            )
            ->images(url('/logo-blue.svg'))
            ->twitterCard('summary_large_image')
            ->openGraphType('website')
            ->jsonLdType('WebPage')
            ->jsonLdGraph()
                ->organization()
                    ->name('Tech360 Systems')
                    ->legalName('Tech360 Systems')
                    ->url(url('/'))
                    ->logo(url('/logo-blue.svg'))
                    ->setProperty('contactPoint', [
                        '@type' => 'ContactPoint',
                        'telephone' => '+254769287724',
                        'email' => 'support@tech360.systems',
                        'contactType' => 'Customer Support',
                        'areaServed' => 'Worldwide',
                        'availableLanguage' => ['English'],
                    ]);

        seo()->jsonLdGraph()
            ->softwareApplication()
                ->name('ValidTrack')
                ->applicationCategory('BusinessApplication')
                ->operatingSystem('Web')
                ->setProperty('offers', [
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'USD',
                    'description' => 'Free trial available with flexible pricing plans',
                ]);
    }

    /**
     * Configure SEO for legal page
     */
    protected function configureLegalSeo(): void
    {
        seo()
            ->title('Legal Documents - Terms, Privacy & Refund Policy')
            ->description('Read ValidTrack\'s Terms & Conditions, Privacy Policy, and Refund Policy. Understand how we protect your data and handle compliance documentation.')
            ->keywords(
                'ValidTrack terms and conditions',
                'ValidTrack privacy policy',
                'ValidTrack refund policy',
                'document compliance legal',
                'data protection policy',
                'GDPR compliance',
                'legal documentation'
            )
            ->openGraphType('website')
            ->jsonLdType('WebPage')
            ->jsonLdName('Legal Documents')
            ->jsonLdDescription('Terms & Conditions, Privacy Policy, and Refund Policy');
    }

    /**
     * Configure SEO for acceptable use page
     */
    protected function configureAcceptableUseSeo(): void
    {
        seo()
            ->title('Acceptable Use Policy - ValidTrack')
            ->description('Learn about ValidTrack\'s Acceptable Use Policy. Guidelines for using our document compliance and tracking platform responsibly and legally.')
            ->keywords(
                'acceptable use policy',
                'ValidTrack usage guidelines',
                'platform terms of use',
                'compliance software rules',
                'prohibited activities',
                'user responsibilities'
            )
            ->openGraphType('article')
            ->jsonLdType('WebPage')
            ->jsonLdName('Acceptable Use Policy')
            ->jsonLdDescription('Guidelines for using ValidTrack responsibly');
    }

    /**
     * Configure SEO for security page
     */
    protected function configureSecuritySeo(): void
    {
        seo()
            ->title('Security Policy - How ValidTrack Protects Your Data')
            ->description('Learn about ValidTrack\'s comprehensive security measures: SSL encryption, AES-256 data encryption, SOC 2 compliance, and 24/7 monitoring to protect your compliance documents.')
            ->keywords(
                'ValidTrack security',
                'document security policy',
                'data encryption',
                'SSL encryption',
                'AES-256 encryption',
                'SOC 2 compliance',
                'GDPR compliance',
                'secure document storage',
                'data protection measures',
                'security compliance',
                'PCI DSS compliance',
                'two-factor authentication'
            )
            ->openGraphType('article')
            ->jsonLdType('WebPage')
            ->jsonLdName('Security Policy')
            ->jsonLdDescription('Comprehensive security measures to protect your data');
    }
}
