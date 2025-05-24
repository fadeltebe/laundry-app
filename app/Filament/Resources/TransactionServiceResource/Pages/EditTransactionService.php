<?php

namespace App\Filament\Resources\TransactionServiceResource\Pages;

use App\Filament\Resources\TransactionServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransactionService extends EditRecord
{
    protected static string $resource = TransactionServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
