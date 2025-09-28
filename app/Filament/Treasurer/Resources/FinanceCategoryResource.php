<?php


namespace App\Filament\Treasurer\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Classroom;
use Filament\Tables\Table;
use Table\Action\DeleteAction;
use App\Models\FinanceCategory;

use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\FinanceCategoryResource\Pages;
use App\Filament\Resources\FinanceCategoryResource\RelationManagers;

class FinanceCategoryResource extends Resource
{
    protected static ?string $pluralModelLabel = 'Jenis Pembayaran';
    protected static ?string $model = FinanceCategory::class;

    protected static ?string $navigationIcon = 'heroicon-s-banknotes';
    protected static ?string $navigationLabel = 'Jenis Pembayaran';
    protected static ?string $navigationGroup = 'Keuangan Sekolah';
    protected static ?string $modelLabel = 'Jenis Pembayaran';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Pembayaran')
                    ->placeholder('Contoh: Seragam Sekolah')
                    ->required()
                    ->live() // penting untuk trigger update kode saat ketik
                    ->afterStateUpdated(function ($state, callable $set) {
                        $state = ucwords(strtolower($state));
                        $set('name', $state);

                        $words = explode(' ', preg_replace('/[^a-zA-Z\s]/', '', $state));
                        $abbreviation = '';

                        if (count($words) >= 4) {
                            $abbreviation = strtoupper(implode('', array_slice(array_map(fn($w) => substr($w, 0, 1), $words), 0, 4)));
                        } elseif (count($words) >= 2) {
                            $abbreviation = strtoupper(substr($words[0], 0, 2) . substr($words[1], 0, 2));
                        } else {
                            $abbreviation = strtoupper(substr($words[0], 0, 4));
                        }

                        $set('kode', $abbreviation);
                    }),

                TextInput::make('kode')
                    ->label('Kode')
                    ->disabled()
                    ->dehydrated(true)
                    ->required()
                    ->maxLength(4),

                Select::make('type')
                    ->label('Tipe Pembayaran')
                    ->options([
                        'income' => 'Pemasukan',
                        'expense' => 'Pengeluaran',
                    ])
                    ->required(),

                TextInput::make('amount')
                    ->label('Nominal')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Select::make('classroom_ids')
                    ->label('Kelas')
                    ->options(function () {
                        $kelasOptions = \App\Models\Classroom::all()
                            ->mapWithKeys(function ($classroom) {
                                return [
                                    $classroom->id => "{$classroom->kelas} {$classroom->name}",
                                ];
                            })
                            ->toArray();

                        return [0 => 'Semua Kelas'] + $kelasOptions;
                    })
                    ->multiple()
                    ->searchable()
                    ->nullable()
                    ->dehydrated(true)
                    ->dehydrateStateUsing(function ($state) {
                        return in_array(0, $state ?? []) ? null : $state;
                    })
                    ->rules(['nullable', 'array']),

                Select::make('bulan')
                    ->label('Bulan')
                    ->options([
                        'juli' => 'Juli',
                        'agustus' => 'Agustus',
                        'september' => 'September',
                        'oktober' => 'Oktober',
                        'november' => 'November',
                        'desember' => 'Desember',
                        'januari' => 'Januari',
                        'februari' => 'Februari',
                        'maret' => 'Maret',
                        'april' => 'April',
                        'mei' => 'Mei',
                        'juni' => 'Juni',
                    ])
                    ->searchable()
                    ->required(),

                TextInput::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                ->label('No. ')
                ->rowIndex(),
                TextColumn::make('name')
                    ->label('Nama Keuangan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn($state) => $state === 'income' ? 'success' : 'danger')
                    ->formatStateUsing(fn($state) => $state === 'income' ? 'Pemasukan' : 'Pengeluaran')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->searchable()
                    ->sortable()
                    ->money('Rp.'),
                TextColumn::make('kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        $ids = $record->classroom_ids;

                        if (empty($ids)) {
                            return 'Semua Kelas';
                        }

                        $classrooms = \App\Models\Classroom::whereIn('id', $ids)->get();

                        return $classrooms->map(function ($classroom) {
                            return "{$classroom->kelas} {$classroom->name}";
                        })->implode(', ');
                    })
                    ->wrap(),

                TextColumn::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bulan')
                    ->label('Bulan')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->label('Dibuat')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'income' => 'Pemasukan',
                        'expense' => 'Pengeluaran',
                    ]),
                SelectFilter::make('classroom_id')
                    ->label('Kelas')
                    ->options(function () {
                        return [
                            null => 'Semua Kelas',
                        ] + Classroom::all()
                            ->mapWithKeys(function ($classroom) {
                                $label = "{$classroom->kelas} {$classroom->jenjang} {$classroom->name}";
                                return [$classroom->id => $label];
                            })
                            ->toArray();
                    })
                    ->placeholder('Pilih Kelas'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    // ->icon('heroicon-m-pencil')
                    ->tooltip('Edit')
                    ->color('primary'),

                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-m-trash')
                    ->tooltip('Hapus')
                    ->color('danger')
                    ->requiresConfirmation()
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
            'index' => Pages\ListFinanceCategories::route('/'),
            // 'create' => Pages\CreateFinanceCategory::route('/create'),
            // 'edit' => Pages\EditFinanceCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('classroom');
    }
}
