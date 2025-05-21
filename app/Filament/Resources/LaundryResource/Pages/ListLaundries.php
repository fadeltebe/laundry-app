<?php

namespace App\Filament\Resources\LaundryResource\Pages;

use App\Filament\Resources\LaundryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLaundries extends ListRecords
{
    protected static string $resource = LaundryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
