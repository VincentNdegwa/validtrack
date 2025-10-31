<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate XML sitemap for the application
     */
    public function index(): Response
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Homepage
        $sitemap .= $this->addUrl(url('/'), '2024-06-05', 'daily', '1.0');

        // Legal pages
        $sitemap .= $this->addUrl(route('legal'), '2024-06-05', 'monthly', '0.8');
        $sitemap .= $this->addUrl(route('acceptable-use'), '2024-06-05', 'monthly', '0.8');
        $sitemap .= $this->addUrl(route('security'), '2024-06-05', 'monthly', '0.8');

        $sitemap .= '</urlset>';

        return response($sitemap)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Add URL to sitemap
     */
    private function addUrl(string $loc, string $lastmod, string $changefreq, string $priority): string
    {
        return sprintf(
            '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%s</priority></url>',
            htmlspecialchars($loc, ENT_XML1, 'UTF-8'),
            $lastmod,
            $changefreq,
            $priority
        );
    }
}
