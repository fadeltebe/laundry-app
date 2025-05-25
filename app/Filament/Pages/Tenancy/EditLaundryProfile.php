<?php

namespace App\Filament\Pages\Tenancy;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile;

class EditLaundryProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Profil Laundry';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
                TextInput::make('nama_owner'),
                TextInput::make('kontak_owner'),
                TextInput::make('logo'),
                TextInput::make('slogan'),
                TextInput::make('alamat'),
                TextInput::make('kontak'),
                // ...
            ]);
    }
}
