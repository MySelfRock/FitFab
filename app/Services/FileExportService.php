<?php

namespace App\Services;

use App\Models\File;
use App\Models\Project;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileExportService
{
    /**
     * Export project files in multiple formats.
     *
     * @param Project $project
     * @param array $formats
     * @return array
     */
    public function exportProjectFiles(Project $project, array $formats = ['pdf', 'svg', 'dxf', 'csv']): array
    {
        $files = [];

        foreach ($formats as $format) {
            try {
                $file = match ($format) {
                    'csv' => $this->exportCsv($project),
                    'svg' => $this->exportSvg($project),
                    'dxf' => $this->exportDxf($project),
                    'pdf' => $this->exportPdf($project),
                    default => null,
                };

                if ($file) {
                    $files[] = $file;
                }
            } catch (\Exception $e) {
                Log::error("Error exporting {$format} for project {$project->id}: {$e->getMessage()}");
            }
        }

        return $files;
    }

    /**
     * Export cutlist as CSV.
     *
     * @param Project $project
     * @return File
     */
    public function exportCsv(Project $project): File
    {
        Log::info("Exporting CSV for project {$project->id}");

        $pieces = $project->pieces()->orderBy('sort_order')->get();

        $csv = "ID,Nome,Quantidade,Largura (mm),Altura (mm),Espessura (mm),Área (mm²),Fita de Borda\n";

        foreach ($pieces as $piece) {
            $edgeBanding = $piece->options['edge_banding'] ?? [];
            $edgeBandingStr = empty($edgeBanding) ? 'Nenhuma' : implode(', ', $edgeBanding);

            $csv .= sprintf(
                "%d,%s,%d,%.2f,%.2f,%.2f,%.2f,%s\n",
                $piece->id,
                $piece->name,
                $piece->qty,
                $piece->width_mm,
                $piece->height_mm,
                $piece->thickness_mm,
                $piece->getArea(),
                $edgeBandingStr
            );
        }

        return $this->saveFile($project, $csv, 'csv', 'text/csv');
    }

    /**
     * Export sheet layouts as SVG (one file per sheet).
     *
     * @param Project $project
     * @return File
     */
    public function exportSvg(Project $project): File
    {
        Log::info("Exporting SVG for project {$project->id}");

        // Get all sheets and their SVGs
        $sheets = $project->sheets;
        $svgContent = '';

        if ($sheets->isEmpty()) {
            Log::warning("No sheets found for project {$project->id}");
            // Create a placeholder SVG
            $svgContent = '<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg"><text x="10" y="20">No layouts available</text></svg>';
        } else {
            // For now, export the first sheet (in production, export all sheets as separate files or combined)
            $firstSheet = $sheets->first();
            $svgContent = $firstSheet->layout_svg ?? '<svg></svg>';
        }

        return $this->saveFile($project, $svgContent, 'svg', 'image/svg+xml');
    }

    /**
     * Export pieces as DXF (AutoCAD format).
     *
     * @param Project $project
     * @return File
     */
    public function exportDxf(Project $project): File
    {
        Log::info("Exporting DXF for project {$project->id}");

        $pieces = $project->pieces;

        // Generate simple DXF content
        // Note: This is a simplified DXF. For production, use a proper DXF library
        $dxf = $this->generateDxfContent($pieces);

        return $this->saveFile($project, $dxf, 'dxf', 'application/dxf');
    }

    /**
     * Export project report as PDF.
     *
     * @param Project $project
     * @return File
     */
    public function exportPdf(Project $project): File
    {
        Log::info("Exporting PDF for project {$project->id}");

        // For now, create a placeholder
        // In production, this would call RenderPdfJob to generate actual PDF
        $pdfContent = "PDF placeholder for project {$project->id}";

        return $this->saveFile($project, $pdfContent, 'pdf', 'application/pdf');
    }

    /**
     * Save file to storage and create File record.
     *
     * @param Project $project
     * @param string $content
     * @param string $type
     * @param string $mimeType
     * @return File
     */
    protected function saveFile(Project $project, string $content, string $type, string $mimeType): File
    {
        $filename = $this->generateFilename($project, $type);
        $path = "projects/{$project->id}/{$filename}";

        // Save to storage
        Storage::put($path, $content);

        // Get file size
        $size = Storage::size($path);

        // Create File record
        return File::create([
            'fileable_id' => $project->id,
            'fileable_type' => Project::class,
            'path' => $path,
            'filename' => $filename,
            'type' => $type,
            'mime_type' => $mimeType,
            'size' => $size,
            'meta' => [
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Generate filename for export.
     *
     * @param Project $project
     * @param string $type
     * @return string
     */
    protected function generateFilename(Project $project, string $type): string
    {
        $slug = Str::slug($project->name);
        $timestamp = now()->format('YmdHis');

        return "{$slug}-cutlist-{$timestamp}.{$type}";
    }

    /**
     * Generate DXF content (simplified version).
     *
     * @param \Illuminate\Database\Eloquent\Collection $pieces
     * @return string
     */
    protected function generateDxfContent($pieces): string
    {
        $dxf = "0\nSECTION\n2\nENTITIES\n";

        foreach ($pieces as $piece) {
            $width = (float) $piece->width_mm;
            $height = (float) $piece->height_mm;

            // Draw rectangle for each piece
            // This is a very simplified DXF representation
            $dxf .= "0\nLINE\n8\n0\n";
            $dxf .= "10\n0\n20\n0\n30\n0\n";
            $dxf .= "11\n{$width}\n21\n0\n31\n0\n";

            $dxf .= "0\nLINE\n8\n0\n";
            $dxf .= "10\n{$width}\n20\n0\n30\n0\n";
            $dxf .= "11\n{$width}\n21\n{$height}\n31\n0\n";

            $dxf .= "0\nLINE\n8\n0\n";
            $dxf .= "10\n{$width}\n20\n{$height}\n30\n0\n";
            $dxf .= "11\n0\n21\n{$height}\n31\n0\n";

            $dxf .= "0\nLINE\n8\n0\n";
            $dxf .= "10\n0\n20\n{$height}\n30\n0\n";
            $dxf .= "11\n0\n21\n0\n31\n0\n";
        }

        $dxf .= "0\nENDSEC\n0\nEOF\n";

        return $dxf;
    }

    /**
     * Delete all files for a project.
     *
     * @param Project $project
     * @return int
     */
    public function deleteProjectFiles(Project $project): int
    {
        $files = $project->files;
        $count = 0;

        foreach ($files as $file) {
            try {
                $file->delete(); // Will trigger storage deletion via model boot
                $count++;
            } catch (\Exception $e) {
                Log::error("Error deleting file {$file->id}: {$e->getMessage()}");
            }
        }

        return $count;
    }
}
