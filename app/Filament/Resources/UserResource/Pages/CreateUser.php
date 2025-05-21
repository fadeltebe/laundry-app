<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\UserResource;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // dd($data);
        // Enkripsi password
        $data['password'] = Hash::make($data['password']);
        return $data;
    }

    protected function afterCreate(): void
    {
        dd($this->record);
        $laundryId = $this->data['laundry_id'] ?? null;

        if ($laundryId) {
            // Tambahkan relasi laundry
            $this->record->laundries()->sync([$laundryId]);

            // Sinkronkan role dengan `laundry_id` sebagai tenant
            $this->record->roles()->syncWithPivotValues(
                $this->data['roles'] ?? [],
                ['laundry_id' => $laundryId]
            );
        }
    }
}
