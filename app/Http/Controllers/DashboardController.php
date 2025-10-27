<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $currentYear = now()->year;
        $currentQuarter = now()->quarter;
        $currentMonth = now()->month;

        // Year-to-date statistics
        $ytdRevenue = Invoice::income()
            ->paid()
            ->whereYear('invoice_date', $currentYear)
            ->sum('total');

        $ytdExpenses = Invoice::expense()
            ->paid()
            ->whereYear('invoice_date', $currentYear)
            ->sum('total');

        $ytdProfit = $ytdRevenue - $ytdExpenses;

        // Current quarter statistics
        $quarterRevenue = Invoice::income()
            ->paid()
            ->forQuarter($currentYear, $currentQuarter)
            ->sum('total');

        $quarterExpenses = Invoice::expense()
            ->paid()
            ->forQuarter($currentYear, $currentQuarter)
            ->sum('total');

        $quarterProfit = $quarterRevenue - $quarterExpenses;

        // Current month statistics
        $monthRevenue = Invoice::income()
            ->paid()
            ->whereYear('invoice_date', $currentYear)
            ->whereMonth('invoice_date', $currentMonth)
            ->sum('total');

        $monthExpenses = Invoice::expense()
            ->paid()
            ->whereYear('invoice_date', $currentYear)
            ->whereMonth('invoice_date', $currentMonth)
            ->sum('total');

        $monthProfit = $monthRevenue - $monthExpenses;

        // Revenue per project (YTD)
        $revenueByProject = Invoice::income()
            ->paid()
            ->whereYear('invoice_date', $currentYear)
            ->select('project_id', DB::raw('SUM(total) as total'))
            ->groupBy('project_id')
            ->with('project')
            ->get()
            ->map(function ($item) {
                return [
                    'project' => $item->project ? $item->project->name : 'Geen project',
                    'total' => $item->total,
                    'color' => $item->project ? $item->project->color : '#95A5A6',
                ];
            });

        // Expenses by category (YTD)
        $expensesByCategory = Invoice::expense()
            ->paid()
            ->whereYear('invoice_date', $currentYear)
            ->select('category_id', DB::raw('SUM(total) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category ? $item->category->name : 'Geen categorie',
                    'total' => $item->total,
                    'color' => $item->category ? $item->category->color : '#95A5A6',
                ];
            });

        // Recent invoices
        $recentInvoices = Invoice::income()
            ->with(['customer', 'project'])
            ->latest('invoice_date')
            ->take(5)
            ->get();

        // Recent expenses
        $recentExpenses = Invoice::expense()
            ->with(['supplier', 'category'])
            ->latest('invoice_date')
            ->take(5)
            ->get();

        // Unpaid invoices count
        $unpaidInvoicesCount = Invoice::income()->unpaid()->count();
        $unpaidInvoicesTotal = Invoice::income()->unpaid()->sum('total');

        // Overdue invoices
        $overdueInvoicesCount = Invoice::income()->overdue()->count();
        $overdueInvoicesTotal = Invoice::income()->overdue()->sum('total');

        // Chart 1: Monthly revenue (current year, per month)
        $monthlyRevenue = $this->getMonthlyRevenue($currentYear);

        // Chart 2: Revenue by project (already have: $revenueByProject)

        // Chart 3: Income vs Expenses per month (line chart)
        $monthlyIncomeVsExpenses = $this->getMonthlyIncomeVsExpenses($currentYear);

        // Chart 4: Expenses by category (already have: $expensesByCategory)

        // Chart 5: Monthly profit (area chart)
        $monthlyProfit = $this->getMonthlyProfit($currentYear);

        // Chart 6: Year-over-year comparison
        $yearOverYear = $this->getYearOverYearComparison($currentYear);

        return view('dashboard', compact(
            'ytdRevenue',
            'ytdExpenses',
            'ytdProfit',
            'quarterRevenue',
            'quarterExpenses',
            'quarterProfit',
            'monthRevenue',
            'monthExpenses',
            'monthProfit',
            'revenueByProject',
            'expensesByCategory',
            'recentInvoices',
            'recentExpenses',
            'unpaidInvoicesCount',
            'unpaidInvoicesTotal',
            'overdueInvoicesCount',
            'overdueInvoicesTotal',
            'currentYear',
            'monthlyRevenue',
            'monthlyIncomeVsExpenses',
            'monthlyProfit',
            'yearOverYear'
        ));
    }

    /**
     * Get monthly revenue for current year
     */
    private function getMonthlyRevenue(int $year): array
    {
        $data = Invoice::income()
            ->paid()
            ->whereYear('invoice_date', $year)
            ->selectRaw("MONTH(invoice_date) as month, SUM(total) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month');

        // Fill in missing months with 0
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = $data->get($i, 0);
        }

        return $months;
    }

    /**
     * Get monthly income vs expenses
     */
    private function getMonthlyIncomeVsExpenses(int $year): array
    {
        $income = Invoice::income()
            ->paid()
            ->whereYear('invoice_date', $year)
            ->selectRaw("MONTH(invoice_date) as month, SUM(total) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month');

        $expenses = Invoice::expense()
            ->paid()
            ->whereYear('invoice_date', $year)
            ->selectRaw("MONTH(invoice_date) as month, SUM(total) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month');

        $incomeData = [];
        $expensesData = [];
        for ($i = 1; $i <= 12; $i++) {
            $incomeData[] = $income->get($i, 0);
            $expensesData[] = $expenses->get($i, 0);
        }

        return [
            'income' => $incomeData,
            'expenses' => $expensesData,
        ];
    }

    /**
     * Get monthly profit
     */
    private function getMonthlyProfit(int $year): array
    {
        $income = Invoice::income()
            ->paid()
            ->whereYear('invoice_date', $year)
            ->selectRaw("MONTH(invoice_date) as month, SUM(total) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month');

        $expenses = Invoice::expense()
            ->paid()
            ->whereYear('invoice_date', $year)
            ->selectRaw("MONTH(invoice_date) as month, SUM(total) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month');

        $profit = [];
        for ($i = 1; $i <= 12; $i++) {
            $profit[] = ($income->get($i, 0) - $expenses->get($i, 0));
        }

        return $profit;
    }

    /**
     * Get year-over-year comparison per quarter
     */
    private function getYearOverYearComparison(int $currentYear): array
    {
        $previousYear = $currentYear - 1;

        $currentYearData = [];
        $previousYearData = [];

        for ($q = 1; $q <= 4; $q++) {
            $current = Invoice::income()
                ->paid()
                ->forQuarter($currentYear, $q)
                ->sum('total');

            $previous = Invoice::income()
                ->paid()
                ->forQuarter($previousYear, $q)
                ->sum('total');

            $currentYearData[] = $current;
            $previousYearData[] = $previous;
        }

        return [
            'currentYear' => $currentYearData,
            'previousYear' => $previousYearData,
            'currentYearLabel' => $currentYear,
            'previousYearLabel' => $previousYear,
        ];
    }
}
