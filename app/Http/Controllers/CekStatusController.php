<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class CekStatusController extends Controller
{
    public function index()
    {
        return view('cek-status.index');
    }

    public function cek(Request $request)
    {
        $request->validate([
            'kode' => 'required|string',
        ]);

        $transaction = Transaction::where('kode', $request->kode)->first();

        // dd($transaction);
        if (!$transaction) {
            return redirect()->back()->withErrors(['kode' => 'Kode transaksi tidak ditemukan.']);
        }

        return view('cek-status.index', [
            'transaction' => $transaction,
            'kode' => $request->kode,
        ]);
    }
}
