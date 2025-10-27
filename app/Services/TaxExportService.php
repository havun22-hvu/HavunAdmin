<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class TaxExportService
{
    /**
     * Export quarterly report for tax purposes (Omzetbelasting aangifte)
     */
    public function exportQuarterlyReport(int $year, int $quarter): string
    {
        $startDate = $this->getQuarterStartDate($year, $quarter);
        $endDate = $this->getQuarterEndDate($year, $quarter);

        // Get all paid invoices (income)
        $invoices = Invoice::where('type', 'income')
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->with(['project', 'customer'])
            ->orderBy('invoice_date')
            ->get();

        // Get all paid expenses
        $expenses = Invoice::where('type', 'expense')
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->with(['category', 'supplier', 'project'])
            ->orderBy('invoice_date')
            ->get();

        // Calculate totals
        $totalRevenue = $invoices->sum('total');
        $totalExpenses = $expenses->sum('total');
        $profit = $totalRevenue - $totalExpenses;

        // Revenue by project
        $revenueByProject = $invoices->groupBy('project.name')->map(function ($items) {
            return $items->sum('total');
        });

        // Create CSV content
        $filename = storage_path("app/exports/belastingdienst_Q{$quarter}_{$year}.csv");

        // Ensure directory exists
        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0755, true);
        }

        $file = fopen($filename, 'w');

        // Add BOM for proper Excel UTF-8 handling
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header
        fputcsv($file, ["Havun - Kwartaaloverzicht Q{$quarter} {$year}"], ';');
        fputcsv($file, ["Gegenereerd op: " . now()->format('d-m-Y H:i')], ';');
        fputcsv($file, [], ';'); // Empty line

        // Summary
        fputcsv($file, ['=== SAMENVATTING ==='], ';');
        fputcsv($file, ['Periode', "Q{$quarter} {$year} ({$startDate->format('d-m-Y')} t/m {$endDate->format('d-m-Y')})"], ';');
        fputcsv($file, ['Totale Omzet', '€ ' . number_format($totalRevenue, 2, ',', '.')], ';');
        fputcsv($file, ['Totale Kosten', '€ ' . number_format($totalExpenses, 2, ',', '.')], ';');
        fputcsv($file, ['Winst', '€ ' . number_format($profit, 2, ',', '.')], ';');
        fputcsv($file, [], ';');

        // Revenue by project
        fputcsv($file, ['=== OMZET PER PROJECT ==='], ';');
        foreach ($revenueByProject as $projectName => $total) {
            fputcsv($file, [$projectName ?: 'Geen project', '€ ' . number_format($total, 2, ',', '.')], ';');
        }
        fputcsv($file, [], ';');

        // Detailed invoices
        fputcsv($file, ['=== INKOMSTEN (DETAIL) ==='], ';');
        fputcsv($file, ['Factuurnummer', 'Datum', 'Klant', 'Project', 'Bedrag (excl. BTW)', 'BTW', 'Totaal (incl. BTW)', 'Status'], ';');

        foreach ($invoices as $invoice) {
            fputcsv($file, [
                $invoice->invoice_number,
                $invoice->invoice_date->format('d-m-Y'),
                $invoice->customer ? $invoice->customer->name : 'Geen klant',
                $invoice->project ? $invoice->project->name : 'Geen project',
                '€ ' . number_format($invoice->subtotal, 2, ',', '.'),
                '€ ' . number_format($invoice->vat_amount, 2, ',', '.'),
                '€ ' . number_format($invoice->total, 2, ',', '.'),
                ucfirst($invoice->status),
            ], ';');
        }
        fputcsv($file, [], ';');

        // Detailed expenses
        fputcsv($file, ['=== UITGAVEN (DETAIL) ==='], ';');
        fputcsv($file, ['Factuurnummer', 'Datum', 'Leverancier', 'Categorie', 'Project', 'Bedrag (excl. BTW)', 'BTW', 'Totaal (incl. BTW)', 'Status'], ';');

        foreach ($expenses as $expense) {
            fputcsv($file, [
                $expense->invoice_number,
                $expense->invoice_date->format('d-m-Y'),
                $expense->supplier ? $expense->supplier->name : 'Geen leverancier',
                $expense->category ? $expense->category->name : 'Geen categorie',
                $expense->project ? $expense->project->name : 'Algemeen',
                '€ ' . number_format($expense->subtotal, 2, ',', '.'),
                '€ ' . number_format($expense->vat_amount, 2, ',', '.'),
                '€ ' . number_format($expense->total, 2, ',', '.'),
                ucfirst($expense->status),
            ], ';');
        }

        fclose($file);

        return $filename;
    }

    /**
     * Export yearly report for tax purposes (Inkomstenbelasting aangifte)
     */
    public function exportYearlyReport(int $year): string
    {
        $startDate = "{$year}-01-01";
        $endDate = "{$year}-12-31";

        // Get all paid invoices
        $invoices = Invoice::where('type', 'income')
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->with(['project', 'customer'])
            ->orderBy('invoice_date')
            ->get();

        // Get all paid expenses
        $expenses = Invoice::where('type', 'expense')
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->with(['category', 'supplier', 'project'])
            ->orderBy('invoice_date')
            ->get();

        // Calculate totals
        $totalRevenue = $invoices->sum('total');
        $totalExpenses = $expenses->sum('total');
        $profit = $totalRevenue - $totalExpenses;

        // Revenue by project
        $revenueByProject = $invoices->groupBy('project.name')->map(function ($items) {
            return $items->sum('total');
        });

        // Expenses by category
        $expensesByCategory = $expenses->groupBy('category.name')->map(function ($items) {
            return $items->sum('total');
        });

        // Revenue per quarter
        $revenuePerQuarter = [];
        $expensesPerQuarter = [];
        for ($q = 1; $q <= 4; $q++) {
            $qStart = $this->getQuarterStartDate($year, $q);
            $qEnd = $this->getQuarterEndDate($year, $q);

            $revenuePerQuarter["Q{$q}"] = Invoice::where('status', 'paid')
                ->whereBetween('invoice_date', [$qStart, $qEnd])
                ->sum('total');

            $expensesPerQuarter["Q{$q}"] = Expense::where('status', 'paid')
                ->whereBetween('invoice_date', [$qStart, $qEnd])
                ->sum('total');
        }

        // Create CSV
        $filename = storage_path("app/exports/belastingdienst_jaaroverzicht_{$year}.csv");

        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0755, true);
        }

        $file = fopen($filename, 'w');
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM

        // Header
        fputcsv($file, ["Havun - Jaaroverzicht {$year}"], ';');
        fputcsv($file, ["Voor Inkomstenbelasting Aangifte"], ';');
        fputcsv($file, ["Gegenereerd op: " . now()->format('d-m-Y H:i')], ';');
        fputcsv($file, [], ';');

        // Company info
        fputcsv($file, ['=== BEDRIJFSGEGEVENS ==='], ';');
        fputcsv($file, ['Bedrijfsnaam', config('app.company_name', 'Havun')], ';');
        fputcsv($file, ['KvK nummer', env('COMPANY_KVK', '98516000')], ';');
        fputcsv($file, ['BTW-id', env('COMPANY_BTW_ID', 'NL002995910B70')], ';');
        fputcsv($file, ['Omzetbelasting nr', env('COMPANY_TAX_NUMBER', '195200305B01')], ';');
        fputcsv($file, [], ';');

        // Summary
        fputcsv($file, ['=== JAAROVERZICHT ==='], ';');
        fputcsv($file, ['Boekjaar', $year], ';');
        fputcsv($file, ['Totale Omzet', '€ ' . number_format($totalRevenue, 2, ',', '.')], ';');
        fputcsv($file, ['Totale Kosten', '€ ' . number_format($totalExpenses, 2, ',', '.')], ';');
        fputcsv($file, ['Winst', '€ ' . number_format($profit, 2, ',', '.')], ';');
        fputcsv($file, [], ';');

        // Per quarter
        fputcsv($file, ['=== OMZET PER KWARTAAL ==='], ';');
        foreach ($revenuePerQuarter as $quarter => $amount) {
            fputcsv($file, [$quarter, '€ ' . number_format($amount, 2, ',', '.')], ';');
        }
        fputcsv($file, [], ';');

        fputcsv($file, ['=== KOSTEN PER KWARTAAL ==='], ';');
        foreach ($expensesPerQuarter as $quarter => $amount) {
            fputcsv($file, [$quarter, '€ ' . number_format($amount, 2, ',', '.')], ';');
        }
        fputcsv($file, [], ';');

        // Revenue by project
        fputcsv($file, ['=== OMZET PER PROJECT ==='], ';');
        foreach ($revenueByProject as $projectName => $total) {
            fputcsv($file, [$projectName ?: 'Geen project', '€ ' . number_format($total, 2, ',', '.')], ';');
        }
        fputcsv($file, [], ';');

        // Expenses by category
        fputcsv($file, ['=== KOSTEN PER CATEGORIE ==='], ';');
        foreach ($expensesByCategory as $categoryName => $total) {
            fputcsv($file, [$categoryName ?: 'Geen categorie', '€ ' . number_format($total, 2, ',', '.')], ';');
        }
        fputcsv($file, [], ';');

        // All invoices
        fputcsv($file, ['=== ALLE INKOMSTEN ==='], ';');
        fputcsv($file, ['Factuurnummer', 'Datum', 'Klant', 'Project', 'Bedrag (excl. BTW)', 'BTW', 'Totaal (incl. BTW)', 'Betaald op'], ';');

        foreach ($invoices as $invoice) {
            fputcsv($file, [
                $invoice->invoice_number,
                $invoice->invoice_date->format('d-m-Y'),
                $invoice->customer ? $invoice->customer->name : '-',
                $invoice->project ? $invoice->project->name : '-',
                '€ ' . number_format($invoice->subtotal, 2, ',', '.'),
                '€ ' . number_format($invoice->vat_amount, 2, ',', '.'),
                '€ ' . number_format($invoice->total, 2, ',', '.'),
                $invoice->payment_date ? $invoice->payment_date->format('d-m-Y') : '-',
            ], ';');
        }
        fputcsv($file, [], ';');

        // All expenses
        fputcsv($file, ['=== ALLE UITGAVEN ==='], ';');
        fputcsv($file, ['Factuurnummer', 'Datum', 'Leverancier', 'Categorie', 'Project', 'Bedrag (excl. BTW)', 'BTW', 'Totaal (incl. BTW)', 'Betaald op'], ';');

        foreach ($expenses as $expense) {
            fputcsv($file, [
                $expense->invoice_number,
                $expense->invoice_date->format('d-m-Y'),
                $expense->supplier ? $expense->supplier->name : '-',
                $expense->category ? $expense->category->name : '-',
                $expense->project ? $expense->project->name : 'Algemeen',
                '€ ' . number_format($expense->subtotal, 2, ',', '.'),
                '€ ' . number_format($expense->vat_amount, 2, ',', '.'),
                '€ ' . number_format($expense->total, 2, ',', '.'),
                $expense->payment_date ? $expense->payment_date->format('d-m-Y') : '-',
            ], ';');
        }

        fclose($file);

        return $filename;
    }

    /**
     * Export BTW report (for when BTW becomes applicable)
     */
    public function exportBTWReport(int $year, int $quarter): string
    {
        $startDate = $this->getQuarterStartDate($year, $quarter);
        $endDate = $this->getQuarterEndDate($year, $quarter);

        // BTW on revenue (verschuldigde omzetbelasting)
        $invoices = Invoice::where('type', 'income')
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->get();

        $btwOnRevenue = $invoices->sum('vat_amount');

        // BTW on expenses (voorbelasting)
        $expenses = Invoice::where('type', 'expense')
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->get();

        $btwOnExpenses = $expenses->sum('vat_amount');

        // To pay to Belastingdienst
        $btwToPay = $btwOnRevenue - $btwOnExpenses;

        // Create CSV
        $filename = storage_path("app/exports/btw_aangifte_Q{$quarter}_{$year}.csv");

        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0755, true);
        }

        $file = fopen($filename, 'w');
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($file, ["Havun - BTW Aangifte Q{$quarter} {$year}"], ';');
        fputcsv($file, ["Gegenereerd op: " . now()->format('d-m-Y H:i')], ';');
        fputcsv($file, [], ';');

        fputcsv($file, ['=== BTW BEREKENING ==='], ';');
        fputcsv($file, ['Periode', "Q{$quarter} {$year}"], ';');
        fputcsv($file, ['BTW op omzet (verschuldigd)', '€ ' . number_format($btwOnRevenue, 2, ',', '.')], ';');
        fputcsv($file, ['BTW op kosten (voorbelasting)', '€ ' . number_format($btwOnExpenses, 2, ',', '.')], ';');
        fputcsv($file, ['Te betalen aan Belastingdienst', '€ ' . number_format($btwToPay, 2, ',', '.')], ';');
        fputcsv($file, [], ';');

        // Detailed revenue with BTW
        fputcsv($file, ['=== INKOMSTEN MET BTW ==='], ';');
        fputcsv($file, ['Factuurnummer', 'Datum', 'Klant', 'Bedrag excl. BTW', 'BTW %', 'BTW bedrag', 'Totaal'], ';');

        foreach ($invoices as $invoice) {
            fputcsv($file, [
                $invoice->invoice_number,
                $invoice->invoice_date->format('d-m-Y'),
                $invoice->customer ? $invoice->customer->name : '-',
                '€ ' . number_format($invoice->subtotal, 2, ',', '.'),
                $invoice->vat_percentage . '%',
                '€ ' . number_format($invoice->vat_amount, 2, ',', '.'),
                '€ ' . number_format($invoice->total, 2, ',', '.'),
            ], ';');
        }
        fputcsv($file, [], ';');

        // Detailed expenses with BTW
        fputcsv($file, ['=== UITGAVEN MET BTW (VOORBELASTING) ==='], ';');
        fputcsv($file, ['Factuurnummer', 'Datum', 'Leverancier', 'Bedrag excl. BTW', 'BTW %', 'BTW bedrag', 'Totaal'], ';');

        foreach ($expenses as $expense) {
            fputcsv($file, [
                $expense->invoice_number,
                $expense->invoice_date->format('d-m-Y'),
                $expense->supplier ? $expense->supplier->name : '-',
                '€ ' . number_format($expense->subtotal, 2, ',', '.'),
                $expense->vat_percentage . '%',
                '€ ' . number_format($expense->vat_amount, 2, ',', '.'),
                '€ ' . number_format($expense->total, 2, ',', '.'),
            ], ';');
        }

        fclose($file);

        return $filename;
    }

    private function getQuarterStartDate(int $year, int $quarter): \Carbon\Carbon
    {
        $month = ($quarter - 1) * 3 + 1;
        return \Carbon\Carbon::create($year, $month, 1)->startOfDay();
    }

    private function getQuarterEndDate(int $year, int $quarter): \Carbon\Carbon
    {
        $month = $quarter * 3;
        return \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
    }
}
