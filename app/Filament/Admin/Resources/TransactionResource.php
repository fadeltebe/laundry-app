<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TransactionResource\Pages;
use App\Filament\Admin\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $tenantOwnershipRelationshipName = 'laundry';

    protected static ?string $navigationLabel = 'Transaksi';


    public static function form(Form $form): Form
    {
        $user = auth()->user();

        $isAdmin = $user->hasRole('Admin');
        $branchId = $isAdmin ? $user->branches()->first()?->id : null;

        return $form
            ->schema([
                Forms\Components\Select::make('branch_id')
                    ->label('Cabang')
                    ->options(function () {
                        $user = auth()->user();

                        // Jika owner, hanya tampilkan cabang dari laundry yang dia miliki
                        if ($user->hasRole('Owner')) {
                            return \App\Models\Branch::whereIn('laundry_id', $user->laundries->pluck('id'))
                                ->pluck('name', 'id');
                        }

                        // Jika superadmin atau lainnya, tampilkan semua cabang
                        return \App\Models\Branch::pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->visible(fn() => !auth()->user()->hasRole('Admin')), // Admin tidak perlu melihat dropdown karena hidden
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')->sortable(),
                Tables\Columns\TextColumn::make('description')->searchable(),
                Tables\Columns\TextColumn::make('amount')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                // Filter berdasarkan Cabang
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Filter Cabang')
                    ->options(function () {
                        $user = auth()->user();

                        if ($user->hasRole('Owner')) {
                            return \App\Models\Branch::whereIn('laundry_id', $user->laundries->pluck('id'))
                                ->pluck('name', 'id');
                        }

                        return \App\Models\Branch::pluck('name', 'id');
                    })
                    ->searchable()
                    ->visible(fn() => !auth()->user()->hasRole('Admin')) // Admin tidak perlu melihat dropdown karena hidden
                    ->preload(),

                // Filter berdasarkan Tanggal
                Tables\Filters\Filter::make('transaction_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('created_at', '<=', $data['until']));
                    })
                    ->label('Tanggal Transaksi'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        // Jika user memiliki peran admin cabang
        if ($user->hasRole('Admin')) {
            return parent::getEloquentQuery()
                ->whereHas('branch', fn($query) => $query->where('user_id', $user->id));
        }

        // Superadmin atau lainnya bisa lihat semua
        return parent::getEloquentQuery();
    }
}
