<?php

namespace App\Services\Tools;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class W3cMarkupChecker
{
    private const HTML_URL = 'https://validator.w3.org/nu/?out=json';

    private const CSS_URL = 'https://jigsaw.w3.org/css-validator/validator';

    /**
     * @return array{valid: bool, checked_by: string, messages: list<array{severity: string, line: int|null, column: int|null, message: string, extract: string|null}>}|null
     */
    public function html(string $source, bool $fragment = false): ?array
    {
        $document = $fragment ? $this->fragmentPrefix().$source.$this->fragmentSuffix() : $source;

        try {
            $response = Http::connectTimeout(5)
                ->timeout(20)
                ->withUserAgent($this->userAgent())
                ->acceptJson()
                ->withBody($document, 'text/html; charset=utf-8')
                ->post(self::HTML_URL);
        } catch (ConnectionException) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $payload = $response->json();

        if (! is_array($payload) || ! array_key_exists('messages', $payload)) {
            return null;
        }

        $messages = $this->htmlMessages($payload, $fragment, $source);

        return [
            'valid' => $this->errorCount($messages) === 0,
            'checked_by' => 'W3C Nu Html Checker',
            'messages' => $messages,
        ];
    }

    /**
     * @return array{valid: bool, checked_by: string, messages: list<array{severity: string, line: int|null, column: int|null, message: string, extract: string|null}>}|null
     */
    public function css(string $source, string $profile = 'css3svg'): ?array
    {
        try {
            $response = Http::connectTimeout(5)
                ->timeout(30)
                ->withUserAgent($this->userAgent())
                ->acceptJson()
                ->asMultipart()
                ->post(self::CSS_URL, [
                    'text' => $source,
                    'output' => 'json',
                    'profile' => $profile,
                    'warning' => '1',
                    'lang' => 'en',
                ]);
        } catch (ConnectionException) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $report = $response->json('cssvalidation');

        if (! is_array($report)) {
            return null;
        }

        $messages = array_merge(
            $this->cssMessages($report['errors'] ?? [], 'error'),
            $this->cssMessages($report['warnings'] ?? [], 'warning'),
        );

        $messages = array_slice($messages, 0, 100);

        return [
            'valid' => $this->errorCount($messages) === 0,
            'checked_by' => 'W3C CSS Validator',
            'messages' => $messages,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{severity: string, line: int|null, column: int|null, message: string, extract: string|null}>
     */
    private function htmlMessages(array $payload, bool $fragment, string $source): array
    {
        $prefixLines = substr_count($this->fragmentPrefix(), "\n");
        $sourceLines = max(1, substr_count($source, "\n") + (str_ends_with($source, "\n") ? 0 : 1));
        $messages = [];

        foreach ($this->rows($payload['messages'] ?? null) as $row) {
            $line = $this->line($row);

            if ($fragment && $line !== null) {
                if ($line <= $prefixLines) {
                    continue;
                }

                $line = $line > $prefixLines + $sourceLines
                    ? $sourceLines
                    : $line - $prefixLines;
            }

            $message = $this->plainText($row['message'] ?? '');

            if ($message === '') {
                continue;
            }

            $messages[] = [
                'severity' => $this->htmlSeverity($row),
                'line' => $line,
                'column' => $this->column($row),
                'message' => $message,
                'extract' => $this->snippet($row['extract'] ?? null),
            ];

            if (count($messages) >= 100) {
                break;
            }
        }

        return $messages;
    }

    /**
     * @return list<array{severity: string, line: int|null, column: int|null, message: string, extract: string|null}>
     */
    private function cssMessages(mixed $rows, string $severity): array
    {
        $messages = [];

        foreach ($this->rows($rows) as $row) {
            $message = $this->plainText($row['message'] ?? '');

            if ($message === '') {
                continue;
            }

            $messages[] = [
                'severity' => $severity,
                'line' => $this->line($row),
                'column' => $this->column($row),
                'message' => $message,
                'extract' => $this->snippet($row['context'] ?? null),
            ];
        }

        return $messages;
    }

    /**
     * @param  list<array{severity: string, line: int|null, column: int|null, message: string, extract: string|null}>  $messages
     */
    private function errorCount(array $messages): int
    {
        return count(array_filter($messages, fn (array $message): bool => $message['severity'] === 'error'));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function rows(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        if (isset($value['message']) || isset($value['line'])) {
            return [$value];
        }

        $rows = [];

        foreach ($value as $item) {
            if (is_array($item)) {
                $rows = array_merge($rows, $this->rows($item));
            }
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function htmlSeverity(array $row): string
    {
        $type = $row['type'] ?? 'error';

        if ($type === 'error' || $type === 'non-document-error') {
            return 'error';
        }

        if (($row['subType'] ?? null) === 'warning') {
            return 'warning';
        }

        return 'info';
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function line(array $row): ?int
    {
        $value = $row['lastLine'] ?? $row['firstLine'] ?? $row['line'] ?? null;

        if (! is_numeric($value)) {
            return null;
        }

        $line = (int) $value;

        return $line > 0 ? $line : null;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function column(array $row): ?int
    {
        $value = $row['lastColumn'] ?? $row['firstColumn'] ?? $row['column'] ?? null;

        if (! is_numeric($value)) {
            return null;
        }

        $column = (int) $value;

        return $column > 0 ? $column : null;
    }

    private function plainText(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        $text = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    private function snippet(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $text = trim(html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($text === '') {
            return null;
        }

        return mb_substr($text, 0, 240);
    }

    private function fragmentPrefix(): string
    {
        return "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n<meta charset=\"utf-8\">\n<title>Fragment</title>\n</head>\n<body>\n";
    }

    private function fragmentSuffix(): string
    {
        return "\n</body>\n</html>\n";
    }

    private function userAgent(): string
    {
        return config('app.name').' markup checker ('.config('app.url').')';
    }
}
