<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-s-users';
    protected static ?string $navigationLabel = 'Pengguna';
    protected static ?string $modelLabel = 'Pengguna';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),

                Select::make('role')
                    ->label('Role')
                    ->options([
                        'admin' => 'Admin',
                        'teacher' => 'Guru',
                        'treasurer' => 'Bendahara',
                        'student' => 'Siswa',
                    ])
                    ->required()
                    ->reactive(), // penting supaya bisa trigger kondisi

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255)
                    ->nullable()
                    ->required(fn(callable $get) => $get('role') !== 'student') // kalau bukan siswa wajib
                    ->visible(fn(callable $get) => filled($get('role'))), // tampil setelah role dipilih

                TextInput::make('password')
                    ->password()
                    ->label('Password')
                    ->nullable()
                    ->required(
                        fn(string $context, callable $get): bool =>
                        $get('role') !== 'student' && $context === 'create'
                    )
                    ->dehydrateStateUsing(function ($state, callable $get) {
                        if ($get('role') === 'student' && blank($state)) {
                            return Hash::make('123456'); // default password siswa
                        }
                        return filled($state) ? Hash::make($state) : null;
                    })
                    ->dehydrated(fn($state) => filled($state))
                    ->visible(fn(callable $get) => filled($get('role'))), // tampil setelah role dipilih
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
                    ->formatStateUsing(fn($state) => Str::title($state)),
                TextColumn::make('email')->searchable(),
                TextColumn::make('role')
                    ->label('Role')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn($state) => ucfirst($state)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Filter Role')
                    ->options([
                        'admin' => 'Admin',
                        'guest' => 'Tamu',
                        'treasurer' => 'Bendahara',
                        'tatausaha' => 'Tata Usaha',
                        'teacher' => 'Guru',
                        'student' => 'Siswa',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->tooltip('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->tooltip('Hapus'),
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
            'index' => Pages\ListUsers::route('/'),
            // 'create' => Pages\CreateUser::route('/create'),
            // 'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
