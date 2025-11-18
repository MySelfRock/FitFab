<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'project_id',
        'user_id',
        'professional_id',
        'offer_id',
        'amount',
        'platform_fee',
        'status',
        'payment_provider',
        'payment_provider_id',
        'payment_metadata',
        'paid_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'payment_metadata' => 'array',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(Str::random(10));
            }
        });
    }

    /**
     * Get the project for the order.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user (customer) for the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the professional for the order.
     */
    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    /**
     * Get the offer for the order.
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    /**
     * Check if order is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid' || $this->status === 'in_progress' || $this->status === 'completed';
    }

    /**
     * Check if order is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get professional amount (after platform fee).
     */
    public function getProfessionalAmount(): float
    {
        return (float) $this->amount - (float) $this->platform_fee;
    }

    /**
     * Scope a query to only include paid orders.
     */
    public function scopePaid($query)
    {
        return $query->whereIn('status', ['paid', 'in_progress', 'completed']);
    }

    /**
     * Scope a query to only include completed orders.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
