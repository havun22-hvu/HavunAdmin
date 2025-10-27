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
            'currentYear'
        ));
    }
}
