<?php

namespace App\Services\Descriptions;

class DemoProductDescriptionProvider implements ProductDescriptionProvider
{
    public function generate(array $input): array
    {
        $name = trim((string) $input['product_name']);
        $category = trim((string) $input['product_category']);
        $features = trim((string) $input['product_features']);
        $audience = trim((string) $input['target_audience']);
        $tone = (string) $input['tone'];
        $includeCta = (bool) ($input['include_call_to_action'] ?? false);

        $featureLines = collect(preg_split('/\r\n|\r|\n/', $features) ?: [])
            ->map(fn (string $line) => trim($line, " \t-•"))
            ->filter()
            ->values();

        if ($featureLines->isEmpty()) {
            $featureLines = collect([$features]);
        }

        $lead = match ($tone) {
            'friendly' => "Meet {$name}, a {$category} made for {$audience}.",
            'playful' => "{$name} is the {$category} {$audience} actually enjoy using.",
            'luxury' => "{$name} is a considered {$category} for {$audience}.",
            'direct' => "{$name} helps {$audience} with a practical {$category}.",
            default => "{$name} is a {$category} designed for {$audience}.",
        };

        $bullets = $featureLines->take(8)->all();
        $short = $lead.' '.implode(' ', array_slice($bullets, 0, 2));
        $full = $lead."\n\n".implode("\n", array_map(fn (string $bullet) => '- '.$bullet, $bullets));

        if ($includeCta) {
            $full .= "\n\nAdd it to your catalog and review the details before you publish.";
        }

        return [
            'mode' => 'demo',
            'title' => $name,
            'short_description' => $short,
            'full_description' => $full,
            'bullets' => $bullets,
            'call_to_action' => $includeCta ? 'Review the details and add it to your store.' : null,
            'notice' => 'Demo mode: no DeepSeek API key is configured. This text was assembled from your inputs on the server. It is not a live DeepSeek response.',
        ];
    }
}
