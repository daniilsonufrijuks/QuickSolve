<?php

namespace App\Support;

class ToolCategoryOrder
{
    /**
     * @var list<string>
     */
    public const SLUGS = [
        'calculators',
        'e-commerce',
        'business-productivity',
        'text-and-content',
        'marketing-and-seo',
        'converters',
        'developer-tools',
        'everyday',
    ];

    public static function rank(string $slug): int
    {
        $index = array_search($slug, self::SLUGS, true);

        return $index === false ? count(self::SLUGS) : $index;
    }
}
