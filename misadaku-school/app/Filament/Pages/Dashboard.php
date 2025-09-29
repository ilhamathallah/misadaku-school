<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Filament\Widgets\FinanceChart;
use App\Filament\Widgets\StudentsPerClassChart;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-s-home';
    protected static string $view = 'filament.pages.custom-dashboard';

    public function getHeading(): string
    {
        return 'Dashboard';
    }

    public function getUser()
    {
        return Auth::user();
    }

    public function getWidgets(): array {
        return [
            FinanceChart::class,
            StudentsPerClassChart::class,
        ];
    }
}
