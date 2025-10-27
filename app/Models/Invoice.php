<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'type',
        'user_id',
        'project_id',
        'customer_id',
        'supplier_id',
        'category_id',
        'invoice_date',
        'due_date',
        'payment_date',
        'description',
        'subtotal',
        'vat_amount',
        'vat_percentage',
        'total',
        'status',
        'payment_method',
        'reference',
        'file_path',
        'source',
        'mollie_payment_id',
        'bunq_transaction_id',
        'gmail_message_id',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
        'subtotal' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'vat_percentage' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the user who created this invoice.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project for this invoice.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the customer (for income invoices).
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the supplier (for expense invoices).
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the category (for expense invoices).
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get invoice items/lines.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get transactions for this invoice.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope to get income invoices.
     */
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope to get expense invoices.
     */
    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope to get paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope to get unpaid invoices.
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['draft', 'sent', 'overdue']);
    }

    /**
     * Scope to get overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function($q) {
                $q->where('status', 'sent')
                  ->whereNotNull('due_date')
                  ->where('due_date', '<', now());
            });
    }

    /**
     * Scope by project.
     */
    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope by date range.
     */
    public function scopeForPeriod($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('invoice_date', [$startDate, $endDate]);
    }

    /**
     * Scope for year.
     */
    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('invoice_date', $year);
    }

    /**
     * Scope for quarter.
     */
    public function scopeForQuarter($query, int $year, int $quarter)
    {
        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $quarter * 3;

        return $query->whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', '>=', $startMonth)
            ->whereMonth('invoice_date', '<=', $endMonth);
    }

    /**
     * Check if invoice is income.
     */
    public function isIncome(): bool
    {
        return $this->type === 'income';
    }

    /**
     * Check if invoice is expense.
     */
    public function isExpense(): bool
    {
        return $this->type === 'expense';
    }

    /**
     * Check if invoice is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if invoice is overdue.
     */
    public function isOverdue(): bool
    {
        if ($this->status === 'overdue') {
            return true;
        }

        if ($this->status === 'sent' && $this->due_date && $this->due_date->isPast()) {
            return true;
        }

        return false;
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(string $paymentDate = null, string $paymentMethod = null): void
    {
        $this->update([
            'status' => 'paid',
            'payment_date' => $paymentDate ?? now(),
            'payment_method' => $paymentMethod ?? $this->payment_method,
        ]);
    }

    /**
     * Calculate total from items.
     */
    public function calculateTotalFromItems(): void
    {
        $subtotal = $this->items->sum('subtotal');
        $vatAmount = $this->items->sum('vat_amount');
        $total = $this->items->sum('total');

        $this->update([
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'total' => $total,
        ]);
    }
}
