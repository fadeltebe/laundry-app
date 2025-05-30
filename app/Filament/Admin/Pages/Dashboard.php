<?php

namespace App\Filament\Admin\Pages;

use App\Models\Branch;
use App\Models\Transaction;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Pages\Actions\Action;
use Filament\Widgets\AccountWidget;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function getHeaderWidgetsColumns(): array
    {
        return [
            'xs' => 3,
            'sm' => 3,
            'md' => 3,
            'lg' => 3,
            'xl' => 3,
            '2xl' => 3,
        ];
    }

    // protected function getActions(): array
    // {
    //     return [
    //         Action::make('Buat Laporan')
    //             ->label('Buat Laporan')
    //             ->form([
    //                 Select::make('branch_id')
    //                     ->label('Cabang')
    //                     ->options(function () {
    //                         $user = auth()->user();

    //                         if ($user->hasRole('Owner')) {
    //                             return \App\Models\Branch::whereIn('laundry_id', $user->laundries->pluck('id'))
    //                                 ->pluck('name', 'id');
    //                         }

    //                         return \App\Models\Branch::pluck('name', 'id');
    //                     })
    //                     ->searchable()
    //                     ->visible(fn() => !auth()->user()->hasRole('Admin'))
    //                     ->placeholder('Pilih cabang'),
    //                 DatePicker::make('start_date')->label('Tanggal Awal')->required(),
    //                 DatePicker::make('end_date')->label('Tanggal Akhir')->required(),
    //             ])
    //             ->action(function (array $data): void {
    //                 // Redirect ke route dengan query string
    //                 $query = http_build_query([
    //                     'branch_id'  => $data['branch_id'] ?? null,
    //                     'start_date' => $data['start_date'],
    //                     'end_date'   => $data['end_date'],
    //                 ]);

    //                 redirect()->away(route('transaksi.report') . '?' . $query);
    //             })
    //             ->color('primary')
    //             ->icon('heroicon-o-document-text')
    //     ];
    // }

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter')
                    ->columns([
                        'xs' => 3,
                        'sm' => 3,
                        'xl' => 3,
                        '2xl' => 3,
                    ])
                    ->schema([
                        Select::make('branch_id')
                            ->label('Cabang')
                            ->options(function () {
                                $user = auth()->user();

                                if ($user->hasRole('Owner')) {
                                    return \App\Models\Branch::whereIn('laundry_id', $user->laundries->pluck('id'))
                                        ->pluck('name', 'id');
                                }

                                return \App\Models\Branch::pluck('name', 'id');
                            })
                            ->searchable()
                            ->visible(fn() => !auth()->user()->hasRole('Admin')) // Admin tidak perlu melihat dropdown karena hidden
                            ->placeholder('Pilih cabang'),
                        DatePicker::make('start_date')
                            ->label('Tanggal Awal'),
                        DatePicker::make('end_date')
                            ->label('Tanggal Akhir')
                            ->label('Tanggal Akhir'),
                    ])
                    ->collapsible()
                    ->collapsed(),

            ]);
    }
}
