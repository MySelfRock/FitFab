<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'sheet_type',
        'sheet_number',
        'layout_data',
        'layout_svg',
        'waste_percent',
        'utilization_percent',
    ];

    protected function casts(): array
    {
        return [
            'sheet_number' => 'integer',
            'layout_data' => 'array',
            'waste_percent' => 'decimal:2',
            'utilization_percent' => 'decimal:2',
        ];
    }

    /**
     * Get the project that owns the sheet.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get pieces placed on this sheet.
     */
    public function getPlacedPieces(): array
    {
        return $this->layout_data['pieces'] ?? [];
    }

    /**
     * Get sheet dimensions from type string (e.g., "1220x2440").
     */
    public function getSheetDimensions(): array
    {
        $dimensions = explode('x', $this->sheet_type);

        return [
            'width' => (float) ($dimensions[0] ?? 0),
            'height' => (float) ($dimensions[1] ?? 0),
        ];
    }
}
