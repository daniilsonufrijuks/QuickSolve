<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\GenerationLimitExceededException;
use App\Exceptions\ProviderUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AskHomeChatRequest;
use App\Services\HomeChat\HomeChatGenerator;
use Illuminate\Http\JsonResponse;

class HomeChatController extends Controller
{
    public function store(AskHomeChatRequest $request, HomeChatGenerator $generator): JsonResponse
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
