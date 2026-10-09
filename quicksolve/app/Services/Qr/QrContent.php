<?php

namespace App\Services\Qr;

use InvalidArgumentException;

class QrContent
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function payload(array $input): string
    {
        $type = $input['type'] ?? 'text';

        return match ($type) {
            'url' => $this->url((string) ($input['url'] ?? '')),
            'wifi' => $this->wifi($input),
            'contact' => $this->contact($input),
            'text' => $this->text((string) ($input['text'] ?? '')),
            default => throw new InvalidArgumentException('Choose a URL, Wi-Fi network, contact, or plain text.'),
        };
    }

    private function url(string $url): string
    {
        $url = trim($url);

        if (! preg_match('/\Ahttps?:\/\/[^\s]+\z/i', $url)) {
            throw new InvalidArgumentException('Enter a URL that starts with http:// or https://.');
        }

        return $url;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function wifi(array $input): string
    {
        $security = strtoupper((string) ($input['wifi_security'] ?? 'WPA'));

        if (! in_array($security, ['WPA', 'WEP', 'NOPASS'], true)) {
            throw new InvalidArgumentException('Choose WPA, WEP, or no password.');
        }

        $ssid = $this->escapeWifi((string) ($input['wifi_ssid'] ?? ''));

        if ($ssid === '') {
            throw new InvalidArgumentException('Enter the Wi-Fi network name.');
        }

        $password = $security === 'NOPASS' ? '' : $this->escapeWifi((string) ($input['wifi_password'] ?? ''));
        $hidden = filter_var($input['wifi_hidden'] ?? false, FILTER_VALIDATE_BOOL) ? 'true' : 'false';

        return 'WIFI:T:'.$security.';S:'.$ssid.';P:'.$password.';H:'.$hidden.';;';
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function contact(array $input): string
    {
        $first = $this->vcard((string) ($input['first_name'] ?? ''));
        $last = $this->vcard((string) ($input['last_name'] ?? ''));
        $phone = $this->vcard((string) ($input['phone'] ?? ''));
        $email = $this->vcard((string) ($input['email'] ?? ''));
        $organization = $this->vcard((string) ($input['organization'] ?? ''));
        $full = trim($first.' '.$last);

        if ($full === '') {
            throw new InvalidArgumentException('Enter a contact name.');
        }

        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'N:'.$last.';'.$first,
            'FN:'.$full,
        ];

        if ($organization !== '') {
            $lines[] = 'ORG:'.$organization;
        }

        if ($phone !== '') {
            $lines[] = 'TEL:'.$phone;
        }

        if ($email !== '') {
            $lines[] = 'EMAIL:'.$email;
        }

        $lines[] = 'END:VCARD';

        return implode("\n", $lines);
    }

    private function text(string $text): string
    {
        $text = trim($text);

        if ($text === '') {
            throw new InvalidArgumentException('Enter the text to encode.');
        }

        if (mb_strlen($text) > 500) {
            throw new InvalidArgumentException('Keep the text to 500 characters so the code stays reliable.');
        }

        return $text;
    }

    private function escapeWifi(string $value): string
    {
        return str_replace(['\\', ';', ',', ':'], ['\\\\', '\\;', '\\,', '\\:'], trim($value));
    }

    private function vcard(string $value): string
    {
        return str_replace(["\r", "\n", ';'], ' ', trim($value));
    }
}
