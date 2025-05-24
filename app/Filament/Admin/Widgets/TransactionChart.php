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
        $startMonth = 1;
        $endMonth = now()->month;

        if ($endDate = $this->filters['end_date'] ?? null) {
            $carbonEnd = \Carbon\Carbon::parse($endDate);
            $endMonth = $carbonEnd->month;
            $tahun = $carbonEnd->year;
        }

        if ($startDate = $this->filters['start_date'] ?? null) {
            $carbonStart = \Carbon\Carbon::parse($startDate);
            $tahun = $carbonStart->year;
        }

        // Ambil branch_id dari filter
        $branchId = $this->filters['branch_id'] ?? null;



        for ($i = 1; $i <= $endMonth; $i++) {
            $bulan = \Carbon\Carbon::createFromDate($tahun, $i, 1);

            $query = \App\Models\Transaction::query()
                ->whereYear('created_at', $bulan->year)
                ->whereMonth('created_at', $bulan->month);

            $user = auth()->user();

            // 🔒 Filter cabang jika user adalah admin
            if (is_admin()) {
                $query->where('branch_id', $user->branches()->first()?->id);
            }


            $jumlah = $query->sum('amount');

            $data->push($jumlah);
            $labels[] = $bulan->translatedFormat('F');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Transaksi',
                    'data' => $data,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                    'borderColor' => 'rgba(75, 192, 192, 0.5)',
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
