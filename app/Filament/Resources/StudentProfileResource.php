<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\StudentProfile;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\StudentProfileResource\Pages;
use App\Filament\Resources\StudentProfileResource\RelationManagers;

class StudentProfileResource extends Resource
{
    protected static ?string $pluralModelLabel = 'Profil Siswa';
    protected static ?string $model = StudentProfile::class;

    protected static ?string $navigationIcon = 'heroicon-s-identification';
    protected static ?string $navigationBadgeTooltip = 'Jumlah Seluruh Siswa';
    protected static ?string $navigationGroup = 'Manajemen Siswa';
    protected static ?string $navigationLabel = 'Profil Siswa';
    protected static ?string $modelLabel = 'Profil Siswa';
    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return (string) StudentProfile::where('is_active', true)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()->schema([
                    FileUpload::make('photo')
                        ->label('Foto Siswa')
                        ->image()
                        ->imageEditor(true)
                        ->directory('student-photos'),
                    Select::make('user_id')
                        ->label('Nama Siswa')
                        ->options(function ($get, $record) {
                            $query = \App\Models\User::query();

                            if ($record) {
                                $query->where('id', $record->user_id)
                                    ->orWhereDoesntHave('studentProfile');
                            } else {
                                $query->whereDoesntHave('studentProfile');
                            }

                            return $query->get()
                                ->mapWithKeys(fn($user) => [
                                    $user->id => ucwords(strtolower($user->name ?? '-'))  // Menambahkan ucwords untuk kapitalisasi
                                ])
                                ->toArray();
                        })
                        ->searchable()
                        ->required(),

                    Select::make('classroom_id')
                        ->label('Kelas')
                        ->relationship('classroom', 'name')
                        ->getOptionLabelFromRecordUsing(fn($record) => $record ? "{$record->kelas}  {$record->name} " : '-'),
                    TextInput::make('nis')
                        ->label('NIS')
                        ->numeric()
                        ->required()
                        ->unique(ignoreRecord: true),
                    Select::make('gender')
                        ->label('Jenis Kelamin')
                        ->options([
                            'L' => 'Laki-laki',
                            'P' => 'Perempuan',
                        ])
                        ->required()
                        ->reactive(),
                    TextInput::make('generation')
                        ->label('Angkatan')
                        ->required(),
                    Repeater::make('parent_phones')
                        ->label('Nomor Orang Tua / Wali')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Nama Wali')
                                ->required(),

                            Forms\Components\TextInput::make('number')
                                ->label('Nomor HP')
                                ->placeholder('08123456789')
                                ->tel()
                                ->required(),
                        ])
                        ->default([])
                        ->columnSpanFull()
                        ->collapsible()
                        ->createItemButtonLabel('Tambah Nomor')
                        ->columns(2),
                    Toggle::make('is_active')->label('Aktif?')->default(true),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->rowIndex(),
                TextColumn::make('classroom.name')
                    ->label('Kelas')
                    ->formatStateUsing(function ($state, $record) {
                        // Ambil data dari relasi classroom
                        $kelas = $record->classroom;
                        if ($kelas) {
                            return "{$kelas->kelas}{$kelas->name}";
                        }
                        return '-'; // Jika tidak ada data classroom
                    })->searchable()->sortable(),
                TextColumn::make('user.name')->label('Nama Siswa')->searchable()->formatStateUsing(fn($state) => Str::title($state))->sortable(),
                TextColumn::make('nis')->sortable()->searchable(),
                TextColumn::make('gender')->label('JK')->searchable()->sortable(),
                TextColumn::make('generation')
                    ->label('Angkatan')
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('photo')->label('Foto')->disk('public')->circular()->height(50),
                BadgeColumn::make('is_active')->label('Status Siswa')->formatStateUsing(fn($state) => $state ? 'Aktif' : 'Tidak')->color(fn($state) => $state ? 'success' : 'danger')->searchable()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('classroom_id')
                    ->label('Kelas')
                    ->relationship('classroom', 'name', fn($query) => $query->orderBy('kelas')->orderBy('name'))
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->kelas} {$record->name}"),
                    

                Tables\Filters\SelectFilter::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ]),
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status Siswa')
                    ->options([
                        true => 'Aktif',
                        false => 'Tidak Aktif',
                    ]),

                Tables\Filters\SelectFilter::make('generation')
                    ->label('Angkatan')
                    ->options(
                        fn() => \App\Models\StudentProfile::query()
                            ->select('generation')
                            ->distinct()
                            ->pluck('generation', 'generation')
                            ->sortDesc()
                    ),

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    // ->icon('heroicon-m-pencil')
                    ->tooltip('Edit')
                    ->color('primary'),
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
            'index' => Pages\ListStudentProfiles::route('/'),
            // 'create' => Pages\CreateStudentProfile::route('/create'),
            // 'edit' => Pages\EditStudentProfile::route('/{record}/edit'),
        ];
    }
}
