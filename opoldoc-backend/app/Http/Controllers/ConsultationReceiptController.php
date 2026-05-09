<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConsultationReceiptController extends Controller
{
    public function show(Request $request, int $transactionId)
    {
        return view('print.consultation_receipt', [
            'transactionId' => $transactionId,
        ]);
    }
}
