<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Bill;
use App\Models\Expense;
use Filament\Pages\Page;
use App\Models\GeneralIncome;
use App\Models\StudentPayment;
use Filament\Pages\Actions\Action;
use App\Exports\FinanceReportExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GeneralExpenseExport;

class FinanceReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-s-chart-bar';
    protected static ?string $navigationGroup = 'Keuangan Sekolah';
    protected static string $view = 'filament.pages.finance-report';
    protected static ?string $title = 'Laporan Keuangan';
    protected static ?int $navigationSort = 7;

    public $startDate;
    public $endDate;
    public $categories = [];

    public $billTotal;
    public $incomeTotal;
    public $expenseTotal;
    public $balance;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->endOfMonth()->toDateString();
        $this->generateReport();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['startDate', 'endDate'])) {
            $this->generateReport();
        }
    }

    public function generateReport()
    {
        $studentPaymentQuery = StudentPayment::query();
        $generalIncomeQuery = GeneralIncome::query();
        $expenseQuery = Expense::query();
        $billQuery = Bill::query();

        if ($this->startDate) {
            $studentPaymentQuery->whereDate('created_at', '>=', $this->startDate);
            $generalIncomeQuery->whereDate('created_at', '>=', $this->startDate);
            $expenseQuery->whereDate('created_at', '>=', $this->startDate);
            $billQuery->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $studentPaymentQuery->whereDate('created_at', '<=', $this->endDate);
            $generalIncomeQuery->whereDate('created_at', '<=', $this->endDate);
            $expenseQuery->whereDate('created_at', '<=', $this->endDate);
            $billQuery->whereDate('created_at', '<=', $this->endDate);
        }

        if (!empty($this->categories)) {
            $studentPaymentQuery->whereHas('bill', function ($q) {
                $q->whereIn('finance_category_id', $this->categories);
            });
        }

        $incomeFromPayments = $studentPaymentQuery->sum('paid_amount');
        $incomeFromGeneral = $generalIncomeQuery->sum('amount');

        $this->billTotal = $billQuery->sum('amount');   // ✅ total tagihan
        $this->incomeTotal = $incomeFromPayments + $incomeFromGeneral;
        $this->expenseTotal = $expenseQuery->sum('amount');
        $this->balance = $this->incomeTotal - $this->expenseTotal;
    }

    public function exportGeneralExpense()
    {
        return Excel::download(
            new GeneralExpenseExport($this->startDate, $this->endDate),
            'Laporan_Pemasukan_dan_Pengeluaran' . now()->format('Ymd_His') . '.xlsx'
        );
    }
}
