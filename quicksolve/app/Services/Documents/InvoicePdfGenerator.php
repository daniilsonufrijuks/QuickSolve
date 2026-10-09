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
     */
    public function download(array $input): Response
    {
        $calculated = $this->calculator->calculate($input);
        $filename = 'invoice-'.str($input['invoice_number'])->slug().'.pdf';

        return Pdf::loadView('invoices.pdf', [
            'invoice' => $input,
            'calculated' => $calculated,
        ])->download($filename);
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $calculated
     */
    public function store(array $input, array $calculated, string $path): void
    {
        $binary = Pdf::loadView('invoices.pdf', [
            'invoice' => $input,
            'calculated' => $calculated,
        ])->output();

        Storage::disk('local')->put($path, $binary);
    }
}
