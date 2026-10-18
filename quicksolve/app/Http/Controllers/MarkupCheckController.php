<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckMarkupRequest;
use App\Services\Tools\W3cMarkupChecker;
use Illuminate\Http\JsonResponse;

class MarkupCheckController extends Controller
{
    public function html(CheckMarkupRequest $request, W3cMarkupChecker $checker): JsonResponse
    {
        $validated = $request->validated();

        return $this->result($checker->html(
            $validated['source'],
            ($validated['mode'] ?? 'document') === 'fragment',
        ));
    }

    public function css(CheckMarkupRequest $request, W3cMarkupChecker $checker): JsonResponse
    {
        $validated = $request->validated();

        return $this->result($checker->css(
            $validated['source'],
            $validated['profile'] ?? 'css3svg',
        ));
    }

    /**
     * @param  array{valid: bool, checked_by: string, messages: list<array{severity: string, line: int|null, column: int|null, message: string, extract: string|null}>}|null  $result
     */
    private function result(?array $result): JsonResponse
    {
        if ($result === null) {
            return response()->json([
                'message' => 'The W3C checker is unavailable right now. Try again in a moment.',
            ], 503);
        }

        return response()->json(['data' => $result]);
    }
}
