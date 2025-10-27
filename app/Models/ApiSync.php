<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiSync extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'service',
        'type',
        'status',
        'started_at',
        'completed_at',
        'items_found',
        'items_processed',
        'items_created',
        'items_updated',
        'items_failed',
        'error_message',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'items_found' => 'integer',
        'items_processed' => 'integer',
        'items_created' => 'integer',
        'items_updated' => 'integer',
        'items_failed' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Scope by service.
     */
    public function scopeForService($query, string $service)
    {
        return $query->where('service', $service);
    }

    /**
     * Scope successful syncs.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope failed syncs.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Get sync duration in seconds.
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at);
    }

    /**
     * Check if sync was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if sync failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
