<?php

namespace App\Filament\Admin\Resources\AddOnResource\Pages;

use App\Filament\Admin\Resources\AddOnResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAddOn extends EditRecord
{
    protected static string $resource = AddOnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
