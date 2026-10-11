<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCoverLetterRequest;
use App\Services\Billing\PlanResolver;
use App\Services\Documents\CoverLetterPdfGenerator;
use Symfony\Component\HttpFoundation\Response;

class CoverLetterController extends Controller
{
    public function pdf(StoreCoverLetterRequest $request, CoverLetterPdfGenerator $pdf, PlanResolver $plans): Response
    {
        if (! $plans->allowsLongDescriptions($request->user())) {
            $plans->consumePremiumTrial($request->user(), $request, 'cover-letter');
        }

        return $pdf->download($request->validated());
    }
}
