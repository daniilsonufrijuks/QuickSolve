<?php

namespace App\Http\Controllers;

use App\Http\Requests\FetchJsonResumeRequest;
use App\Http\Requests\ImportJsonResumeRequest;
use App\Http\Requests\StoreCvRequest;
use App\Services\Documents\JsonResumeMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class JsonResumeController extends Controller
{
    public function import(ImportJsonResumeRequest $request, JsonResumeMapper $mapper): JsonResponse
    {
        $template = $request->validated('template') ?: 'classic';

        return response()->json([
            'data' => $mapper->toCv($request->validated('resume'), $template),
        ]);
    }

    public function export(StoreCvRequest $request, JsonResumeMapper $mapper): Response
    {
        $resume = $mapper->fromCv($request->validated());
        $filename = 'resume-'.str($request->validated('full_name'))->slug().'.json';

        return response(json_encode($resume, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), 200, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function registry(FetchJsonResumeRequest $request, JsonResumeMapper $mapper): JsonResponse
    {
        $username = $request->validated('username');
        $template = $request->validated('template') ?: 'classic';
        $url = 'https://registry.jsonresume.org/'.$username.'.json';

        try {
            $response = Http::acceptJson()
                ->connectTimeout(3)
                ->timeout(8)
                ->withOptions(['allow_redirects' => false])
                ->get($url);
        } catch (\Throwable) {
            return response()->json([
                'message' => 'The JSON Resume registry is unavailable. Paste a resume.json file instead.',
            ], 503);
        }

        if ($response->notFound()) {
            return response()->json([
                'message' => 'No resume.json is published for that GitHub username on the JSON Resume registry.',
            ], 404);
        }

        if (! $response->successful()) {
            return response()->json([
                'message' => 'The JSON Resume registry is unavailable. Paste a resume.json file instead.',
            ], 503);
        }

        $resume = $response->json();

        if (! is_array($resume)) {
            return response()->json([
                'message' => 'That registry file is not a JSON Resume object.',
            ], 422);
        }

        return response()->json([
            'data' => $mapper->toCv($resume, $template),
        ]);
    }
}
