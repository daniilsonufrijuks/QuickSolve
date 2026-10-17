<?php

namespace App\Services\Documents;

use Illuminate\Validation\Rule;

class CvThemeCatalog
{
    public const CUSTOM_ID = 'custom';

    /**
     * @var list<string>
     */
    public const CLASSIC_IDS = [
        'classic',
        'modern',
        'compact',
        'executive',
        'timeline',
        'creative',
        'split',
        'banner',
        'mosaic',
        'atlantic',
        'portrait',
        'mono',
        'academic',
        'sand',
        'rose',
        'slate',
        'forest',
    ];

    /**
     * @var list<string>
     */
    public const LAYOUTS = ['sidebar_left', 'sidebar_right', 'header_band', 'stacked', 'photo_top', 'corner_frame', 'split_panel', 'centered_portrait'];

    /**
     * @var list<string>
     */
    public const TYPES = ['sans', 'serif'];

    /**
     * @var list<string>
     */
    public const NAME_SIZES = ['md', 'lg', 'xl'];

    /**
     * @var list<string>
     */
    public const PHOTOS = ['circle', 'rounded', 'hidden'];

    /**
     * @var list<string>
     */
    public const ALIGNS = ['left', 'center'];

    /**
     * @var list<string>
     */
    public const SKILL_STYLES = ['chips', 'list'];

    /**
     * @return list<string>
     */
    public static function ids(): array
    {
        return array_merge(self::CLASSIC_IDS, self::themedIds(), [self::CUSTOM_ID]);
    }

    /**
     * @return list<string>
     */
    public static function themedIds(): array
    {
        return array_column(self::themed(), 'id');
    }

