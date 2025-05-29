<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Penting untuk mobile -->
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
        $icons = ['✅', '🧺', '✔️', '📦'];
        $currentStatus = strtolower($transaction->status);
        $statusIndex = array_search($currentStatus, array_map('strtolower', $steps));
        @endphp

        <div class="mt-8 overflow-x-auto">
            <div class="flex items-center justify-between text-sm font-medium text-gray-600 relative min-w-[28rem]">
                @foreach($steps as $index => $step)
                <div class="flex-1 flex flex-col items-center relative min-w-[4rem]">
                    <!-- Icon Status -->
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg
                        {{ $index <= $statusIndex ? 'bg-white border-2 border-blue-500 text-blue-500' : 'bg-gray-300 text-gray-600' }}">
                        {{ $icons[$index] }}
                    </div>
                    <!-- Label -->
                    <div class="mt-2 text-xs text-center">{{ $step }}</div>

                    <!-- Garis Penghubung -->
                    @if ($index < count($steps) - 1) <div class="absolute top-5 right-0 w-full h-1
                        {{ $index < $statusIndex ? 'bg-indigo-600' : 'bg-gray-300' }}" style="left: 50%; transform: translateX(0%); width: 100%;">
                </div>
                @endif
            </div>
            @endforeach
        </div>
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