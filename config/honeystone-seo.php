<?php

declare(strict_types=1);

use Honeystone\Seo\Generators;

return [

    'generators' => [
        Generators\MetaGenerator::class => [
            'title' => env('APP_NAME', 'ValidTrack'),
            'titleTemplate' => '{title} - '.env('APP_NAME', 'ValidTrack'),
            'description' => 'Track, manage, and ensure compliance for documents of employees, vendors, contractors, and any entity. Never miss a document expiry deadline with automated reminders.',
            'keywords' => [
                'document tracking',
                'compliance management',
                'document expiry reminders',
                'employee document management',
                'vendor compliance',
                'contractor documents',
                'asset tracking',
                'regulatory compliance',
                'document management system',
                'expiry notifications',
            ],
            'canonicalEnabled' => true,
            'canonical' => null, // null to use current url
            'robots' => ['index', 'follow'],
            'custom' => [
                // [
                //     'google-site-verification' => 'xxx',
                // ],
            ],
        ],
        Generators\TwitterGenerator::class => [
            'enabled' => true,
            'site' => '@ValidTrack', // @twitterUsername
            'card' => 'summary_large_image',
            'creator' => '@Tech360Systems',
            'creatorId' => '',
            'title' => '',
            'description' => '',
            'image' => '',
            'imageAlt' => 'ValidTrack - Document Compliance Management System',
        ],
        Generators\OpenGraphGenerator::class => [
            'enabled' => true,
            'site' => env('APP_NAME', 'ValidTrack'),
            'type' => 'website',
            'title' => '',
            'description' => '',
            'images' => [],
            'audio' => [],
            'videos' => [],
            'determiner' => '',
            'url' => null, // null to use current url
            'locale' => 'en_US',
            'alternateLocales' => [],
            'custom' => [],
        ],
        Generators\JsonLdGenerator::class => [
            'enabled' => true,
            'pretty' => env('APP_DEBUG'),
            'type' => 'WebPage',
            'name' => '',
            'description' => '',
            'images' => [],
            'url' => null, // null to use current url
            'custom' => [],

            // determines if the configured json-ld is automatically placed on the graph
            'place-on-graph' => true,
        ],
    ],

    'sync' => [
        'url-canonical' => true,
        'keywords-tags' => false,
    ],
];
