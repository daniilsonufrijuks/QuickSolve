<?php

namespace App\Services\Documents;

use Illuminate\Support\Str;

class JsonResumeMapper
{
    /**
     * @param  array<string, mixed>  $resume
     * @return array<string, mixed>
     */
    public function toCv(array $resume, string $template = 'classic'): array
    {
        $basics = is_array($resume['basics'] ?? null) ? $resume['basics'] : [];
        $location = is_array($basics['location'] ?? null) ? $basics['location'] : [];
        $locationLine = collect([
            $this->string($location['city'] ?? null),
            $this->string($location['region'] ?? null),
            $this->string($location['countryCode'] ?? null),
        ])->filter()->implode(', ');

        return [
            'template' => $template,
            'full_name' => $this->clip($this->string($basics['name'] ?? null), 160),
            'headline' => $this->clip($this->string($basics['label'] ?? null), 160),
            'email' => $this->clip($this->string($basics['email'] ?? null), 160),
            'phone' => $this->clip($this->string($basics['phone'] ?? null), 60),
            'location' => $this->clip($locationLine, 120),
            'website' => $this->clip($this->string($basics['url'] ?? null), 180),
            'summary' => $this->clip($this->string($basics['summary'] ?? null), 2000),
            'skills' => $this->skills($resume['skills'] ?? null),
            'experience' => $this->work($resume['work'] ?? null),
            'education' => $this->education($resume['education'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>  $cv
     * @return array<string, mixed>
     */
    public function fromCv(array $cv): array
    {
        $basics = array_filter([
            'name' => $this->string($cv['full_name'] ?? null),
            'label' => $this->string($cv['headline'] ?? null),
            'email' => $this->string($cv['email'] ?? null),
            'phone' => $this->string($cv['phone'] ?? null),
            'url' => $this->absoluteUrl($this->string($cv['website'] ?? null)),
            'summary' => $this->string($cv['summary'] ?? null),
            'location' => array_filter([
                'city' => $this->string($cv['location'] ?? null),
            ]),
        ], fn ($value) => $value !== '' && $value !== []);

        return [
            '$schema' => 'https://raw.githubusercontent.com/jsonresume/resume-schema/v1.0.0/schema.json',
            'basics' => $basics,
            'work' => $this->exportWork($cv['experience'] ?? []),
            'education' => $this->exportEducation($cv['education'] ?? []),
            'skills' => $this->exportSkills($cv['skills'] ?? []),
        ];
    }

    /**
     * @return list<string>
     */
    private function skills(mixed $skills): array
    {
        $names = collect(is_array($skills) ? $skills : [])
            ->flatMap(function ($skill) {
                if (is_string($skill)) {
                    return [$skill];
                }

                if (! is_array($skill)) {
                    return [];
                }

                return array_merge(
                    [$this->string($skill['name'] ?? null)],
                    is_array($skill['keywords'] ?? null) ? $skill['keywords'] : [],
                );
            })
            ->map(fn ($name) => $this->clip($this->string($name), 80))
            ->filter()
            ->unique()
            ->take(30)
            ->values()
            ->all();

        return $names;
    }

    /**
     * @return list<array{role: string, company: string, dates: string, details: string}>
     */
    private function work(mixed $work): array
    {
        $jobs = [];

        foreach (array_slice(is_array($work) ? $work : [], 0, 12) as $job) {
            if (! is_array($job)) {
                continue;
            }

            $role = $this->clip($this->string($job['position'] ?? null), 160);

            if ($role === '') {
                continue;
            }

            $highlights = collect(is_array($job['highlights'] ?? null) ? $job['highlights'] : [])
                ->map(fn ($line) => $this->string($line))
                ->filter();
            $details = collect([$this->string($job['summary'] ?? null), ...$highlights])
                ->filter()
                ->implode("\n");

            $jobs[] = [
                'role' => $role,
                'company' => $this->clip($this->string($job['name'] ?? $job['company'] ?? null), 160),
                'dates' => $this->clip($this->formatDates($job['startDate'] ?? null, $job['endDate'] ?? null), 80),
                'details' => $this->clip($details, 1500),
            ];
        }

        return $jobs;
    }

    /**
     * @return list<array{school: string, credential: string, dates: string}>
     */
    private function education(mixed $education): array
    {
        $items = [];

        foreach (array_slice(is_array($education) ? $education : [], 0, 8) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $school = $this->clip($this->string($item['institution'] ?? $item['school'] ?? null), 160);

            if ($school === '') {
                continue;
            }

            $credential = collect([
                $this->string($item['studyType'] ?? null),
                $this->string($item['area'] ?? $item['credential'] ?? null),
            ])->filter()->implode(' ');

            $items[] = [
                'school' => $school,
                'credential' => $this->clip($credential, 160),
                'dates' => $this->clip($this->formatDates($item['startDate'] ?? null, $item['endDate'] ?? null), 80),
            ];
        }

        return $items;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function exportWork(mixed $experience): array
    {
        $jobs = [];

        foreach (is_array($experience) ? $experience : [] as $job) {
            if (! is_array($job)) {
                continue;
            }

            $role = $this->string($job['role'] ?? null);

            if ($role === '') {
                continue;
            }

            [$start, $end] = $this->parseDates($this->string($job['dates'] ?? null));
            $exported = array_filter([
                'name' => $this->string($job['company'] ?? null),
                'position' => $role,
                'startDate' => $start,
                'endDate' => $end,
                'summary' => $this->string($job['details'] ?? null),
            ]);

            $jobs[] = $exported;
        }

        return $jobs;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function exportEducation(mixed $education): array
    {
        $items = [];

        foreach (is_array($education) ? $education : [] as $item) {
            if (! is_array($item)) {
                continue;
            }

            $school = $this->string($item['school'] ?? null);

            if ($school === '') {
                continue;
            }

            [$start, $end] = $this->parseDates($this->string($item['dates'] ?? null));
            $exported = array_filter([
                'institution' => $school,
                'area' => $this->string($item['credential'] ?? null),
                'startDate' => $start,
                'endDate' => $end,
            ]);

            $items[] = $exported;
        }

        return $items;
    }

    /**
     * @return list<array{name: string}>
     */
    private function exportSkills(mixed $skills): array
    {
        return collect(is_array($skills) ? $skills : [])
            ->map(fn ($skill) => $this->string($skill))
            ->filter()
            ->map(fn ($name) => ['name' => $name])
            ->values()
            ->all();
    }

    private function formatDates(mixed $start, mixed $end): string
    {
        $startDate = $this->string($start);
        $endDate = $this->string($end);

        if ($startDate === '' && $endDate === '') {
            return '';
        }

        if ($endDate === '') {
            return $startDate.' — present';
        }

        if ($startDate === '') {
            return $endDate;
        }

        return $startDate.' — '.$endDate;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function parseDates(string $dates): array
    {
        preg_match_all('/\d{4}(?:-\d{2}(?:-\d{2})?)?/', $dates, $matches);
        $found = $matches[0] ?? [];

        return [$found[0] ?? '', $found[1] ?? ''];
    }

    private function absoluteUrl(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $value) === 1) {
            return $value;
        }

        return 'https://'.$value;
    }

    private function string(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    private function clip(string $value, int $max): string
    {
        return Str::limit($value, $max, '');
    }
}
