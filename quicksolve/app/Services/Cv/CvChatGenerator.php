<?php

namespace App\Services\Cv;

use App\Exceptions\GenerationLimitExceededException;
use App\Models\UsageEvent;
use App\Models\User;
use App\Services\Billing\PlanResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CvChatGenerator
{
    public function __construct(
        private readonly PlanResolver $plans,
        private readonly DemoCvChatProvider $demo,
        private readonly DeepSeekCvChatProvider $deepSeek,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function reply(array $input, ?User $user, Request $request): array
    {
        $this->enforceLimit($user, $request);

        $result = $this->provider()->reply($input);
        $result['patch'] = $this->sanitizePatch(is_array($result['patch'] ?? null) ? $result['patch'] : null);
        $result['reply'] = Str::limit(trim((string) ($result['reply'] ?? '')), 1200, '');

        UsageEvent::query()->create([
            'user_id' => $user?->id,
            'guest_hash' => $user ? null : $this->plans->guestHash($request),
            'tool_slug' => 'cv-builder',
            'usage_type' => 'generated',
            'metadata' => [
                'mode' => $result['mode'],
            ],
        ]);

        $result['usage'] = $this->plans->generatorUsage($user, $request);

        return $result;
    }

    public function provider(): CvChatProvider
    {
        $key = config('quicksolve.ai.api_key');

        if (! is_string($key) || $key === '') {
            return $this->demo;
        }

        return $this->deepSeek;
    }

    public function enforceLimit(?User $user, Request $request): void
    {
        $usage = $this->plans->generatorUsage($user, $request);

        if ($usage['used'] >= $usage['limit']) {
            throw new GenerationLimitExceededException($usage['limit'], $usage['window']);
        }
    }

    /**
     * @param  array<string, mixed>|null  $patch
     * @return array<string, mixed>|null
     */
    private function sanitizePatch(?array $patch): ?array
    {
        if ($patch === null || $patch === []) {
            return null;
        }

        $clean = [];

        foreach (['headline' => 160, 'summary' => 2000] as $field => $max) {
            if (isset($patch[$field]) && is_string($patch[$field])) {
                $clean[$field] = Str::limit(trim($patch[$field]), $max, '');
            }
        }

        if (isset($patch['skills']) && is_array($patch['skills'])) {
            $clean['skills'] = collect($patch['skills'])
                ->filter(fn ($skill) => is_string($skill))
                ->map(fn ($skill) => Str::limit(trim($skill), 80, ''))
                ->filter()
                ->take(30)
                ->values()
                ->all();
        }

        if (isset($patch['experience']) && is_array($patch['experience'])) {
            $clean['experience'] = collect($patch['experience'])
                ->filter(fn ($job) => is_array($job) && is_string($job['role'] ?? null) && trim($job['role']) !== '')
                ->map(fn ($job) => [
                    'role' => Str::limit(trim((string) $job['role']), 160, ''),
                    'company' => Str::limit(trim((string) ($job['company'] ?? '')), 160, ''),
                    'dates' => Str::limit(trim((string) ($job['dates'] ?? '')), 80, ''),
                    'details' => Str::limit(trim((string) ($job['details'] ?? '')), 1500, ''),
                ])
                ->take(12)
                ->values()
                ->all();
        }

        if (isset($patch['education']) && is_array($patch['education'])) {
            $clean['education'] = collect($patch['education'])
                ->filter(fn ($item) => is_array($item) && is_string($item['school'] ?? null) && trim($item['school']) !== '')
                ->map(fn ($item) => [
                    'school' => Str::limit(trim((string) $item['school']), 160, ''),
                    'credential' => Str::limit(trim((string) ($item['credential'] ?? '')), 160, ''),
                    'dates' => Str::limit(trim((string) ($item['dates'] ?? '')), 80, ''),
                ])
                ->take(8)
                ->values()
                ->all();
        }

        return $clean === [] ? null : $clean;
    }
}
