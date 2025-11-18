<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Professional extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'specialty',
        'description',
        'portfolio_url',
        'bio',
        'lat',
        'lng',
        'address',
        'city',
        'state',
        'postal_code',
        'service_radius_km',
        'services',
        'hourly_rate',
        'approved',
        'active',
        'verified',
        'verified_at',
        'rating',
        'reviews_count',
        'total_reviews',
        'total_jobs',
        'certifications',
        'equipment',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'service_radius_km' => 'integer',
            'services' => 'array',
            'hourly_rate' => 'decimal:2',
            'approved' => 'boolean',
            'active' => 'boolean',
            'verified' => 'boolean',
            'verified_at' => 'datetime',
            'rating' => 'decimal:2',
            'reviews_count' => 'integer',
            'total_reviews' => 'integer',
            'total_jobs' => 'integer',
            'certifications' => 'array',
            'equipment' => 'array',
        ];
    }

    /**
     * Get the user that owns the professional profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the offers for the professional.
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * Get the orders for the professional.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Scope a query to only include approved professionals.
     */
    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    /**
     * Scope a query to filter professionals within radius.
     */
    public function scopeWithinRadius($query, float $lat, float $lng, int $radiusKm)
    {
        $earthRadiusKm = 6371;

        return $query->selectRaw("*, (
            {$earthRadiusKm} * acos(
                cos(radians(?)) * cos(radians(lat)) *
                cos(radians(lng) - radians(?)) +
                sin(radians(?)) * sin(radians(lat))
            )
        ) AS distance", [$lat, $lng, $lat])
            ->havingRaw('distance <= ?', [$radiusKm])
            ->orderBy('distance');
    }

    /**
     * Get full location string.
     */
    public function getFullAddress(): string
    {
        return trim("{$this->address}, {$this->city} - {$this->state}");
    }
}
