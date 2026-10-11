<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScopeChangeRequest;
use App\Services\Billing\PlanResolver;
use App\Services\Documents\ScopeChangePdfGenerator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class ScopeChangeController extends Controller
{
    public function pdf(StoreScopeChangeRequest $request, ScopeChangePdfGenerator $pdf, PlanResolver $plans): Response
    {
        if (! $plans->allowsLongDescriptions($request->user())) {
            $plans->consumePremiumTrial($request->user(), $request, 'scope-change-note');
        }

        try {
            return $pdf->download($request->validated());
        } catch (InvalidArgumentException $exception) {
            abort(422, $exception->getMessage());
        }
    }
}
