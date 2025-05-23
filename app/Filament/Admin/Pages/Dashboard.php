<?php

namespace App\Filament\Admin\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Widgets\AccountWidget;
use Filament\Forms\Components\Select;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        return $form
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
                    ->label('Tanggal Mulai'),
                DatePicker::make('end_date')
                    ->label('Tanggal Akhir')
                    ->label('Tanggal'),
            ]);
    }
}
