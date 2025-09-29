<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class FinanceChart extends ChartWidget
{
    protected static ?string $heading = 'Statistik Keuangan';

    public $startDate;
    public $endDate;

    public $incomeTotal;
    public $expenseTotal;
    public $balance;

    protected function getData(): array
    {
        $labels = [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec'
        ];

        // Ambil pemasukan dari student_payments
        $studentIncomes = DB::table('student_payments')
            ->selectRaw('MONTH(created_at) as month, SUM(total_amount) as total')
            ->groupByRaw('MONTH(created_at)')
            ->pluck('total', 'month');

        // Ambil pemasukan dari general_incomes
        $generalIncomes = DB::table('general_incomes')
            ->selectRaw('MONTH(created_at) as month, SUM(amount) as total')
            ->groupByRaw('MONTH(created_at)')
            ->pluck('total', 'month');

        // Ambil pengeluaran dari expenses
        $expenses = DB::table('expenses')
            ->selectRaw('MONTH(created_at) as month, SUM(amount) as total')
            ->groupByRaw('MONTH(created_at)')
            ->pluck('total', 'month');

        // Ambil tagihan dari bills
        $bills = DB::table('bills')
            ->selectRaw('MONTH(created_at) as month, SUM(amount) as total')
            ->whereNotNull('created_at')
            ->groupByRaw('MONTH(created_at)')
            ->pluck('total', 'month');

        // Inisialisasi array untuk chart
        $incomeData = [];
        $expenseData = [];
        $billData = [];

        // Loop tiap bulan
        for ($i = 1; $i <= 12; $i++) {
            $income = ($studentIncomes[$i] ?? 0) + ($generalIncomes[$i] ?? 0);
            $expense = $expenses[$i] ?? 0;
            $bill = $bills[$i] ?? 0;

            $incomeData[] = $income;
            $expenseData[] = $expense;
            $billData[] = $bill;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'fill' => true,
                    'tension' => 0.4,
                    'data' => $incomeData,
                    'pointRadius' => 0,
                    'backgroundColor' => 'rgba(22, 163, 74, 0.3)', // hijau
                    'borderColor' => 'rgba(22, 163, 74, 1)',       // hijau solid
                ],
                [
                    'label' => 'Pengeluaran',
                    'fill' => true,
                    'tension' => 0.4,
                    'data' => $expenseData,
                    'pointRadius' => 0,
                    'backgroundColor' => 'rgba(220, 38, 38, 0.3)', // merah
                    'borderColor' => 'rgba(220, 38, 38, 1)',       // merah solid
                ],
                [
                    'label' => 'Tagihan',
                    'fill' => true,
                    'tension' => 0.4,
                    'data' => $billData,
                    'pointRadius' => 0,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.3)', // biru
                    'borderColor' => 'rgba(59, 130, 246, 1)',       // biru solid
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
