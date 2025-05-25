<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Laundry;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function index()
    {
        dd('tes');
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
        $slogan_laundry = $laundry->slogan;
        $alamat_laundry = $laundry->alamat;
        $kontak_laundry = ($laundry->kontak);

        $text = centerText(strtoupper($nama_laundry)) . "\n";
        $text .= centerText(strtoupper($slogan_laundry)) . "\n";
        $text .= centerText(strtoupper($alamat_laundry)) . '( ' . ($kontak_laundry) . ' )' . "\n";

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

    public function kirimWhatsApp($id)
    {
        $transaksi = Transaction::with(['customer', 'transactionServices.service'])->findOrFail($id);
        $customer = $transaksi->customer;
        $laundry = Laundry::first();
        $nama_laundry = $laundry->name;

        // Format nomor HP
        $nomorHp = $customer->phone;
        if (Str::startsWith($nomorHp, '0')) {
            $nomorHp = '62' . substr($nomorHp, 1);
        }
        $nomorHp = str_replace('+', '', $nomorHp);

        // Format tanggal
        $tanggal = Carbon::parse($transaksi->received_at)->format('d/m/Y');

        // Format layanan dan detail
        $detailLayanan = '';
        foreach ($transaksi->transactionServices as $item) {
            $nama = $item->service->nama_layanan ?? '-';
            $satuan = $item->service->satuan ?? '-';
            $harga = $item->price ?? 0;
            $subtotal = $item->subtotal ?? 0;

            $detailLayanan .= "{$nama}%0A";
            $detailLayanan .= "{$item->weight} {$satuan} x Rp " . number_format($harga, 0, ',', '.') . " = Rp " . number_format($subtotal, 0, ',', '.') . "%0A%0A";
        }

        // Format pesan
        $pesan = "*Hallo Kak {$customer->name},*%0A"
            . "*Pesanan Laundry*%0A%0A"
            . "🆔 ID : {$transaksi->kode}%0A"
            . "📅 Tanggal: {$tanggal}%0A"
            . "📦 Detail Layanan:%0A{$detailLayanan}"
            . "-----------------------------%0A"
            . "💰 *Total Bayar: Rp " . number_format($transaksi->amount, 0, ',', '.') . "*%0A"
            . "💳 Status Pembayaran: {$transaksi->payment_status}%0A%0A";

        // Tambahan berdasarkan status pesanan
        switch ($transaksi->status) {
            case 'Diterima':
                $pesan .= "Sudah diterima dan siap diproses yah, Terima kasih 😊🙏";
                break;
            case 'Diproses':
                $pesan .= "Sementara diproses yah, Terima kasih 😊🙏";
                break;
            case 'Selesai':
                $pesan .= "Sudah selesai dan siap diambil yah, Terima kasih 😊🙏";
                break;
            case 'Diambil':
                $pesan .= "Terima kasih sudah mempercayakan laundrynya sama kami, Ditunggu lagi kedatangannya 😊🙏";
                break;
            default:
                $pesan .= "Terima kasih 😊🙏";
                break;
        }

        // Redirect ke WhatsApp
        $url = "https://wa.me/{$nomorHp}?text={$pesan}";

        return redirect()->away($url);
    }
}
