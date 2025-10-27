<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class OAuthToken extends Model
{
    use HasFactory;

    protected $table = 'oauth_tokens';

    protected $fillable = [
        'service',
        'access_token',
        'refresh_token',
        'expires_at',
        'scope',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Get token for a service.
     */
    public static function forService(string $service): ?self
    {
        return static::where('service', $service)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Set encrypted access token.
     */
    public function setAccessTokenAttribute($value): void
    {
        $this->attributes['access_token'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get decrypted access token.
     */
    public function getAccessTokenAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Set encrypted refresh token.
     */
    public function setRefreshTokenAttribute($value): void
    {
        $this->attributes['refresh_token'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get decrypted refresh token.
     */
    public function getRefreshTokenAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Check if token is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Check if token needs refresh (expires in less than 5 minutes).
     */
    public function needsRefresh(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->subMinutes(5)->isPast();
    }

    /**
     * Revoke this token.
     */
    public function revoke(): void
    {
        $this->update(['is_active' => false]);
    }
}
