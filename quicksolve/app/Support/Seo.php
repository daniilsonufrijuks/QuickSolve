<?php

namespace App\Support;

use Inertia\Inertia;
use Inertia\Response;

class Seo
{
    /**
     * @param  array<string, mixed>  $props
     */
    public static function page(string $component, array $props, string $title, string $description, ?string $path = null): Response
    {
        $canonical = url($path ?? '/');

        return Inertia::render($component, $props)->withViewData([
            'metaTitle' => $title.' — '.config('app.name'),
            'metaDescription' => $description,
            'canonical' => $canonical,
        ]);
    }
}
