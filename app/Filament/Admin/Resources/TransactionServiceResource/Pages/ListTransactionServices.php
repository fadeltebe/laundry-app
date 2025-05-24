<?php

namespace App\Filament\Admin\Resources\TransactionServiceResource\Pages;

use App\Filament\Admin\Resources\TransactionServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransactionServices extends ListRecords
{
    protected static string $resource = TransactionServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
