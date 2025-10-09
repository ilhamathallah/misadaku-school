<?php

namespace App\Filament\Treasurer\Resources;

use Filament\Forms;
use App\Models\Bill;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\Classroom;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\FinanceCategory;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BillResource\Pages;

class BillResource extends Resource
{
    protected static ?string $pluralModelLabel = 'Tagihan Siswa';
    protected static ?string $model = Bill::class;

    protected static ?string $navigationIcon = 'heroicon-s-credit-card'; // Ikon untuk Tagihan
    protected static ?string $navigationLabel = 'Tagihan Siswa';
    protected static ?string $modelLabel = 'Tagihan';
    protected static ?string $navigationGroup = 'Keuangan Sekolah';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_tagihan')
                    ->required()
                    ->label('Nama Tagihan')
                    ->helperText('Pastikan nama tagihan akurat')
                    ->reactive()
                    ->afterStateUpdated(fn($state, Set $set) => $set('nama_tagihan', Str::title($state))),

                Select::make('student_profile_id')
                    ->label('Siswa')
                    ->options(
                        \App\Models\StudentProfile::with(['user', 'classroom'])
                            ->get()
                            ->mapWithKeys(function ($profile) {
                                if (!$profile->user) {
                                    return [];
                                }
                                $kelas = $profile->classroom->kelas ?? '-';
                                $namaKelas = $profile->classroom->name ?? '-';
                                $name = Str::title($profile->user->name);
                                return [
                                    $profile->id => "{$name} - {$kelas} {$namaKelas}"
                                ];
                            })
                            ->toArray()
                    )
                    ->searchable()
                    ->required(),

                DatePicker::make('tanggal_jatuh_tempo')
                    ->label('Tanggal Jatuh Tempo')
                    ->native(false)
                    ->required(),

                Select::make('category_ids')
                    ->label('Kategori Pemasukan')
                    ->options(function () {
                        return FinanceCategory::where('type', 'income')
                            ->get()
                            ->mapWithKeys(function ($category) {
                                // classroom_ids bentuknya array (json)
                                $ids = is_array($category->classroom_ids)
                                    ? $category->classroom_ids
                                    : (json_decode($category->classroom_ids, true) ?? []);

                                // ambil nama kelas dari ids
                                $classroomNames = Classroom::whereIn('id', $ids)
                                    ->get()
                                    ->map(fn($c) => "{$c->kelas} {$c->name}")
                                    ->implode(', ');

                                return [
                                    $category->id => Str::title("{$category->name} - {$classroomNames}"),
                                ];
                            })
                            ->toArray();
                    })
                    ->multiple()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $totalAmount = FinanceCategory::whereIn('id', $state)->sum('amount');
                        $set('amount', $totalAmount);
                    }),

                TextInput::make('amount')
                    ->label('Total Pembayaran')
                    ->numeric()
                    ->disabled() // Disable the field to prevent manual input
                    ->dehydrated()
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
                TextColumn::make('studentProfile.user.name')
                    ->label('Siswa')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn($state) => Str::title($state)),

                TextColumn::make('studentProfile.classroom_id')
                    ->label('Kelas')
                    ->getStateUsing(function ($record) {
                        $classroom = $record->studentProfile?->classroom;
                        if (!$classroom) {
                            return '-';
                        }
                        $kelas = $classroom->kelas;
                        $namaKelas = $classroom->name;
                        return Str::title("{$kelas}{$namaKelas}");
                    })
                    ->sortable()
                    ->searchable(query: function (Builder $query, string $search) {
                        // biar search bisa lewat nama kelas
                        $query->whereHas('studentProfile.classroom', function ($q) use ($search) {
                            $q->where('kelas', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('nama_tagihan')
                    ->label('Nama Tagihan')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn($state) => Str::title($state)),

                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->getStateUsing(
                        fn($record) =>
                        \App\Models\FinanceCategory::whereIn('id', $record->category_ids)
                            ->pluck('name')
                            ->map(fn($name) => Str::title($name))
                            ->implode(', ')
                    ),

                TextColumn::make('amount')
                    ->label('Total Pembayaran')
                    ->searchable()
                    ->money('Rp.'),

                TextColumn::make('status')
                    ->label('Status Pembayaran')
                    ->badge()
                    ->sortable()
                    ->getStateUsing(fn($record) => $record->status) // ambil accessor, bukan field database
                    ->color(fn($state) => match ($state) {
                        'Lunas' => 'success',
                        'Kurang' => 'warning',
                        'Belum Lunas' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Tanggal Jatuh Tempo')
                    ->sortable()
                    ->searchable()
                    ->date(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Pembayaran')
                    ->options([
                        'Belum Lunas' => 'Belum Lunas',
                        'Kurang' => 'Kurang',
                        'Lunas' => 'Lunas',
                    ]),

                Tables\Filters\SelectFilter::make('classroom_id')
                    ->label('Kelas')
                    ->options(
                        \App\Models\Classroom::all()->mapWithKeys(fn($c) => [
                            $c->id => "{$c->kelas}{$c->name}"
                        ])->toArray()
                    )
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('studentProfile.classroom', function ($q) use ($data) {
                                $q->where('id', $data['value']);
                            });
                        }
                    }),

                Tables\Filters\SelectFilter::make('category_ids')
                    ->label('Kategori')
                    ->multiple()
                    ->options(
                        \App\Models\FinanceCategory::pluck('name', 'id')->toArray()
                    )
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['values']) && is_array($data['values'])) {
                            // Cek apakah ada ID kategori di category_ids JSON column
                            $query->where(function ($q) use ($data) {
                                foreach ($data['values'] as $id) {
                                    $q->orWhereJsonContains('category_ids', (int) $id);
                                }
                            });
                        }
                    }),
                Filter::make('periode')
                    ->label('Periode')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),

            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->tooltip('Riwayat')
                    ->icon('heroicon-o-clock')
                    ->color('info'),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->tooltip('Hapus')
                    ->icon('heroicon-s-trash')
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBills::route('/'),
        ];
    }
}
