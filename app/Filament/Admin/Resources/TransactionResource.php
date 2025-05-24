<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Admin\Resources\TransactionResource\Pages;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use App\Filament\Admin\Resources\TransactionResource\RelationManagers;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $branchId = is_admin() ? $user->branches()->first()?->id : null;

        return $form->schema([
            Forms\Components\Select::make('branch_id')
                ->label('Cabang')
                ->options(function () use ($user) {
                    if ($user->hasRole('Owner')) {
                        return \App\Models\Branch::whereIn('laundry_id', $user->laundries->pluck('id'))
                            ->pluck('name', 'id');
                    }

                    return \App\Models\Branch::pluck('name', 'id');
                })
                ->required()
                ->searchable()
                ->preload()
                ->default($branchId) // ✅ Diisi otomatis
                ->hidden(fn() => is_admin()), // ✅ Dikirim walaupun disembunyikan
            Forms\Components\Select::make('customer_id')
                ->relationship('customer', 'name'),
            Forms\Components\TextInput::make('description')
                ->maxLength(255),
            Forms\Components\DateTimePicker::make('received_at'),
            Forms\Components\DateTimePicker::make('completed_at'),
            ToggleButtons::make('status')
                ->options([
                    'Diterima' => 'Diterima',
                    'Diproses' => 'Diproses',
                    'Selesai' => 'Selesai',
                    'Diambil' => 'Diambil'
                ])
                ->colors([
                    'Diterima' => 'info',
                    'Diproses' => 'warning',
                    'Selesai' => 'success',
                    'Diambil' => 'danger',
                ])->inline()

                ->default('Diterima')
                ->required()
                ->grouped(),

            Select::make('payment_method')
                ->label('Metode Pembayaran')
                ->options([
                    'Cash' => 'Cash',
                    'Transfer' => 'Transfer',
                    'QRIS' => 'QRIS',
                ])
                ->required(fn(callable $get) => $get('status_bayar') === true),
            Forms\Components\DateTimePicker::make('paid_at'),
            ToggleButtons::make('payment_status')
                ->options([
                    'Lunas' => 'Lunas',
                    'Belum Lunas' => 'Belum Lunas',
                ])
                ->colors([
                    'Lunas' => 'success',
                    'Belum Lunas' => 'danger',
                ])->inline()
                ->default('Belum Lunas')
                ->required()
                ->grouped()
                ->reactive(),
            TableRepeater::make('transactionServices')
                ->label('Layanan Transaksi')
                ->relationship('transactionServices') // pastikan relasi ini didefinisikan di model Transaction
                ->schema([
                    Select::make('service_id')
                        ->label('Layanan')
                        ->relationship('service', 'nama_layanan')
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->nama_layanan . ' - Rp. ' . number_format($record->harga, 0, ',', '.') . '/' . $record->satuan)
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (callable $get, callable $set, $state) {
                            $weight = $get('weight');
                            $service = \App\Models\Service::find($state);
                            $set('price', $service->harga);
                            if ($weight && $service) {
                                $set('subtotal', $service->harga * $weight);
                            }

                            // Update amount
                            $items = $get('../../transactionServices') ?? [];
                            $total = collect($items)->sum('subtotal');
                            $set('../../amount', $total);
                        }),

                    Select::make('add_ons_id')
                        ->label('Add-ons')
                        ->relationship('addOns', 'name')
                        ->nullable(),

                    TextInput::make('price')
                        ->label('Harga per Kg')
                        ->numeric()
                        ->prefix('Rp')
                        ->readOnly(),

                    TextInput::make('weight')
                        ->label('Berat')
                        ->numeric()
                        ->inputMode('decimal')
                        ->suffix('Kg')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (callable $get, callable $set, $state) {
                            $service = $get('service_id');
                            $price = \App\Models\Service::find($service)?->harga ?? 0;
                            $set('price', $price);
                            $set('subtotal', $price * $state);

                            $items = $get('../../transactionServices') ?? [];
                            $total = collect($items)->sum('subtotal');
                            $set('../../amount', $total);
                        }),

                    TextInput::make('subtotal')
                        ->label('Subtotal')
                        ->numeric()
                        ->prefix('Rp')
                        ->required()
                        ->readOnly()
                        ->dehydrated(true),
                ])
                ->defaultItems(1)
                ->columns(2)
                ->columnSpanFull()
                ->afterStateHydrated(function (callable $get, callable $set) {
                    $items = $get('transactionServices') ?? [];
                    $total = collect($items)->sum('subtotal');
                    $set('amount', $total);
                }),
            Forms\Components\TextInput::make('amount')
                ->label('Total')
                ->required()
                ->readOnly()
                ->numeric(),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('laundry.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('received_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
