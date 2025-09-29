<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Models\User;
use Filament\Actions;
use Illuminate\Support\Str;
use App\Imports\StudentUsersImport;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use App\Filament\Resources\UserResource;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-s-user-plus')
                ->label('Buat Pengguna'),

            Actions\Action::make('bulkCreate')
                ->label('Buat Banyak Pengguna')
                ->icon('heroicon-s-users')
                ->form([
                    Textarea::make('names')
                        ->label('Daftar Nama')
                        ->helperText('Pisahkan dengan koma atau enter. Contoh: Budi, Siti, Andi')
                        ->required(),

                    Select::make('role')
                        ->label('Role')
                        ->options([
                            'admin' => 'Admin',
                            'teacher' => 'Guru',
                            'treasurer' => 'Bendahara',
                            'student' => 'Siswa',
                        ])
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $names = preg_split('/[\r\n,]+/', $data['names']);

                    foreach ($names as $name) {
                        $name = trim($name);
                        if (blank($name)) {
                            continue;
                        }

                        User::create([
                            'name' => $name,
                            'role' => $data['role'],
                            'email' => null,
                            'password' => null,
                        ]);
                    }

                    Notification::make()
                        ->title('Berhasil menambahkan banyak akun!')
                        ->success()
                        ->send();
                }),

        ];
    }
}
