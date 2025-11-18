<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'fileable_id',
        'fileable_type',
        'path',
        'filename',
        'type',
        'mime_type',
        'size',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'meta' => 'array',
        ];
    }

    /**
     * Get the parent fileable model (project, order, etc).
     */
    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the full URL of the file.
     */
    public function getUrl(): string
    {
        return Storage::disk(config('filesystems.default'))->url($this->path);
    }

    /**
     * Get the download URL of the file.
     */
    public function getDownloadUrl(): string
    {
        return route('files.download', ['file' => $this->id]);
    }

    /**
     * Check if file is a PDF.
     */
    public function isPdf(): bool
    {
        return $this->type === 'pdf';
    }

    /**
     * Check if file is an SVG.
     */
    public function isSvg(): bool
    {
        return $this->type === 'svg';
    }

    /**
     * Check if file is a DXF.
     */
    public function isDxf(): bool
    {
        return $this->type === 'dxf';
    }

    /**
     * Check if file is a CSV.
     */
    public function isCsv(): bool
    {
        return $this->type === 'csv';
    }

    /**
     * Get human-readable file size.
     */
    public function getHumanSize(): string
    {
        if ($this->size === null) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Delete the file from storage when the model is deleted.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($file) {
            Storage::disk(config('filesystems.default'))->delete($file->path);
        });
    }
}
