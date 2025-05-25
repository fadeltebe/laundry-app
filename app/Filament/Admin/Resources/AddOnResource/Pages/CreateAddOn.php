<?php

namespace App\Filament\Admin\Resources\AddOnResource\Pages;

use App\Filament\Admin\Resources\AddOnResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAddOn extends CreateRecord
{
    protected static string $resource = AddOnResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
