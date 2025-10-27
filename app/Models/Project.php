<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'status',
        'start_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the invoices for this project.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get income invoices for this project.
     */
    public function incomeInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->where('type', 'income');
    }

    /**
     * Get expense invoices for this project.
     */
    public function expenseInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->where('type', 'expense');
    }

    /**
     * Scope to get active projects only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    /**
     * Calculate total revenue for this project.
     */
    public function getTotalRevenueAttribute(): float
    {
        return $this->incomeInvoices()
            ->where('status', 'paid')
            ->sum('total');
    }

    /**
     * Calculate total expenses for this project.
     */
    public function getTotalExpensesAttribute(): float
    {
        return $this->expenseInvoices()
            ->where('status', 'paid')
            ->sum('total');
    }

    /**
     * Calculate profit for this project.
     */
    public function getProfitAttribute(): float
    {
        return $this->total_revenue - $this->total_expenses;
    }
}
