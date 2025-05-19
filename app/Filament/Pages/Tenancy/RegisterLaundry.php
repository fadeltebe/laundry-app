<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Laundry;
use App\Models\Team;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;

class RegisterLaundry extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register Laundry';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
                TextInput::make('slug'),
                // ...
            ]);
    }

    protected function handleRegistration(array $data): Laundry
    {
        $laundry = Laundry::create($data);

        $laundry->members()->attach(auth()->user());

        return $laundry;
    }
}
