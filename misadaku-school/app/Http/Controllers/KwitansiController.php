<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentPayment;

class KwitansiController extends Controller
{
    public function show($id)
    {
        $payment = StudentPayment::with(['student', 'bill'])->findOrFail($id);

        return view('kwitansi.student-payment', compact('payment'));
    }
}
