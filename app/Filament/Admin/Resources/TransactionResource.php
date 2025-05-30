<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\AddOn;
use App\Models\Branch;
use App\Models\Service;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use PhpParser\Node\Stmt\Label;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Admin\Resources\TransactionResource\Pages;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use App\Filament\Admin\Resources\TransactionResource\RelationManagers;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Transaksi';


    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $branchId = is_admin() ? $user->branches()->first()?->id : null;

        return $form->schema([

            Forms\Components\TextInput::make('kode')
                ->label('Kode')
                ->disabled()
                ->dehydrated() // Supaya tetap disimpan ke database
                ->required()
                ->default(fn(callable $get) => self::generateKode($get('tanggal_masuk') ?? now())),

            Forms\Components\Select::make('branch_id')
                ->label('Cabang')
                ->options(function () use ($user) {
                    if ($user->hasRole('Owner')) {
                        return Branch::whereIn('laundry_id', $user->laundries->pluck('id'))
                            ->pluck('name', 'id');
                    }

                    return Branch::pluck('name', 'id');
                })
                ->required()
                ->preload()
                ->default($branchId) // ✅ Diisi otomatis
                ->hidden(fn() => is_admin()), // ✅ Dikirim walaupun disembunyikan
            Forms\Components\Select::make('customer_id')
                ->label('Pelanggan')
                ->options(function () use ($user) {
                    return Customer::whereIn('laundry_id', $user->laundries->pluck('id'))
                        ->pluck('name', 'id');
                })
                ->searchable()
                ->preload()
                ->required()
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')->required(),
                    Forms\Components\TextInput::make('phone')->required(),
                    Forms\Components\TextInput::make('adress'),
                ])
                ->createOptionUsing(function (array $data) {
                    // Pastikan laundry_id diset agar relasinya benar
                    $data['laundry_id'] = auth()->user()->laundries()->first()?->id;
                    return Customer::create($data);
                })
                ->createOptionAction(
                    fn(\Filament\Forms\Components\Actions\Action $action) =>
                    $action->mutateFormDataUsing(function (array $data) {
                        $data['laundry_id'] = auth()->user()->laundries()->first()?->id;
                        return $data;
                    })
                ),

            Forms\Components\TextInput::make('description')
                ->label('Catatan')
                ->maxLength(255),
            Forms\Components\DateTimePicker::make('received_at')
                ->default(now())
                ->label('Tanggal Diterima')
                ->required(),
            Forms\Components\DateTimePicker::make('completed_at')
                ->default(now())
                ->label('Tanggal Selesai')
                ->required(fn(callable $get) => $get('status') === 'Selesai'),
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
            DateTimePicker::make('paid_at')
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state) {
                        $set('payment_status', 'Lunas');
                    } else {
                        $set('payment_status', 'Belum Lunas');
                    }
                }),
            ToggleButtons::make('payment_status')
                ->options([
                    'Lunas' => 'Lunas',
                    'Belum Lunas' => 'Belum Lunas',
                ])
                ->colors([
                    'Lunas' => 'success',
                    'Belum Lunas' => 'danger',
                ])
                ->inline()
                ->disabled() // Agar tidak bisa diubah manual
                ->default('Belum Lunas')
                ->required()
                ->reactive(),
            Repeater::make('transactionServices')
                ->label('Layanan Transaksi')
                ->relationship('transactionServices') // pastikan relasi ini didefinisikan di model Transaction
                ->schema([
                    Select::make('service_id')
                        ->label('Layanan')
                        ->options(function () use ($user) {
                            return Service::whereIn('laundry_id', $user->laundries->pluck('id'))
                                ->pluck('nama_layanan', 'id');
                        })
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
                        ->options(function () use ($user) {
                            return AddOn::whereIn('laundry_id', $user->laundries->pluck('id'))
                                ->pluck('name', 'id');
                        })
                        ->required(),
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
                Split::make([
                    Stack::make([
                        Tables\Columns\TextColumn::make('customer.name'),
                        Tables\Columns\TextColumn::make('kode')->weight(FontWeight::Bold),
                    ]),
                    Stack::make([
                        Tables\Columns\TextColumn::make('received_at')
                            ->dateTime()
                            ->sortable(),
                        Tables\Columns\TextColumn::make('status'),

                    ]),
                    Stack::make([

                        Tables\Columns\TextColumn::make('amount')
                            ->numeric()
                            ->sortable(),
                        Tables\Columns\TextColumn::make('payment_status'),
                    ]),

                ])
            ])
            ->poll('10s')
            ->filters([
                //
            ])
            ->actions(
                [
                    ActionGroup::make([
                        Tables\Actions\EditAction::make()->color('primary'),
                        // Tables\Actions\ViewAction::make(),
                        Action::make('printBluetooth')
                            ->label('Cetak')
                            ->url(fn($record) => route('transaksi.print', $record))
                            ->icon('heroicon-o-printer')->color('danger'),
                        Action::make('kirimWhatsApp')
                            ->label('Kirim WA')
                            ->url(fn($record) => route('transaksi.kirim-wa', $record))
                            ->icon('heroicon-o-chat-bubble-oval-left-ellipsis')
                            ->color('success')
                            ->openUrlInNewTab()
                    ])->icon('heroicon-c-squares-2x2')
                ],
                position: ActionsPosition::BeforeColumns

            )
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
            // 'edit' => Pages\EditTransaction::route('/{record}/edit'),
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

    private static function generateKode($tanggal)
    {
        $tanggal = \Carbon\Carbon::parse($tanggal);
        $prefix = '25' . $tanggal->format('md');

        $latestKode = \App\Models\Transaction::whereDate('received_at', $tanggal->toDateString())
            ->latest('id')
            ->value('kode');

        $lastIncrement = 0;
        if ($latestKode && substr($latestKode, 0, 6) === $prefix) {
            $lastIncrement = (int) substr($latestKode, 6);
        }

        return $prefix . str_pad($lastIncrement + 1, 3, '0', STR_PAD_LEFT);
    }
}
