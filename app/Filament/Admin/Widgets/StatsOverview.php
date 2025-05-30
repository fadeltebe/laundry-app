<?php

namespace App\Filament\Admin\Widgets;

use Carbon\Carbon;
use App\Models\Branch;
use App\Models\Transaction;
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
        $user = auth()->user();
        $baseQuery = Transaction::with('transactionServices.service')
            ->where('laundry_id', $user->laundries->first()?->id);

        // 🔐 Filter untuk Admin
        if (is_admin()) {
            $baseQuery->where('branch_id', $user->branches()->first()?->id);
        }

        // 📅 Filter berdasarkan tanggal
        $startDate = $this->filters['start_date'] ?? null;
        $endDate = $this->filters['end_date'] ?? null;

        if ($startDate && $endDate) {
            $baseQuery->whereBetween('received_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        } elseif ($startDate) {
            $baseQuery->where('received_at', '>=', Carbon::parse($startDate)->startOfDay());
        } elseif ($endDate) {
            $baseQuery->where('received_at', '<=', Carbon::parse($endDate)->endOfDay());
        }

        // 🏢 Filter cabang dari filter halaman
        if ($branchId = $this->filters['branch_id'] ?? null) {
            $baseQuery->where('branch_id', $branchId);
        }

        // Salin query untuk tiap range waktu
        $today = now()->toDateString();
        $month = now()->month;
        $year = now()->year;

        // 💡 Hari Ini
        $todayQuery = (clone $baseQuery)->whereDate('received_at', $today)->get();
        $todayAmount = $todayQuery->sum('amount');
        $todayCount = $todayQuery->count();
        $todayBerat = $todayQuery->flatMap->transactionServices->sum('weight');
        $todayLayanan = $todayQuery->flatMap->transactionServices->pluck('service_id')->unique()->count();

        // 💡 Bulan Ini
        $monthQuery = (clone $baseQuery)
            ->whereMonth('received_at', $month)
            ->whereYear('received_at', $year)
            ->get();
        $monthAmount = $monthQuery->sum('amount');
        $monthCount = $monthQuery->count();
        $monthBerat = $monthQuery->flatMap->transactionServices->sum('weight');
        $monthLayanan = $monthQuery->flatMap->transactionServices->pluck('service_id')->unique()->count();

        // 💡 Total Keseluruhan (tanpa filter tanggal)
        $totalQuery = (clone $baseQuery)->get();
        $totalAmount = $totalQuery->sum('amount');
        $totalCount = $totalQuery->count();
        $totalBerat = $totalQuery->flatMap->transactionServices->sum('weight');
        $totalLayanan = $totalQuery->flatMap->transactionServices->pluck('service_id')->unique()->count();

        return [
            Stat::make('Hari Ini', 'Rp ' . number_format($todayAmount, 0, ',', '.'))
                ->description("Transaksi: $todayCount | Berat: {$todayBerat}kg | Layanan: $todayLayanan")
                ->color('success'),

            Stat::make('Bulan Ini', 'Rp ' . number_format($monthAmount, 0, ',', '.'))
                ->description("Transaksi: $monthCount | Berat: {$monthBerat}kg | Layanan: $monthLayanan")
                ->color('info'),

            Stat::make('Keseluruhan', 'Rp ' . number_format($totalAmount, 0, ',', '.'))
                ->description("Transaksi: $totalCount | Berat: {$totalBerat}kg | Layanan: $totalLayanan")
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
