<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'default_options',
        'pieces_definition',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'default_options' => 'array',
            'pieces_definition' => 'array',
            'active' => 'boolean',
        ];
    }

    /**
     * Get the projects for the template.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Scope a query to only include active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
