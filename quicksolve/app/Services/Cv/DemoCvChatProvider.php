<?php

namespace App\Services\Cv;

use Illuminate\Support\Str;

class DemoCvChatProvider implements CvChatProvider
{
    public function reply(array $input): array
    {
        $cv = is_array($input['cv'] ?? null) ? $input['cv'] : [];
        $instruction = mb_strtolower(trim((string) $input['instruction']));
        $summary = trim((string) ($cv['summary'] ?? ''));
        $skills = collect($cv['skills'] ?? [])->filter()->values();
        $name = trim((string) ($cv['full_name'] ?? 'this résumé'));

        if (str_contains($instruction, 'summary') && $summary !== '') {
            $tight = $this->tighten($summary);

            return [
                'mode' => 'demo',
                'notice' => 'Demo mode: no DeepSeek API key is configured. This rewrite uses only the summary already in the form. It is not a live DeepSeek response.',
                'reply' => 'Here is a shorter summary from the text already on the form. Review it, then apply it if it still matches the facts.',
                'patch' => ['summary' => $tight],
            ];
        }

        if (str_contains($instruction, 'skill') && $skills->isNotEmpty()) {
            return [
                'mode' => 'demo',
                'notice' => 'Demo mode: no DeepSeek API key is configured. This reply lists skills already in the form. It is not a live DeepSeek response.',
                'reply' => $name.' currently lists: '.$skills->implode(', ').'. Demo mode will not add skills you did not type.',
                'patch' => null,
            ];
        }

        return [
            'mode' => 'demo',
            'notice' => 'Demo mode: no DeepSeek API key is configured. This reply was assembled on the server from the form. It is not a live DeepSeek response.',
            'reply' => 'I can only rephrase fields already in the form. Ask to tighten the summary, or type the missing facts first. Demo mode will not invent jobs, dates, or skills.',
            'patch' => null,
        ];
    }

    private function tighten(string $summary): string
    {
        $plain = trim(preg_replace('/\s+/', ' ', $summary) ?? $summary);

        if (mb_strlen($plain) <= 280) {
            return $plain;
        }

        return Str::limit($plain, 280, '.');
    }
}
