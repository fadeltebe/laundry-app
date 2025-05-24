<?php

namespace App\Filament\Admin\Widgets;

use Carbon\Carbon;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TransactionChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Grafik Transaksi Bulanan';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = collect();
        $labels = [];

        $tahun = now()->year;

        $startMonth = 1; // Mulai dari bulan Januari
        $endMonth = now()->month; // Hingga bulan saat ini

        // Gunakan filter tanggal jika tersedia, tetapi tetap mulai dari Januari
        if ($endDate = $this->filters['end_date'] ?? null) {
            $carbonEnd = Carbon::parse($endDate);
            $endMonth = $carbonEnd->month;
            $tahun = $carbonEnd->year;
        }

        if ($startDate = $this->filters['start_date'] ?? null) {
            $carbonStart = Carbon::parse($startDate);
            $tahun = $carbonStart->year; // Tahun tetap mengacu pada filter
        }

        for ($i = 1; $i <= $endMonth; $i++) {
            $bulan = Carbon::createFromDate($tahun, $i, 1);

            $query = Transaction::query()
                ->whereYear('created_at', $bulan->year)
                ->whereMonth('created_at', $bulan->month);

            // Filter cabang jika ada
            if ($branchId = $this->filters['branch_id'] ?? null) {
                $query->where('branch_id', $branchId);
            }

            $jumlah = $query->sum('amount');

            $data->push($jumlah);
            $labels[] = $bulan->translatedFormat('F'); // Hanya nama bulan
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Transaksi',
                    'data' => $data->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
