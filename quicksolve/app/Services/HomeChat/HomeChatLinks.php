<?php

namespace App\Services\HomeChat;

class HomeChatLinks
{
    /**
     * @var array<string, string>
     */
    public const ALLOWED = [
        '/tools' => 'Browse tools',
        '/tools/invoice-generator' => 'Invoice generator',
        '/tools/profit-margin-calculator' => 'Profit margin calculator',
        '/tools/payment-landing-date' => 'Payment landing date',
        '/tools/scope-change-note' => 'Scope change note',
        '/tools/product-description-generator' => 'Product descriptions',
        '/tools/email-templates' => 'Email templates',
        '/tools/cv-builder' => 'CV builder',
        '/templates' => 'Templates',
        '/pricing' => 'Pricing',
        '/contact' => 'Contact',
        '/register' => 'Create account',
        '/login' => 'Log in',
    ];

    /**
     * @param  mixed  $links
     * @return list<array{label: string, href: string}>
     */
    public static function sanitize(mixed $links): array
    {
        if (! is_array($links)) {
            return [];
        }

        $clean = [];

        foreach ($links as $link) {
            if (! is_array($link)) {
                continue;
            }

            $href = is_string($link['href'] ?? null) ? $link['href'] : '';
            $path = parse_url($href, PHP_URL_PATH);

            if (! is_string($path) || ! array_key_exists($path, self::ALLOWED)) {
                continue;
            }

            $label = is_string($link['label'] ?? null) && $link['label'] !== ''
                ? $link['label']
                : self::ALLOWED[$path];

            $clean[] = [
                'label' => mb_substr($label, 0, 80),
                'href' => $path,
            ];

            if (count($clean) === 4) {
                break;
            }
        }

        return $clean;
    }
}
