<?php

use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

Route::get('/transaksi/{id}/print', [TransaksiController::class, 'print'])->name('transaksi.print');

Route::get('/transaksi/{id}/kirim-wa', [TransaksiController::class, 'kirimWhatsApp'])->name('transaksi.kirim-wa');