    /**
     * Color layouts in a popular résumé-maker style. Original QuickSolve designs, not Canva files.
     *
     * @return list<array<string, string>>
     */
    public static function themed(): array
    {
        return [
            self::theme('coral', 'Coral column', 'sidebar_left', '#fff8f4', '#3f2e2a', '#8d6e68', '#e07a5f', '#e07a5f', '#fff7f3', '#c45c40', 'sans', 'lg', 'circle', 'left', 'chips'),
            self::theme('blush', 'Blush band', 'header_band', '#fbf7f4', '#4a3540', '#8e7380', '#c97b84', '#c97b84', '#fff6f7', '#a85a66', 'serif', 'lg', 'circle', 'center', 'chips'),
            self::theme('sky', 'Sky masthead', 'header_band', '#ffffff', '#1e3a5f', '#5b7a9d', '#4c8dda', '#4c8dda', '#f4f8ff', '#2e6db4', 'sans', 'lg', 'circle', 'left', 'chips'),
            self::theme('mustard', 'Mustard rule', 'stacked', '#f6f1e7', '#3f351f', '#8a7a55', '#c9a227', '#ede4cc', '#3f351f', '#a07e12', 'serif', 'xl', 'hidden', 'center', 'list'),
            self::theme('ember', 'Charcoal ember', 'sidebar_left', '#fffdfb', '#1a1a1a', '#6b6560', '#e36414', '#2b2b2b', '#f6f1ec', '#e36414', 'sans', 'lg', 'circle', 'left', 'chips'),
            self::theme('sage', 'Sage column', 'sidebar_right', '#f7f6f1', '#2f3a2e', '#6d7a68', '#6b7f5a', '#6b7f5a', '#f4f6ef', '#4f6142', 'sans', 'lg', 'circle', 'left', 'chips'),
            self::theme('lilac', 'Lilac frame', 'photo_top', '#f7f4fb', '#3d3354', '#7d7394', '#7c6bb0', '#7c6bb0', '#f7f4fb', '#5e4f8a', 'sans', 'lg', 'rounded', 'left', 'chips'),
            self::theme('navygold', 'Navy gold', 'sidebar_left', '#fbf8f1', '#1b365d', '#6b7280', '#c9a227', '#1b365d', '#f7f1de', '#1b365d', 'serif', 'lg', 'circle', 'left', 'chips'),
            self::theme('citrus', 'Citrus frame', 'corner_frame', '#ffffff', '#1f2937', '#6b7280', '#f08a3a', '#1f6a70', '#ecfdf5', '#111827', 'sans', 'xl', 'rounded', 'left', 'list'),
            self::theme('violet', 'Violet split', 'split_panel', '#e3ddf5', '#3d2b6b', '#6d5b9a', '#5a4d8c', '#5a4d8c', '#f4f0fb', '#3d2b6b', 'sans', 'xl', 'circle', 'left', 'list'),
            self::theme('harbor', 'Harbor portrait', 'centered_portrait', '#ffffff', '#1f2937', '#64748b', '#1d4e89', '#1d4e89', '#ffffff', '#1d4e89', 'sans', 'lg', 'circle', 'center', 'chips'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function fallback(): array
    {
        return self::themed()[0];
    }

    /**
     * @param  array<string, mixed>|null  $theme
     * @return array<string, string>|null
     */
    public static function resolve(string $template, ?array $theme): ?array
    {
        foreach (self::themed() as $preset) {
            if ($preset['id'] === $template) {
                return $preset;
            }
        }

        if ($template === self::CUSTOM_ID) {
            return self::sanitize($theme ?? []);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $theme
     * @return array<string, string>
     */
    public static function sanitize(array $theme): array
    {
        $base = self::fallback();

        return [
            'id' => self::CUSTOM_ID,
            'name' => self::clipName($theme['name'] ?? null) ?: 'My template',
            'layout' => self::pick($theme['layout'] ?? null, self::LAYOUTS, $base['layout']),
            'paper' => self::hex($theme['paper'] ?? null, $base['paper']),
            'ink' => self::hex($theme['ink'] ?? null, $base['ink']),
            'muted' => self::hex($theme['muted'] ?? null, $base['muted']),
            'accent' => self::hex($theme['accent'] ?? null, $base['accent']),
            'sidebar' => self::hex($theme['sidebar'] ?? null, $base['sidebar']),
            'sidebar_ink' => self::hex($theme['sidebar_ink'] ?? null, $base['sidebar_ink']),
            'heading' => self::hex($theme['heading'] ?? null, $base['heading']),
            'type' => self::pick($theme['type'] ?? null, self::TYPES, $base['type']),
            'name_size' => self::pick($theme['name_size'] ?? null, self::NAME_SIZES, $base['name_size']),
            'photo' => self::pick($theme['photo'] ?? null, self::PHOTOS, $base['photo']),
            'header_align' => self::pick($theme['header_align'] ?? null, self::ALIGNS, $base['header_align']),
            'skills' => self::pick($theme['skills'] ?? null, self::SKILL_STYLES, $base['skills']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(string $prefix = 'theme'): array
    {
        $hex = ['regex:/^#[0-9A-Fa-f]{6}$/'];

        return [
            $prefix => ['required_if:template,'.self::CUSTOM_ID, 'nullable', 'array'],
            $prefix.'.name' => ['nullable', 'string', 'max:40'],
            $prefix.'.layout' => ['required_with:'.$prefix, Rule::in(self::LAYOUTS)],
            $prefix.'.paper' => ['required_with:'.$prefix, ...$hex],
            $prefix.'.ink' => ['required_with:'.$prefix, ...$hex],
            $prefix.'.muted' => ['required_with:'.$prefix, ...$hex],
            $prefix.'.accent' => ['required_with:'.$prefix, ...$hex],
            $prefix.'.sidebar' => ['required_with:'.$prefix, ...$hex],
            $prefix.'.sidebar_ink' => ['required_with:'.$prefix, ...$hex],
            $prefix.'.heading' => ['required_with:'.$prefix, ...$hex],
            $prefix.'.type' => ['required_with:'.$prefix, Rule::in(self::TYPES)],
            $prefix.'.name_size' => ['required_with:'.$prefix, Rule::in(self::NAME_SIZES)],
            $prefix.'.photo' => ['required_with:'.$prefix, Rule::in(self::PHOTOS)],
            $prefix.'.header_align' => ['required_with:'.$prefix, Rule::in(self::ALIGNS)],
            $prefix.'.skills' => ['required_with:'.$prefix, Rule::in(self::SKILL_STYLES)],
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function theme(
        string $id,
        string $name,
        string $layout,
        string $paper,
        string $ink,
        string $muted,
        string $accent,
        string $sidebar,
        string $sidebarInk,
        string $heading,
        string $type,
        string $nameSize,
        string $photo,
        string $align,
        string $skills,
    ): array {
        return [
            'id' => $id,
            'name' => $name,
            'layout' => $layout,
            'paper' => $paper,
            'ink' => $ink,
            'muted' => $muted,
            'accent' => $accent,
            'sidebar' => $sidebar,
            'sidebar_ink' => $sidebarInk,
            'heading' => $heading,
            'type' => $type,
            'name_size' => $nameSize,
            'photo' => $photo,
            'header_align' => $align,
            'skills' => $skills,
        ];
    }

    /**
     * @param  list<string>  $allowed
     */
    private static function pick(mixed $value, array $allowed, string $fallback): string
    {
        return is_string($value) && in_array($value, $allowed, true) ? $value : $fallback;
    }

    private static function hex(mixed $value, string $fallback): string
    {
        if (! is_string($value) || preg_match('/^#[0-9A-Fa-f]{6}$/', $value) !== 1) {
            return $fallback;
        }

        return strtolower($value);
    }

    private static function clipName(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        return mb_substr(trim(strip_tags($value)), 0, 40);
    }
}
