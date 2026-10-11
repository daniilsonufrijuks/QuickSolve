<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCvRequest;
use App\Services\Billing\PlanResolver;
use App\Services\Documents\CvPdfGenerator;
use Symfony\Component\HttpFoundation\Response;

class CvController extends Controller
{
    public function pdf(StoreCvRequest $request, CvPdfGenerator $pdf, PlanResolver $plans): Response
    {
        if (! $plans->allowsLongDescriptions($request->user())) {
            $plans->consumePremiumTrial($request->user(), $request, 'cv-builder');
        }

        return $pdf->download($request->validated());
    }
}
