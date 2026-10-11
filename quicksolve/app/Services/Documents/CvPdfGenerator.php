<?php

namespace App\Services\Documents;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class CvPdfGenerator
{
    /**
     * @param  array<string, mixed>  $cv
     */
    public function download(array $cv): Response
    {
        $filename = 'cv-'.str($cv['full_name'])->slug().'.pdf';

        return Pdf::loadView('cv.pdf', ['cv' => $cv])->download($filename);
    }
}
