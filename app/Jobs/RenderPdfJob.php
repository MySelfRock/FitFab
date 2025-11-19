<?php

namespace App\Jobs;

use App\Models\File;
use App\Models\Project;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class RenderPdfJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 180; // 3 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Project $project
    ) {
        $this->onQueue('pdfs');
    }

    /**
     * Execute the job.
     */
    public function handle(\App\Services\PdfGeneratorService $pdfService): void
    {
        Log::info("Starting PDF rendering for project {$this->project->id}");

        try {
            // Load relationships
            $this->project->load(['pieces', 'sheets', 'material', 'user', 'template']);

            // Generate all PDFs using PdfGeneratorService
            $paths = $pdfService->generateAllProjectPdfs($this->project);

            Log::info("PDFs generated successfully for project {$this->project->id}", $paths);

            // Update project status to completed
            $this->project->update(['status' => 'completed']);

            // Send notification to user
            $this->project->user->notify(new \App\Notifications\ProjectCompletedNotification($this->project));

            Log::info("Notification sent to user {$this->project->user->id}");
        } catch (\Exception $e) {
            Log::error("Error rendering PDF for project {$this->project->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Generate HTML for PDF.
     *
     * @return string
     */
    protected function generateHtml(): string
    {
        // Create a simple HTML template
        // In production, use blade views: View::make('pdf.project', ['project' => $this->project])
        return $this->generateSimpleHtml();
    }

    /**
     * Generate simple HTML (placeholder for blade template).
     *
     * @return string
     */
    protected function generateSimpleHtml(): string
    {
        $project = $this->project;
        $pieces = $project->pieces;
        $sheets = $project->sheets;

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Projeto {$project->name}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .summary { background: #e3f2fd; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Projeto: {$project->name}</h1>

    <div class="summary">
        <h2>Resumo</h2>
        <p><strong>Cliente:</strong> {$project->user->name}</p>
        <p><strong>Material:</strong> {$project->material->name}</p>
        <p><strong>Total de Chapas:</strong> {$sheets->count()}</p>
        <p><strong>Total de Peças:</strong> {$pieces->sum('qty')}</p>
        <p><strong>Preço Estimado:</strong> R$ {$project->estimated_price}</p>
    </div>

    <h2>Lista de Peças</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>Quantidade</th>
                <th>Largura (mm)</th>
                <th>Altura (mm)</th>
                <th>Espessura (mm)</th>
            </tr>
        </thead>
        <tbody>
HTML;

        foreach ($pieces as $index => $piece) {
            $html .= <<<HTML
            <tr>
                <td>{$index + 1}</td>
                <td>{$piece->name}</td>
                <td>{$piece->qty}</td>
                <td>{$piece->width_mm}</td>
                <td>{$piece->height_mm}</td>
                <td>{$piece->thickness_mm}</td>
            </tr>
HTML;
        }

        $html .= <<<HTML
        </tbody>
    </table>

    <h2>Chapas</h2>
HTML;

        foreach ($sheets as $sheet) {
            $html .= <<<HTML
    <div style="page-break-after: always;">
        <h3>Chapa #{$sheet->sheet_number}</h3>
        <p>Tipo: {$sheet->sheet_type}</p>
        <p>Aproveitamento: {$sheet->utilization_percent}%</p>
        <p>Desperdício: {$sheet->waste_percent}%</p>
    </div>
HTML;
        }

        $html .= <<<HTML
</body>
</html>
HTML;

        return $html;
    }

    /**
     * Convert HTML to PDF.
     * TODO: Integrate with Puppeteer or DomPDF.
     *
     * @param string $html
     * @return string
     */
    protected function convertHtmlToPdf(string $html): string
    {
        // Placeholder: In production, use:
        // - Spatie\Browsershot for Puppeteer/Chrome
        // - Barryvdh\Snappy for wkhtmltopdf
        // - DomPDF for pure PHP solution

        Log::info("Converting HTML to PDF (placeholder)");

        // For now, return HTML content
        // In production, this would return actual PDF binary
        return $html;
    }

    /**
     * Save PDF file to storage.
     *
     * @param string $content
     * @return File
     */
    protected function savePdfFile(string $content): File
    {
        $filename = $this->generateFilename();
        $path = "projects/{$this->project->id}/{$filename}";

        // Save to storage
        Storage::put($path, $content);

        // Get file size
        $size = Storage::size($path);

        // Create or update File record
        $file = File::updateOrCreate(
            [
                'fileable_id' => $this->project->id,
                'fileable_type' => Project::class,
                'type' => 'pdf',
            ],
            [
                'path' => $path,
                'filename' => $filename,
                'mime_type' => 'application/pdf',
                'size' => $size,
                'meta' => [
                    'generated_at' => now()->toIso8601String(),
                    'renderer' => 'placeholder', // 'puppeteer', 'dompdf', etc
                ],
            ]
        );

        return $file;
    }

    /**
     * Generate filename for PDF.
     *
     * @return string
     */
    protected function generateFilename(): string
    {
        $slug = Str::slug($this->project->name);
        $timestamp = now()->format('YmdHis');

        return "{$slug}-report-{$timestamp}.pdf";
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("RenderPdfJob failed for project {$this->project->id}: {$exception->getMessage()}");
    }
}
