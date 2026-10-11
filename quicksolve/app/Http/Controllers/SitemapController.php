<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\Tool;

class SitemapController extends Controller
{
    public function sitemap()
    {
        $urls = [
            ['loc' => url('/'), 'changefreq' => 'weekly', 'priority' => '1.0'],
            ['loc' => url('/tools'), 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['loc' => url('/templates'), 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['loc' => url('/pricing'), 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['loc' => url('/about'), 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['loc' => url('/contact'), 'changefreq' => 'yearly', 'priority' => '0.4'],
            ['loc' => url('/privacy'), 'changefreq' => 'yearly', 'priority' => '0.3'],
            ['loc' => url('/terms'), 'changefreq' => 'yearly', 'priority' => '0.3'],
        ];

        foreach (Tool::query()->published()->orderBy('slug')->get(['slug', 'updated_at']) as $tool) {
            $urls[] = [
                'loc' => url('/tools/'.$tool->slug),
                'changefreq' => 'monthly',
                'priority' => '0.8',
                'lastmod' => $tool->updated_at?->toAtomString(),
            ];
        }

        foreach (Template::query()->published()->orderBy('slug')->get(['slug', 'updated_at']) as $template) {
            $urls[] = [
                'loc' => url('/templates/'.$template->slug),
                'changefreq' => 'monthly',
                'priority' => '0.7',
                'lastmod' => $template->updated_at?->toAtomString(),
            ];
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
            'Disallow: /stripe',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ]);

        return response($body, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
