<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::expense()
            ->with(['supplier', 'category', 'project'])
            ->latest('invoice_date');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $expenses = $query->paginate(20)->withQueryString();
        $categories = Category::active()->root()->get();
        $projects = Project::active()->get();

        return view('expenses.index', compact('expenses', 'categories', 'projects'));
    }

    public function create()
    {
        $projects = Project::active()->get();
        $categories = Category::active()->root()->get();
        $suppliers = Supplier::active()->get();
        $nextExpenseNumber = $this->generateExpenseNumber();

        return view('expenses.create', compact('projects', 'categories', 'suppliers', 'nextExpenseNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|unique:invoices,invoice_number',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'category_id' => 'required|exists:categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'description' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'vat_percentage' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
            'payment_method' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Calculate totals
        $subtotal = $validated['subtotal'];
        $vatPercentage = $validated['vat_percentage'] ?? 0;
        $vatAmount = $subtotal * ($vatPercentage / 100);
        $total = $subtotal + $vatAmount;

        // Handle file upload
        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('invoices/expenses', 'private');
        }

        $expense = Invoice::create([
            ...$validated,
            'type' => 'expense',
            'user_id' => Auth::id(),
            'vat_amount' => $vatAmount,
            'total' => $total,
            'file_path' => $filePath,
            'source' => 'manual',
        ]);

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Uitgave succesvol aangemaakt!');
    }

    public function show(Invoice $expense)
    {
        // Ensure it's an expense
        if ($expense->type !== 'expense') {
            abort(404);
        }

        $expense->load(['supplier', 'category', 'project', 'items', 'transactions', 'user']);

        return view('expenses.show', compact('expense'));
    }

    public function edit(Invoice $expense)
    {
        // Ensure it's an expense
        if ($expense->type !== 'expense') {
            abort(404);
        }

        $projects = Project::active()->get();
        $categories = Category::active()->root()->get();
        $suppliers = Supplier::active()->get();

        return view('expenses.edit', compact('expense', 'projects', 'categories', 'suppliers'));
    }

    public function update(Request $request, Invoice $expense)
    {
        // Ensure it's an expense
        if ($expense->type !== 'expense') {
            abort(404);
        }

        $validated = $request->validate([
            'invoice_number' => 'required|unique:invoices,invoice_number,' . $expense->id,
            'supplier_id' => 'nullable|exists:suppliers,id',
            'category_id' => 'required|exists:categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'description' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'vat_percentage' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Calculate totals
        $subtotal = $validated['subtotal'];
        $vatPercentage = $validated['vat_percentage'] ?? 0;
        $vatAmount = $subtotal * ($vatPercentage / 100);
        $total = $subtotal + $vatAmount;

        // Handle file upload
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($expense->file_path) {
                Storage::disk('private')->delete($expense->file_path);
            }
            $validated['file_path'] = $request->file('file')->store('invoices/expenses', 'private');
        }

        $expense->update([
            ...$validated,
            'vat_amount' => $vatAmount,
            'total' => $total,
        ]);

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Uitgave succesvol bijgewerkt!');
    }

    public function destroy(Invoice $expense)
    {
        // Ensure it's an expense
        if ($expense->type !== 'expense') {
            abort(404);
        }

        // Delete file if exists
        if ($expense->file_path) {
            Storage::disk('private')->delete($expense->file_path);
        }

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Uitgave succesvol verwijderd!');
    }

    public function markAsPaid(Invoice $expense)
    {
        $expense->markAsPaid();

        return redirect()->back()
            ->with('success', 'Uitgave gemarkeerd als betaald!');
    }

    private function generateExpenseNumber(): string
    {
        $year = now()->year;
        $lastExpense = Invoice::expense()
            ->whereYear('created_at', $year)
            ->latest('id')
            ->first();

        $number = $lastExpense ? (int) substr($lastExpense->invoice_number, -4) + 1 : 1;

        return sprintf('EXP-%d-%04d', $year, $number);
    }
}
