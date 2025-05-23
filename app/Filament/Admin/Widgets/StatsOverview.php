<?php

namespace App\Filament\Admin\Widgets;

use Carbon\Carbon;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Ambil data transaksi
        $today = Transaction::whereDate('created_at', Carbon::today())->sum('amount');
        $thisMonth = Transaction::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount');
        $total = Transaction::sum('amount');

        return [
            Stat::make('Total Hari Ini', number_format($today, 0, ',', '.'))
                ->description('Jumlah transaksi hari ini')
                ->color('success'),

            Stat::make('Total Bulan Ini', number_format($thisMonth, 0, ',', '.'))
                ->description('Jumlah transaksi bulan ini')
                ->color('info'),

            Stat::make('Total Keseluruhan', number_format($total, 0, ',', '.'))
                ->description('Jumlah seluruh transaksi')
                ->color('primary'),
        ];
    }
}
