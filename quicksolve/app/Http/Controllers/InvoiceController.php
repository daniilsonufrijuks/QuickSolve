<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Resources\GeneratedDocumentResource;
use App\Models\GeneratedDocument;
use App\Services\Calculators\InvoiceCalculator;
use App\Services\Documents\InvoicePdfGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    public function pdf(StoreInvoiceRequest $request, InvoicePdfGenerator $pdf): Response
    {
        return $pdf->download($request->validated());
    }

    public function store(StoreInvoiceRequest $request, InvoiceCalculator $calculator, InvoicePdfGenerator $pdf): RedirectResponse|JsonResponse
    {
        try {
            $calculated = $calculator->calculate($request->validated());
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $document = GeneratedDocument::query()->create([
            'user_id' => $request->user()->id,
            'document_type' => DocumentType::Invoice,
            'title' => 'Invoice '.$request->string('invoice_number'),
            'structured_data' => [
                'input' => $request->validated(),
                'calculated' => collect($calculated)->except(['subtotal_minor', 'tax_minor', 'total_minor'])->all(),
            ],
        ]);

        $path = 'invoices/'.$document->id.'.pdf';
        $pdf->store($request->validated(), $calculated, $path);
        $document->update(['private_file_path' => $path]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return (new GeneratedDocumentResource($document))->response()->setStatusCode(201);
        }

        return redirect()->route('dashboard.documents')->with('success', 'Invoice saved to your account.');
    }

    public function index(Request $request): JsonResponse
    {
        $documents = GeneratedDocument::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(12);

        return GeneratedDocumentResource::collection($documents)->response();
    }

    public function show(Request $request, GeneratedDocument $invoice): GeneratedDocumentResource
    {
        $this->authorize('view', $invoice);

        return new GeneratedDocumentResource($invoice);
    }

    public function destroy(Request $request, GeneratedDocument $invoice): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $invoice);

        if ($invoice->private_file_path) {
            Storage::disk('local')->delete($invoice->private_file_path);
        }

        $invoice->delete();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Invoice deleted.']);
        }

        return back()->with('success', 'Invoice deleted.');
    }

    public function file(Request $request, GeneratedDocument $invoice): StreamedResponse
    {
        $this->authorize('view', $invoice);
        abort_unless($invoice->private_file_path && Storage::disk('local')->exists($invoice->private_file_path), 404);

        return Storage::disk('local')->download($invoice->private_file_path, str($invoice->title)->slug().'.pdf');
    }
}
