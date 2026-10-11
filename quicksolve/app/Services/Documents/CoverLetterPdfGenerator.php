<?php

namespace App\Services\Documents;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class CoverLetterPdfGenerator
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function download(array $input): Response
    {
        $filename = 'cover-letter-'.str($input['full_name'])->slug().'.pdf';

        return Pdf::loadView('cover-letters.pdf', [
            'letter' => $input,
        ])->download($filename);
    }
}
