<?php

namespace App\Filament\Admin\Resources\TransactionResource\Pages;

use Filament\Actions;
use App\Models\Branch;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Admin\Resources\TransactionResource;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if (! $user->hasRole('super_admin')) {
            $data['branch_id'] = Branch::where('user_id', $user->id)->first()?->id;
        }

        return $data;
    }
}
