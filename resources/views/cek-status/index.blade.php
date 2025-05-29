<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Pesanan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-r from-blue-400 via-indigo-500 to-purple-600 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white shadow-lg rounded-lg p-6 w-full max-w-xl">
        <h1 class="text-2xl font-bold mb-4 text-indigo-600 text-center">Cek Status Pesanan</h1>

        <form action="{{ route('cek-status.cek') }}" method="POST" class="space-y-4">
            @csrf
            <input type="text" name="kode" value="{{ old('kode', $kode ?? '') }}" placeholder="Masukkan kode pesanan" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:outline-none">
            <button type="submit" class="w-full bg-indigo-600 text-white rounded-md py-2 hover:bg-indigo-700 transition">Cek Status</button>
        </form>

        @if (isset($transaction))
        @php
        $steps = ['Diterima', 'Diproses', 'Selesai', 'Diambil'];
        $currentStatus = strtolower($transaction->status);
        $statusIndex = array_search($currentStatus, array_map('strtolower', $steps));
        @endphp

        <div class="mt-8">
            <ol class="flex justify-between items-start w-full text-sm font-medium text-gray-500 sm:text-base">
                @foreach ($steps as $index => $step)
                <li class="flex flex-col items-center w-full">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full text-white text-lg
                                {{ $index <= $statusIndex ? 'bg-blue-600' : 'bg-gray-300 text-gray-500' }}">
                        @if ($index <= $statusIndex) ✅ @else ❌ @endif </div>
                            <span class="mt-2 text-xs text-center sm:text-sm {{ $index <= $statusIndex ? 'text-blue-600' : 'text-gray-500' }}">
                                {{ $step }}
                            </span>
                </li>
                @endforeach
            </ol>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <tbody>
                    <tr class="border-b">
                        <th class="py-2 px-3 font-semibold">Kode Pesanan</th>
                        <td class="py-2 px-3">{{ $transaction->kode }}</td>
                    </tr>
                    <tr class="border-b bg-gray-50">
                        <th class="py-2 px-3 font-semibold">Nama Customer</th>
                        <td class="py-2 px-3">{{ $transaction->customer->name }}</td>
                    </tr>
                    <tr class="border-b">
                        <th class="py-2 px-3 font-semibold">Tanggal</th>
                        <td class="py-2 px-3">{{ $transaction->created_at->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr class="border-b bg-gray-50">
                        <th class="py-2 px-3 font-semibold">Status Pesanan</th>
                        <td class="py-2 px-3">{{ $transaction->status }}</td>
                    </tr>
                    <tr>
                        <th class="py-2 px-3 font-semibold">Status Pembayaran</th>
                        <td class="py-2 px-3">{{ $transaction->payment_status }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        @elseif(isset($kode))
        <p class="mt-6 text-center text-red-600 font-semibold">Kode pesanan tidak ditemukan.</p>
        @endif
    </div>
</body>

</html>