<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class Seo
{
    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, mixed>  $options
     */
    public static function page(string $component, array $props, string $title, string $description, ?string $path = null, array $options = []): Response
    {
        $seo = self::payload($title, $description, $path, $options);

        return Inertia::render($component, array_merge($props, ['seo' => $seo]))->withViewData(self::viewData($seo));
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public static function payload(string $title, string $description, ?string $path = null, array $options = []): array
    {
        $app = (string) config('app.name', 'QuickSolve');
        $canonical = url($path ?? '/');
        $documentTitle = ($options['branded'] ?? false) ? $title : $title.' — '.$app;

        return [
            'title' => $documentTitle,
            'description' => self::describe($description),
            'canonical' => $canonical,
            'robots' => $options['robots'] ?? 'index,follow',
            'ogType' => $options['ogType'] ?? 'website',
            'image' => $options['image'] ?? url('/images/og.svg'),
            'imageAlt' => $options['imageAlt'] ?? $app.' — Small tools. Smarter business.',
            'jsonLd' => $options['jsonLd'] ?? self::graph([
                self::organization(),
                self::website(),
                self::webPage($documentTitle, $description, $path ?? '/'),
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $seo
     * @return array<string, mixed>
     */
    public static function viewData(array $seo): array
    {
        return [
            'metaTitle' => $seo['title'],
            'metaDescription' => $seo['description'],
            'canonical' => $seo['canonical'],
            'robots' => $seo['robots'],
            'ogType' => $seo['ogType'],
            'ogImage' => $seo['image'],
            'ogImageAlt' => $seo['imageAlt'],
            'jsonLd' => $seo['jsonLd'],
        ];
    }

    public static function isPrivate(Request $request): bool
    {
        return $request->is([
            'dashboard',
            'dashboard/*',
            'admin',
            'admin/*',
            'settings',
            'settings/*',
            'login',
            'register',
            'forgot-password',
            'reset-password',
            'reset-password/*',
            'verify-email',
            'verify-email/*',
            'confirm-password',
            'email/verify',
            'email/verify/*',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function privateViewData(Request $request): array
    {
        $app = (string) config('app.name', 'QuickSolve');

        return [
            'metaTitle' => $app,
            'metaDescription' => 'Sign in to manage your QuickSolve account, downloads, and billing.',
            'canonical' => $request->url(),
            'robots' => 'noindex,nofollow',
            'ogType' => 'website',
            'ogImage' => url('/images/og.svg'),
            'ogImageAlt' => $app,
            'jsonLd' => null,
        ];
    }

    public static function describe(?string $text, int $max = 155): string
    {
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) $text)) ?? '');

        if ($plain === '') {
            return 'Small tools for invoices, profit, and practical business downloads.';
        }

        return Str::limit($plain, $max, '…');
    }

    /**
     * @param  list<array<string, mixed>>  $nodes
     * @return array<string, mixed>
     */
    public static function graph(array $nodes): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => array_values($nodes),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function organization(): array
    {
        $app = (string) config('app.name', 'QuickSolve');

        return [
            '@type' => 'Organization',
            '@id' => url('/').'#organization',
            'name' => $app,
            'url' => url('/'),
            'logo' => url('/logo.svg'),
            'slogan' => 'Small tools. Smarter business.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => url('/').'#website',
            'url' => url('/'),
            'name' => (string) config('app.name', 'QuickSolve'),
            'publisher' => ['@id' => url('/').'#organization'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => url('/tools').'?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function webPage(string $name, string $description, string $path, string $type = 'WebPage'): array
    {
        return [
            '@type' => $type,
            '@id' => url($path).'#webpage',
            'url' => url($path),
            'name' => $name,
            'description' => self::describe($description),
            'isPartOf' => ['@id' => url('/').'#website'],
            'about' => ['@id' => url('/').'#organization'],
        ];
    }

    /**
     * @param  list<array{q?: string, a?: string}|array{0: string, 1: string}>  $faqs
     * @return array<string, mixed>|null
     */
    public static function faqPage(array $faqs): ?array
    {
        $entities = [];

        foreach ($faqs as $faq) {
            $question = is_array($faq) ? (string) ($faq['q'] ?? $faq[0] ?? '') : '';
            $answer = is_array($faq) ? (string) ($faq['a'] ?? $faq[1] ?? '') : '';

            if ($question === '' || $answer === '') {
                continue;
            }

            $entities[] = [
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $answer,
                ],
            ];
        }

        if ($entities === []) {
            return null;
        }

        return [
            '@type' => 'FAQPage',
            'mainEntity' => $entities,
        ];
    }
}
