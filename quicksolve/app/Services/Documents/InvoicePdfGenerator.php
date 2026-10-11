<?php

namespace App\Services\Documents;

use App\Services\Calculators\InvoiceCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class InvoicePdfGenerator
{
    public function __construct(private readonly InvoiceCalculator $calculator) {}

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $options
     */
    public function download(array $input, array $options = []): Response
    {
        $calculated = $this->calculator->calculate($input);
        $kind = $options['kind'] ?? 'invoice';
        $number = (string) ($input['quote_number'] ?? $input['invoice_number'] ?? $kind);
        $filename = $kind.'-'.str($number)->slug().'.pdf';

        return Pdf::loadView('invoices.pdf', $this->viewData($input, $calculated, $options))->download($filename);
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $calculated
     * @param  array<string, mixed>  $options
     */
    public function store(array $input, array $calculated, string $path, array $options = []): void
    {
        $binary = Pdf::loadView('invoices.pdf', $this->viewData($input, $calculated, $options))->output();

        Storage::disk('local')->put($path, $binary);
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $calculated
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function viewData(array $input, array $calculated, array $options): array
    {
        $kind = $options['kind'] ?? 'invoice';

        return [
            'invoice' => $input,
            'calculated' => $calculated,
            'kind' => $kind,
            'heading' => $kind === 'quote' ? 'Quote' : 'Invoice',
            'number' => $input['quote_number'] ?? $input['invoice_number'] ?? '',
            'date_label' => $kind === 'quote' ? 'Valid until' : 'Due',
            'date_value' => $input['valid_until'] ?? $input['due_date'] ?? '',
            'accent' => $options['accent'] ?? '#0f172a',
            'branded' => $options['branded'] ?? true,
            'custom_footer' => $options['custom_footer'] ?? '',
        ];
    }
}
