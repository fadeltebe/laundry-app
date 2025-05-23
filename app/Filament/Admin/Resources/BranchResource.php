<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Branch;
use App\Models\Laundry;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Admin\Resources\BranchResource\Pages;
use App\Filament\Admin\Resources\BranchResource\RelationManagers;


class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Cabang';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nama Cabang')
                ->required()
                ->maxLength(255),

            Select::make('user_id')
                ->label('Admin Cabang')
                ->options(function () {
                    $laundry = Filament::getTenant(); // atau sesuaikan dengan tenant resolver kamu
                    if (! $laundry) return [];

                    return User::whereHas('laundries', function ($query) use ($laundry) {
                        $query->where('laundries.id', $laundry->id);
                    })
                        ->role('Admin') // pastikan ini sesuai dengan Spatie Role kamu
                        ->pluck('name', 'id');
                })

                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Nama Cabang')->searchable(),
            Tables\Columns\TextColumn::make('laundry.name')->label('Laundry')->sortable(),
            Tables\Columns\TextColumn::make('user.name')->label('Admin')->sortable(),
        ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
