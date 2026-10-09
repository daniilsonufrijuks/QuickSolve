<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnalyticsEventRequest;
use App\Services\Analytics\UsageRecorder;
use Illuminate\Http\Response;

class AnalyticsController extends Controller
{
    public function store(StoreAnalyticsEventRequest $request, UsageRecorder $usage): Response
    {
        $metadata = $request->input('metadata', []);
        $metadata = is_array($metadata) ? array_slice($metadata, 0, 10) : [];

        $usage->record(
            $request->string('type')->toString(),
            $request->string('slug')->toString(),
            $request->user(),
            $request,
            $metadata,
        );

        return response()->noContent();
    }
}
