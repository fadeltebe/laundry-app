<?php

namespace App\Http\Controllers;

use App\Models\Laundry;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function index()
    {
        return view('transaksi.index');
    }

    public function print($id)
    {
        $transaksi = Transaction::with('transactionServices.service', 'customer')->findOrFail($id);


        function centerText($text, $width = 32)
        {
            $text = trim($text);
            $padding = max(0, floor(($width - strlen($text)) / 2));
            return str_repeat(' ', $padding) . $text;
        }

        $laundry = Laundry::first();
        $nama_laundry = $laundry->name;
        // $alamat_laundry = $laundry->alamat;
        // $telepon_laundry = ($laundry->telepon);
        $text = centerText("STRUK PEMBAYARAN") . "\n";
        $text .= centerText(strtoupper($nama_laundry)) . "\n";
        // $text .= centerText(strtoupper($alamat_laundry)) . "\n";
        // $text .= centerText($telepon_laundry) . "\n";

        $text .= "-----------------------------\n";
        $text .= "customer: " . ($transaksi->customer->name ?? '-') . "\n";
        $text .= "Tanggal  : " . $transaksi->received_at . "\n\n";

        foreach ($transaksi->transactionServices as $item) {
            $text .= $item->service->nama_layanan . "\n";
            $text .= $item->weight . " " . $item->service->satuan . " x Rp " . number_format($item->service->harga, 0, ',', '.') . " = " . number_format($item->subtotal, 0, ',', '.') . "\n";
        }

        $text .= "-----------------------------\n";
        $text .= "Total Bayar: Rp " . number_format($transaksi->amount, 0, ',', '.') . "\n";
        $text .= "-----------------------------\n";
        $text .= "Terima kasih :)\n";

        $encodedText = urlencode($text);

        $intentUrl = "intent:$encodedText#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;";

        return redirect()->away($intentUrl);
    }
}
