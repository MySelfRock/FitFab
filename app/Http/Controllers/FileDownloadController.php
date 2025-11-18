<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileDownloadController extends Controller
{
    /**
     * Download a specific file from a project.
     */
    public function download(Project $project, Request $request): StreamedResponse|Response
    {
        $this->authorize('view', $project);

        $format = $request->query('format', 'csv');

        // Find the file for this project and format
        $file = File::where('fileable_type', Project::class)
            ->where('fileable_id', $project->id)
            ->where('type', $format)
            ->latest()
            ->first();

        if (!$file) {
            abort(404, "Arquivo {$format} não encontrado para este projeto.");
        }

        // Check if file exists in storage
        $disk = config('filesystems.default');

        if (!Storage::disk($disk)->exists($file->path)) {
            abort(404, 'Arquivo não encontrado no storage.');
        }

        // Get file content
        $content = Storage::disk($disk)->get($file->path);

        // Determine filename
        $filename = $this->generateFilename($project, $format);

        // Return download response
        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Download all files from a project as a ZIP.
     */
    public function downloadAll(Project $project): StreamedResponse|Response
    {
        $this->authorize('view', $project);

        // Get all files for this project
        $files = File::where('fileable_type', Project::class)
            ->where('fileable_id', $project->id)
            ->get();

        if ($files->isEmpty()) {
            abort(404, 'Nenhum arquivo encontrado para este projeto.');
        }

        $disk = config('filesystems.default');
        $zipFilename = $this->generateFilename($project, 'zip');
        $zipPath = storage_path("app/temp/{$zipFilename}");

        // Create temp directory if it doesn't exist
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // Create ZIP archive
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Não foi possível criar o arquivo ZIP.');
        }

        foreach ($files as $file) {
            if (Storage::disk($disk)->exists($file->path)) {
                $content = Storage::disk($disk)->get($file->path);
                $filename = basename($file->path);
                $zip->addFromString($filename, $content);
            }
        }

        $zip->close();

        // Return download and delete temp file after
        return response()->download($zipPath, $zipFilename)->deleteFileAfterSend(true);
    }

    /**
     * Generate a friendly filename for download.
     */
    protected function generateFilename(Project $project, string $format): string
    {
        $slug = $project->slug;
        $date = now()->format('Y-m-d');

        return "{$slug}_{$date}.{$format}";
    }
}
