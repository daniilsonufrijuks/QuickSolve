<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\GenerationLimitExceededException;
use App\Exceptions\ProviderUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateEmailDraftRequest;
use App\Services\Emails\EmailDraftGenerator;
use Illuminate\Http\JsonResponse;

class EmailDraftController extends Controller
{
    public function store(GenerateEmailDraftRequest $request, EmailDraftGenerator $generator): JsonResponse
    {
        try {
            $result = $generator->generate($request->validated(), $request->user(), $request);
        } catch (GenerationLimitExceededException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 429);
        } catch (ProviderUnavailableException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 503);
        }

        return response()->json(['data' => $result]);
    }
}
