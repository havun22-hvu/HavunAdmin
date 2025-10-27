<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::income()
            ->with(['customer', 'project'])
            ->latest('invoice_date');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->paginate(20)->withQueryString();
        $projects = Project::active()->get();

        return view('invoices.index', compact('invoices', 'projects'));
    }

    public function create()
    {
        $projects = Project::active()->get();
        $customers = Customer::active()->get();
        $nextInvoiceNumber = $this->generateInvoiceNumber();

        return view('invoices.create', compact('projects', 'customers', 'nextInvoiceNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|unique:invoices,invoice_number',
            'customer_id' => 'nullable|exists:customers,id',
            'project_id' => 'required|exists:projects,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'description' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'vat_percentage' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
            'payment_method' => 'nullable|string',
        ]);

        // Calculate totals
        $subtotal = $validated['subtotal'];
        $vatPercentage = $validated['vat_percentage'] ?? 0;
        $vatAmount = $subtotal * ($vatPercentage / 100);
        $total = $subtotal + $vatAmount;

        $invoice = Invoice::create([
            ...$validated,
            'type' => 'income',
            'user_id' => Auth::id(),
            'vat_amount' => $vatAmount,
            'total' => $total,
            'source' => 'manual',
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Factuur succesvol aangemaakt!');
    }

    public function show(Invoice $invoice)
    {
        // Ensure it's an income invoice
        if ($invoice->type !== 'income') {
            abort(404);
        }

        $invoice->load(['customer', 'project', 'items', 'transactions', 'user']);

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        // Ensure it's an income invoice
        if ($invoice->type !== 'income') {
            abort(404);
        }

        $projects = Project::active()->get();
        $customers = Customer::active()->get();

        return view('invoices.edit', compact('invoice', 'projects', 'customers'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        // Ensure it's an income invoice
        if ($invoice->type !== 'income') {
            abort(404);
        }

        $validated = $request->validate([
            'invoice_number' => 'required|unique:invoices,invoice_number,' . $invoice->id,
            'customer_id' => 'nullable|exists:customers,id',
            'project_id' => 'required|exists:projects,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'description' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'vat_percentage' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string',
        ]);

        // Calculate totals
        $subtotal = $validated['subtotal'];
        $vatPercentage = $validated['vat_percentage'] ?? 0;
        $vatAmount = $subtotal * ($vatPercentage / 100);
        $total = $subtotal + $vatAmount;

        $invoice->update([
            ...$validated,
            'vat_amount' => $vatAmount,
            'total' => $total,
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Factuur succesvol bijgewerkt!');
    }

    public function destroy(Invoice $invoice)
    {
        // Ensure it's an income invoice
        if ($invoice->type !== 'income') {
            abort(404);
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Factuur succesvol verwijderd!');
    }

    public function markAsPaid(Invoice $invoice)
    {
        $invoice->markAsPaid();

        return redirect()->back()
            ->with('success', 'Factuur gemarkeerd als betaald!');
    }

    private function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $lastInvoice = Invoice::income()
            ->whereYear('created_at', $year)
            ->latest('id')
            ->first();

        $number = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;

        return sprintf('INV-%d-%04d', $year, $number);
    }
}
