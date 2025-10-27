<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\TransactionMatchingService;
use Illuminate\Http\Request;

class ReconciliationController extends Controller
{
    protected $matchingService;

    public function __construct(TransactionMatchingService $matchingService)
    {
        $this->matchingService = $matchingService;
    }

    /**
     * Show reconciliation dashboard with duplicate groups
     */
    public function index()
    {
        $duplicateGroups = $this->matchingService->getDuplicateGroups();

        // Get all invoices without memorial_reference (need manual review)
        $unmatchedInvoices = Invoice::whereNull('memorial_reference')
            ->where('source', '!=', 'manual')
            ->latest()
            ->get();

        return view('reconciliation.index', compact('duplicateGroups', 'unmatchedInvoices'));
    }

    /**
     * Manually link two invoices as duplicates
     */
    public function link(Request $request)
    {
        $validated = $request->validate([
            'master_id' => 'required|exists:invoices,id',
            'duplicate_id' => 'required|exists:invoices,id',
        ]);

        $duplicate = Invoice::findOrFail($validated['duplicate_id']);
        $duplicate->update([
            'parent_invoice_id' => $validated['master_id'],
            'is_duplicate' => true,
            'match_confidence' => 50, // Manual link = medium confidence
            'match_notes' => 'Manually linked by user on ' . now()->format('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Facturen succesvol gekoppeld!');
    }

    /**
     * Unlink a duplicate invoice
     */
    public function unlink(Invoice $invoice)
    {
        if (!$invoice->is_duplicate) {
            return redirect()->back()->with('error', 'Deze factuur is geen duplicate.');
        }

        $invoice->update([
            'parent_invoice_id' => null,
            'is_duplicate' => false,
            'match_confidence' => null,
            'match_notes' => null,
        ]);

        return redirect()->back()->with('success', 'Koppeling verwijderd!');
    }
}
