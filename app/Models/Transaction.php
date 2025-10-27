<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'type',
        'source',
        'external_id',
        'transaction_date',
        'amount',
        'description',
        'counterparty_name',
        'counterparty_iban',
        'payment_method',
        'status',
        'raw_data',
        'matched',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:2',
        'raw_data' => 'array',
        'matched' => 'boolean',
    ];

    /**
     * Get the invoice for this transaction.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Scope to get unmatched transactions.
     */
    public function scopeUnmatched($query)
    {
        return $query->where('matched', false);
    }

    /**
     * Scope to get matched transactions.
     */
    public function scopeMatched($query)
    {
        return $query->where('matched', true);
    }

    /**
     * Scope by source.
     */
    public function scopeFromSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Mark as matched with an invoice.
     */
    public function matchWith(Invoice $invoice): void
    {
        $this->update([
            'invoice_id' => $invoice->id,
            'matched' => true,
        ]);
    }
}
