<?php

namespace App\Filament\Treasurer\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\GeneralIncome;
use Filament\Resources\Resource;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\GeneralIncomeResource\Pages;
use App\Filament\Resources\GeneralIncomeResource\RelationManagers;

class GeneralIncomeResource extends Resource
{
    protected static ?string $pluralModelLabel = 'Pemasukan Umum';
    protected static ?string $model = GeneralIncome::class;

    protected static ?string $navigationIcon = 'heroicon-s-arrow-trending-up';
    protected static ?string $navigationLabel = 'Pemasukan non tagihan';
    protected static ?string $navigationGroup = 'Keuangan Sekolah';
    protected static ?string $modelLabel = 'Pemasukan';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Pemasukan')
                    ->required()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('name', ucfirst($state)); // bikin huruf pertama kapital
                    }),
                DatePicker::make('income_date')
                    ->label('Tanggal')
                    ->native(false)
                    ->required(),
                TextInput::make('amount')
                    ->label('Jumlah')
                    ->numeric()
                    ->prefix('Rp.')
                    ->required(),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3),
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
                    ->searchable()
                    ->sortable()
                    ->label('Nama'),
                TextColumn::make('amount')
                    ->money('Rp.')
                    ->searchable()
                    ->sortable()
                    ->label('Jumlah'),
                TextColumn::make('income_date')
                    ->date()
                    ->searchable()
                    ->sortable()
                    ->label('Waktu'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->icon('heroicon-s-eye')
                    ->color('info')
                    ->tooltip('Detail'),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListGeneralIncomes::route('/'),
            // 'create' => Pages\CreateGeneralIncome::route('/create'),
            // 'edit' => Pages\EditGeneralIncome::route('/{record}/edit'),
        ];
    }
}
