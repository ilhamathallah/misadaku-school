<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Classroom;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ClassroomResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ClassroomResource\RelationManagers;
use Dom\Text;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;

class ClassroomResource extends Resource
{
    protected static ?string $model = Classroom::class;

    protected static ?string $navigationIcon = 'heroicon-s-building-library';
    protected static ?string $navigationGroup = 'Manajemen Siswa';
    protected static ?string $navigationLabel = 'Ruang Kelas';
    protected static ?string $modelLabel = 'Ruang Kelas';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
                Select::make('jenjang')->options([
                    'SD' => 'SD',
                    'MI' => 'MI',
                    'SMP' => 'SMP',
                    'SMA' => 'SMA'
                ])->default('SD')->label('Jenjang')->native(false)->live(),
                TextInput::make('kelas')->required()->label('Kelas')->numeric()
                ->minValue(fn(Get $get) => match ($get('jenjang')) {
                    'SD', 'MI' => 1,
                    'SMP' => 7,
                    'SMA' => 10,
                    default => 1,
                })->maxValue(fn(Get $get) => match ($get('jenjang')) {
                    'SD', 'MI' => 6,
                    'SMP' => 9,
                    'SMA' => 13,
                    default => 1,
                })->default(fn(Get $get) => match ($get('jenjang')) {
                    'SD', 'MI' => 1,
                    'SMP' => 7,
                    'SMA' => 10,
                })->required(),
                TextInput::make('name')->label('Nama Kelas')->maxLength(255)->placeholder('Nama Kelas')->columnSpanFull(),
                Toggle::make('isSMK')->visible(fn(Get $get) => $get('jenjang') === 'SMA')->label('SMK')->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                ->label('No. ')
                ->rowIndex(),
                TextColumn::make('kelas')->label('Kelas')->sortable()->searchable(),
                TextColumn::make('name')->label('Nama Kelas')->sortable()->searchable(),
                TextColumn::make('jenjang')->label('Jenjang')->sortable()->searchable(),
                IconColumn::make('isSMK')->label('SMK')->sortable()->icon(fn(bool $state): string => match ($state) {
                    true => 'heroicon-o-check-circle',
                    false => 'heroicon-o-x-circle',
                })->color(fn(bool $state): string => match ($state) {
                    true => 'success',
                    false => 'danger',
                    default => 'gray',
                }),
                TextColumn::make('created_at')->dateTime('d M y')->label('Dibuat')->searchable()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenjang')
                    ->label('Filter Jenjang')
                    ->options([
                        'SD' => 'SD',
                        'MI' => 'MI',
                        'SMP' => 'SMP',
                        'SMA' => 'SMA',
                    ]),

                // Filter berdasarkan kelas (angka)
                Tables\Filters\SelectFilter::make('kelas')
                    ->label('Filter Kelas')
                    ->options(
                        collect(range(1, 13))->mapWithKeys(fn($v) => [$v => "Kelas $v"])
                    ),

                // Filter berdasarkan nama kelas
                Tables\Filters\SelectFilter::make('name')
                    ->label('Filter Nama Kelas')
                    ->options(
                        fn() => \App\Models\Classroom::query()
                            ->distinct()
                            ->pluck('name', 'name')
                            ->toArray()
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->tooltip('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete'),
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
            'index' => Pages\ListClassrooms::route('/'),
            // 'create' => Pages\CreateClassroom::route('/create'),
            // 'edit' => Pages\EditClassroom::route('/{record}/edit'),
        ];
    }
}
