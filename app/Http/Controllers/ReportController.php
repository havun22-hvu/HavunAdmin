<?php

namespace App\Http\Controllers;

use App\Services\TaxExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    protected $taxExportService;

    public function __construct(TaxExportService $taxExportService)
    {
        $this->taxExportService = $taxExportService;
    }

    /**
     * Display the reports page with export options
     */
    public function index()
    {
        // Get list of previously generated exports
        $exports = [];
        if (Storage::disk('local')->exists('exports')) {
            $files = Storage::disk('local')->files('exports');
            foreach ($files as $file) {
                $exports[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => Storage::disk('local')->size($file),
                    'modified' => Storage::disk('local')->lastModified($file),
                ];
            }
            // Sort by modified date, newest first
            usort($exports, function($a, $b) {
                return $b['modified'] - $a['modified'];
            });
        }

        return view('reports.index', [
            'exports' => $exports,
            'currentYear' => date('Y'),
            'currentQuarter' => ceil(date('n') / 3),
        ]);
    }

    /**
     * Export quarterly report (Omzetbelasting aangifte)
     */
    public function exportQuarterly(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'quarter' => 'required|integer|min:1|max:4',
        ]);

        try {
            $filePath = $this->taxExportService->exportQuarterlyReport(
                $request->input('year'),
                $request->input('quarter')
            );

            return response()->download(
                storage_path('app/' . $filePath),
                basename($filePath)
            )->deleteFileAfterSend(false);
        } catch (\Exception $e) {
            return back()->with('error', 'Fout bij het genereren van het kwartaaloverzicht: ' . $e->getMessage());
        }
    }

    /**
     * Export yearly report (Inkomstenbelasting aangifte)
     */
    public function exportYearly(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
        ]);

        try {
            $filePath = $this->taxExportService->exportYearlyReport(
                $request->input('year')
            );

            return response()->download(
                storage_path('app/' . $filePath),
                basename($filePath)
            )->deleteFileAfterSend(false);
        } catch (\Exception $e) {
            return back()->with('error', 'Fout bij het genereren van het jaaroverzicht: ' . $e->getMessage());
        }
    }

    /**
     * Export BTW report (for when BTW becomes applicable)
     */
    public function exportBTW(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'quarter' => 'required|integer|min:1|max:4',
        ]);

        try {
            $filePath = $this->taxExportService->exportBTWReport(
                $request->input('year'),
                $request->input('quarter')
            );

            return response()->download(
                storage_path('app/' . $filePath),
                basename($filePath)
            )->deleteFileAfterSend(false);
        } catch (\Exception $e) {
            return back()->with('error', 'Fout bij het genereren van de BTW aangifte: ' . $e->getMessage());
        }
    }

    /**
     * Download a previously generated export file
     */
    public function download($filename)
    {
        $filePath = 'exports/' . $filename;

        if (!Storage::disk('local')->exists($filePath)) {
            return back()->with('error', 'Bestand niet gevonden.');
        }

        return response()->download(
            storage_path('app/' . $filePath),
            $filename
        );
    }

    /**
     * Delete an export file
     */
    public function delete($filename)
    {
        $filePath = 'exports/' . $filename;

        if (Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
            return back()->with('success', 'Export verwijderd.');
        }

        return back()->with('error', 'Bestand niet gevonden.');
    }
}
