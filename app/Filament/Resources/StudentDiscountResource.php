<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\StudentDiscount;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\StudentDiscountResource\Pages;
use App\Filament\Resources\StudentDiscountResource\RelationManagers;

class StudentDiscountResource extends Resource
{
    protected static ?string $pluralModelLabel = 'Diskon Siswa';
    protected static ?string $model = StudentDiscount::class;

    protected static ?string $navigationGroup = 'Keuangan Sekolah';
    protected static ?string $navigationIcon = 'heroicon-s-receipt-percent';
    protected static ?string $navigationLabel = 'Diskon Siswa';
    protected static ?string $modelLabel = 'Diskon Siswa';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Diskon')
                    ->required()
                    ->maxLength(255),

                TextInput::make('percentage')
                    ->label('Jumlah Diskon (%)')
                    ->numeric()
                    ->suffix('%')
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
                    ->label('Jenis Diskon')
                    ->formatStateUsing(fn($state) => Str::title($state))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('percentage')
                    ->label('Diskon (%)')
                    ->suffix('%')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->date()
                    ->label('Waktu')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
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
                    // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListStudentDiscounts::route('/'),
            // 'create' => Pages\CreateStudentDiscount::route('/create'),
            // 'edit' => Pages\EditStudentDiscount::route('/{record}/edit'),
        ];
    }
}
