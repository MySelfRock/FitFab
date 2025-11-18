<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'thickness_mm',
        'sheet_width_mm',
        'sheet_height_mm',
        'price_per_sheet',
        'properties',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'thickness_mm' => 'decimal:2',
            'sheet_width_mm' => 'decimal:2',
            'sheet_height_mm' => 'decimal:2',
            'price_per_sheet' => 'decimal:2',
            'properties' => 'array',
            'active' => 'boolean',
        ];
    }

    /**
     * Get the projects using this material.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Scope a query to only include active materials.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Get sheet dimensions as array.
     */
    public function getSheetDimensions(): array
    {
        return [
            'width' => (float) $this->sheet_width_mm,
            'height' => (float) $this->sheet_height_mm,
        ];
    }
}
