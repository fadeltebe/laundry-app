<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CekStatusController;
use App\Http\Controllers\TransaksiController;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

Route::get('/transaksi/{id}/print', [TransaksiController::class, 'print'])->name('transaksi.print');

Route::get('/transaksi/{id}/kirim-wa', [TransaksiController::class, 'kirimWhatsApp'])->name('transaksi.kirim-wa');

Route::get('/transaksi/report', [TransaksiController::class, 'report'])->name('transaksi.report');


Route::get('/cek-status', [CekStatusController::class, 'index'])->name('cek-status.index');
Route::post('/cek-status', [CekStatusController::class, 'cek'])->name('cek-status.cek');
