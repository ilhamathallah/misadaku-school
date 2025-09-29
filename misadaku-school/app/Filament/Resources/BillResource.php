<?php

namespace App\Filament\Resources;

use Dom\Text;
use Filament\Forms;
use App\Models\Bill;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BillResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BillResource\RelationManagers;

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
                    ->helperText(new HtmlString('Pastikan nama tagihan <strong>akurat</strong>'))
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
                        return \App\Models\FinanceCategory::where('type', 'income')
                            ->with('classroom')
                            ->get()
                            ->mapWithKeys(function ($category) {
                                $classroom = $category->classroom;
                                $classroomName = $classroom
                                    ? Str::title("{$classroom->kelas} {$classroom->name}")
                                    : 'Semua Kelas';

                                return [
                                    $category->id => Str::title("{$category->name} - {$classroomName}")
                                ];
                            })
                            ->toArray();
                    })
                    ->multiple()
                    ->searchable(),

                TextInput::make('amount')
                    ->label('Total Pembayaran')
                    ->numeric()
                    ->live()
                    ->reactive()
                    ->disabled()
                    ->dehydrated()
                    ->required(),

                Toggle::make('status')
                    ->label('Pembayaran Lunas')
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
                        return Str::title("{$kelas} {$namaKelas}");
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nama_tagihan')
                    ->label('Nama Tagihan')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn($state) => Str::title($state)),

                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->sortable()
                    // ->searchable()
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
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state ? 'Lunas' : 'Belum Lunas')
                    ->color(fn($state) => $state ? 'success' : 'danger'),

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
                        1 => 'Lunas',
                        0 => 'Belum Lunas',
                    ]),

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
                Tables\Actions\ViewAction::make()
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBills::route('/'),
            // 'create' => Pages\CreateBill::route('/create'),
            // 'edit' => Pages\EditBill::route('/{record}/edit'),
        ];
    }
}
