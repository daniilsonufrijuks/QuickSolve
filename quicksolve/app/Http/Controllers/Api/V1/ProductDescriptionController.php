<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\GenerationLimitExceededException;
use App\Exceptions\ProviderUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateProductDescriptionRequest;
use App\Services\Descriptions\ProductDescriptionGenerator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class ProductDescriptionController extends Controller
{
    public function store(GenerateProductDescriptionRequest $request, ProductDescriptionGenerator $generator): JsonResponse
    {
        try {
            $result = $generator->generate($request->validated(), $request->user(), $request);
        } catch (GenerationLimitExceededException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 429);
        } catch (AuthorizationException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 403);
        } catch (ProviderUnavailableException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 503);
        }

        return response()->json(['data' => $result]);
    }
}
