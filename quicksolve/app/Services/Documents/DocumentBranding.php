<?php

namespace App\Services\Documents;

use App\Models\User;
use App\Services\Billing\PlanResolver;
use Illuminate\Http\Request;

class DocumentBranding
{
    public function __construct(private readonly PlanResolver $plans) {}

    /**
     * Client colour and footer are ignored unless the account plan allows them.
     *
     * @return array{kind?: string, accent: string, branded: bool, custom_footer: string}
     */
    public function fromRequest(Request $request, string $kind = 'invoice'): array
    {
        return $this->forUser(
            $request->user(),
            $request->input('accent_color'),
            $request->input('custom_footer'),
            $kind,
        );
    }

    /**
     * @return array{kind: string, accent: string, branded: bool, custom_footer: string}
     */
    public function forUser(?User $user, mixed $accentColor, mixed $customFooter, string $kind = 'invoice'): array
    {
        $accent = is_string($accentColor) && preg_match('/^#[0-9A-Fa-f]{6}$/', $accentColor) === 1
            ? $accentColor
            : '#0f172a';

        if (! $this->plans->allowsDocumentBranding($user)) {
            $accent = '#0f172a';
        }

        $footer = is_string($customFooter) ? mb_substr(trim($customFooter), 0, 500) : '';

        if (! $this->plans->allowsUnbrandedDocuments($user)) {
            $footer = '';
        }

        return [
            'kind' => $kind,
            'accent' => $accent,
            'branded' => ! $this->plans->allowsUnbrandedDocuments($user),
            'custom_footer' => $footer,
        ];
    }
}
