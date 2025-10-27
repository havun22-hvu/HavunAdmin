<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::withCount(['invoices'])
            ->latest()
            ->get()
            ->map(function ($project) {
                $project->total_revenue = $project->total_revenue;
                $project->total_expenses = $project->total_expenses;
                $project->profit = $project->profit;
                return $project;
            });

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'status' => 'required|in:active,development,archived',
            'start_date' => 'nullable|date',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = true;

        $project = Project::create($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project succesvol aangemaakt!');
    }

    public function show(Project $project)
    {
        $project->load(['invoices.customer', 'invoices.category', 'invoices.supplier']);

        $incomeInvoices = $project->incomeInvoices()->with('customer')->latest('invoice_date')->take(10)->get();
        $expenseInvoices = $project->expenseInvoices()->with(['supplier', 'category'])->latest('invoice_date')->take(10)->get();

        $stats = [
            'total_revenue' => $project->total_revenue,
            'total_expenses' => $project->total_expenses,
            'profit' => $project->profit,
            'invoices_count' => $project->invoices()->count(),
            'paid_invoices_count' => $project->invoices()->paid()->count(),
            'unpaid_invoices_count' => $project->invoices()->unpaid()->count(),
        ];

        return view('projects.show', compact('project', 'incomeInvoices', 'expenseInvoices', 'stats'));
    }

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'status' => 'required|in:active,development,archived',
            'start_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project succesvol bijgewerkt!');
    }

    public function destroy(Project $project)
    {
        // Check if project has invoices
        if ($project->invoices()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Kan project niet verwijderen, er zijn nog facturen gekoppeld.');
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project succesvol verwijderd!');
    }
}
