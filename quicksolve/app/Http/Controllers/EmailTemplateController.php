<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmailTemplateRequest;
use App\Services\Billing\PlanResolver;
use App\Services\Documents\EmailTemplateComposer;
use Symfony\Component\HttpFoundation\Response;

class EmailTemplateController extends Controller
{
    public function download(StoreEmailTemplateRequest $request, EmailTemplateComposer $composer, PlanResolver $plans): Response
    {
        if (! $plans->allowsLongDescriptions($request->user())) {
            $plans->consumePremiumTrial($request->user(), $request, 'email-templates');
        }

        $message = $composer->compose($request->validated());
        $filename = 'email-'.str($message['subject'])->slug()->limit(48, '').'.txt';

        return response($message['subject']."\n\n".$message['body'], 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
