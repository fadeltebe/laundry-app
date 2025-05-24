<?php

namespace App\Filament\Admin\Widgets;

use Carbon\Carbon;
use App\Models\Transaction;
use App\Models\Branch;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{

    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $query = Transaction::query();
        $user = auth()->user();

        // 🔒 Filter cabang jika user adalah admin
        if (is_admin()) {
            $query->where('branch_id', $user->branches()->first()?->id);
        }

        // 📅 Filter tanggal
        $startDate = $this->filters['start_date'] ?? null;
        $endDate = $this->filters['end_date'] ?? null;

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);
        } elseif ($startDate) {
            $query->where('created_at', '>=', Carbon::parse($startDate)->startOfDay());
        } elseif ($endDate) {
            $query->where('created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }

        // 🏢 Filter berdasarkan cabang dari filter halaman
        if ($branchId = $this->filters['branch_id'] ?? null) {
            $query->where('branch_id', $branchId);
        }

        return [
            Stat::make('Total Hari Ini', number_format(
                (clone $query)->whereDate('created_at', now())->sum('amount'),
                0,
                ',',
                '.'
            ))
                ->description('Jumlah transaksi hari ini')
                ->color('success'),

            Stat::make('Total Bulan Ini', number_format(
                (clone $query)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('amount'),
                0,
                ',',
                '.'
            ))
                ->description('Jumlah transaksi bulan ini')
                ->color('info'),

            Stat::make('Total Keseluruhan', number_format(
                (clone $query)->sum('amount'),
                0,
                ',',
                '.'
            ))
                ->description('Jumlah seluruh transaksi')
                ->color('primary'),
        ];
    }

    protected function hasFiltersForm(): bool
    {
        return true;
    }

    protected function getFilters(): array
    {
        return [
            DatePicker::make('start_date')->label('Tanggal Mulai'),
            DatePicker::make('end_date')->label('Tanggal Akhir'),
            Select::make('branch_id')
                ->label('Cabang')
                ->options(Branch::pluck('name', 'id'))
                ->searchable()
                ->placeholder('Semua Cabang'),
        ];
    }
}
