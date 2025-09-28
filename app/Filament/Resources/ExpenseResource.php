<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Expense;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\FinanceCategory;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ExpenseResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ExpenseResource\RelationManagers;

class ExpenseResource extends Resource
{
    protected static ?string $pluralModelLabel = 'Dana Pengeluaran';
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-s-arrow-trending-down';
    protected static ?string $navigationLabel = 'Pengeluaran';
    protected static ?string $navigationGroup = 'Keuangan Sekolah';
    protected static ?string $modelLabel = 'Pengeluaran';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Grid::make()
                
                    ->schema([

                        Select::make('finance_category_id')
                            ->label('Kategori Pengeluaran (Opsional)')
                            ->options(FinanceCategory::where('type', 'pengeluaran')->pluck('name', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $category = FinanceCategory::find($state);
                                    $set('custom_category_name', null);

                                    // Jika kategori ditemukan, set amount-nya
                                    if ($category) {
                                        $set('amount', $category->amount); // <- asumsi field-nya `amount`
                                    }
                                }
                            }),

                        TextInput::make('custom_category_name')
                            ->label('Pengeluaran Manual (Buat Data Pengeluaran Manual)')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('finance_category_id', null);
                                    $set('amount', null); // reset amount karena bukan pakai kategori
                                }
                            }),

                        TextInput::make('amount')
                            ->label('Jumlah')
                            ->numeric()
                            ->prefix('Rp.')
                            ->required(),

                        DatePicker::make('expense_date')->default(now())
                            ->native(false)->required()->label('Tanggal Pengeluaran'),

                        Textarea::make('note')
                            ->label('Catatan')
                            ->rows(3)
                            ->nullable(),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                ->label('No. ')
                ->rowIndex(),
                TextColumn::make('kategori')
                    ->label('Nama')
                    ->getStateUsing(
                        fn($record) =>
                        $record->financeCategory?->name ?? $record->custom_category_name ?? '-'
                    )
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => Str::title($state)),

                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('Rp.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('expense_date')
                    ->label('Waktu')
                    ->searchable()
                    ->sortable()
                    ->date(),
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
            'index' => Pages\ListExpenses::route('/'),
            // 'create' => Pages\CreateExpense::route('/create'),
            // 'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
