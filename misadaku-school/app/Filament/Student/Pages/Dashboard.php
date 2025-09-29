<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-s-home';
    protected static string $view = 'filament.student.pages.dashboard';

    protected static ?string $title = 'Student Dashboard';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $navigationGroup = null; // biar langsung tampil
}
