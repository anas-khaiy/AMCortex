<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentationController extends Controller
{
    public function downloadPdf()
    {
        $pdf = Pdf::loadView('docs.amcortex-guide')
            ->setPaper('a4', 'portrait');

        return $pdf->download('AMCortex_Guide_Utilisateur.pdf');
    }
}