<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Piece extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'qty',
        'width_mm',
        'height_mm',
        'thickness_mm',
        'options',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'width_mm' => 'decimal:2',
            'height_mm' => 'decimal:2',
            'thickness_mm' => 'decimal:2',
            'options' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the project that owns the piece.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the area of the piece in square millimeters.
     */
    public function getArea(): float
    {
        return (float) $this->width_mm * (float) $this->height_mm;
    }

    /**
     * Get the total area of all pieces (qty * area).
     */
    public function getTotalArea(): float
    {
        return $this->getArea() * $this->qty;
    }

    /**
     * Get dimensions as array.
     */
    public function getDimensions(): array
    {
        return [
            'width' => (float) $this->width_mm,
            'height' => (float) $this->height_mm,
            'thickness' => (float) $this->thickness_mm,
        ];
    }
}
