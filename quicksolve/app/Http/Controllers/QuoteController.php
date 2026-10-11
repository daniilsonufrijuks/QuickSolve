<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Http\Requests\StoreQuoteRequest;
use App\Models\GeneratedDocument;
use App\Services\Billing\PlanResolver;
use App\Services\Calculators\InvoiceCalculator;
use App\Services\Documents\DocumentBranding;
use App\Services\Documents\InvoicePdfGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class QuoteController extends Controller
{
    public function pdf(StoreQuoteRequest $request, InvoicePdfGenerator $pdf, DocumentBranding $branding, PlanResolver $plans): Response
    {
        if (! $plans->allowsLongDescriptions($request->user())) {
            $plans->consumePremiumTrial($request->user(), $request, 'quote-builder');
        }

        return $pdf->download($this->documentInput($request), $branding->fromRequest($request, 'quote'));
    }

    public function store(StoreQuoteRequest $request, InvoiceCalculator $calculator, InvoicePdfGenerator $pdf, DocumentBranding $branding, PlanResolver $plans): RedirectResponse|JsonResponse
    {
        if (! $plans->allowsLongDescriptions($request->user())) {
            $plans->consumePremiumTrial($request->user(), $request, 'quote-builder');
        }

        $input = $this->documentInput($request);
        $options = $branding->fromRequest($request, 'quote');

        try {
            $calculated = $calculator->calculate($input);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $document = GeneratedDocument::query()->create([
            'user_id' => $request->user()->id,
            'document_type' => DocumentType::Quote,
            'title' => 'Quote '.$request->string('quote_number'),
            'structured_data' => [
                'input' => $input,
                'calculated' => collect($calculated)->except(['subtotal_minor', 'tax_minor', 'total_minor'])->all(),
                'branding' => $options,
            ],
        ]);

        $path = 'quotes/'.$document->id.'.pdf';
        $pdf->store($input, $calculated, $path, $options);
        $document->update(['private_file_path' => $path]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['id' => $document->id, 'title' => $document->title], 201);
        }

        return redirect()->route('dashboard.documents')->with('success', 'Quote saved to your account.');
    }

    /**
     * @return array<string, mixed>
     */
    private function documentInput(StoreQuoteRequest $request): array
    {
        $input = $request->safe()->except(['accent_color', 'custom_footer']);
        $input['invoice_number'] = $input['quote_number'];

        return $input;
    }
}
