<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\GenerationLimitExceededException;
use App\Exceptions\ProviderUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateCvChatRequest;
use App\Services\Cv\CvChatGenerator;
use Illuminate\Http\JsonResponse;

class CvChatController extends Controller
{
    public function store(GenerateCvChatRequest $request, CvChatGenerator $generator): JsonResponse
    {
        try {
            $result = $generator->reply($request->validated(), $request->user(), $request);
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
