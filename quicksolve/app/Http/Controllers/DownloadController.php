<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function __invoke(Request $request, Purchase $purchase): StreamedResponse
    {
        $this->authorize('download', $purchase);

        $template = $purchase->template;
        $path = $template->private_file_path;
        $filename = str($template->name)->slug().'.'.pathinfo($path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download($path, $filename);
    }
}
