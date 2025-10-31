<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Generate robots.txt file
     */
    public function index(): Response
    {
        $robots = "User-agent: *\n";
        $robots .= "Allow: /\n";
        $robots .= "Disallow: /dashboard\n";
        $robots .= "Disallow: /admin\n";
        $robots .= "Disallow: /api/\n";
        $robots .= "\n";
        $robots .= "Sitemap: " . url('/sitemap.xml') . "\n";

        return response($robots)
            ->header('Content-Type', 'text/plain');
    }
}
