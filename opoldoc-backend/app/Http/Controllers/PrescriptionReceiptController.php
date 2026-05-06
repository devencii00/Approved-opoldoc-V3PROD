<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PrescriptionReceiptController extends Controller
{
    public function show(Request $request, int $prescriptionId)
    {
        return view('print.prescription_receipt', [
            'prescriptionId' => $prescriptionId,
        ]);
    }
}
