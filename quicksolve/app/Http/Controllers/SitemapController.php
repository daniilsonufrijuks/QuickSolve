<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\Tool;

class SitemapController extends Controller
{
    public function sitemap()
    {
        $urls = [
            ['loc' => url('/'), 'changefreq' => 'weekly'],
            ['loc' => url('/tools'), 'changefreq' => 'weekly'],
            ['loc' => url('/templates'), 'changefreq' => 'weekly'],
            ['loc' => url('/pricing'), 'changefreq' => 'monthly'],
            ['loc' => url('/about'), 'changefreq' => 'monthly'],
            ['loc' => url('/contact'), 'changefreq' => 'yearly'],
            ['loc' => url('/privacy'), 'changefreq' => 'yearly'],
            ['loc' => url('/terms'), 'changefreq' => 'yearly'],
        ];

        foreach (Tool::query()->published()->pluck('slug') as $slug) {
            $urls[] = ['loc' => url('/tools/'.$slug), 'changefreq' => 'monthly'];
        }

        foreach (Template::query()->published()->pluck('slug') as $slug) {
            $urls[] = ['loc' => url('/templates/'.$slug), 'changefreq' => 'monthly'];
        }

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }

    public function robots()
    {
        $body = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /dashboard',
            'Disallow: /admin',
            'Disallow: /api/',
            'Disallow: /settings',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ]);

        return response($body, 200)->header('Content-Type', 'text/plain');
    }
}
