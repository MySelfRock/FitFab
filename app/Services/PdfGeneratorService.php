<?php

namespace App\Services;

use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfGeneratorService
{
    /**
     * Generate a detailed project specification PDF.
     */
    public function generateProjectPdf(Project $project): string
    {
        $project->load(['template', 'material', 'user']);

        $data = [
            'project' => $project,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.project-specification', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        $filename = "project-{$project->id}-specification.pdf";
        $path = "projects/{$project->id}/{$filename}";

        // Save to storage
        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Generate a cutting plan PDF.
     */
    public function generateCuttingPlanPdf(Project $project): string
    {
        $project->load(['template', 'material']);

        // Calculate cutting details
        $cuttingDetails = $this->calculateCuttingDetails($project);

        $data = [
            'project' => $project,
            'cuttingDetails' => $cuttingDetails,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.cutting-plan', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        $filename = "project-{$project->id}-cutting-plan.pdf";
        $path = "projects/{$project->id}/{$filename}";

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Generate a material list PDF.
     */
    public function generateMaterialListPdf(Project $project): string
    {
        $project->load(['material']);

        $materialList = $this->calculateMaterialList($project);

        $data = [
            'project' => $project,
            'materialList' => $materialList,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.material-list', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        $filename = "project-{$project->id}-material-list.pdf";
        $path = "projects/{$project->id}/{$filename}";

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Generate all project PDFs.
     */
    public function generateAllProjectPdfs(Project $project): array
    {
        return [
            'specification' => $this->generateProjectPdf($project),
            'cutting_plan' => $this->generateCuttingPlanPdf($project),
            'material_list' => $this->generateMaterialListPdf($project),
        ];
    }

    /**
     * Calculate cutting details for the project.
     */
    private function calculateCuttingDetails(Project $project): array
    {
        $material = $project->material;

        // Calculate number of sheets needed
        $projectArea = ($project->width * $project->height) / 1000000; // Convert to m²
        $sheetArea = ($material->width * $material->height) / 1000000; // Convert to m²
        $sheetsNeeded = ceil($projectArea / $sheetArea);

        // Generate cutting pieces (simplified)
        $pieces = [];

        // Main panels
        $pieces[] = [
            'name' => 'Painel Frontal',
            'width' => $project->width,
            'height' => $project->height,
            'quantity' => 1,
            'edge_banding' => 'Todas as bordas',
        ];

        $pieces[] = [
            'name' => 'Painel Traseiro',
            'width' => $project->width,
            'height' => $project->height,
            'quantity' => 1,
            'edge_banding' => 'Todas as bordas',
        ];

        $pieces[] = [
            'name' => 'Painel Lateral Esquerdo',
            'width' => $project->depth,
            'height' => $project->height,
            'quantity' => 1,
            'edge_banding' => 'Todas as bordas',
        ];

        $pieces[] = [
            'name' => 'Painel Lateral Direito',
            'width' => $project->depth,
            'height' => $project->height,
            'quantity' => 1,
            'edge_banding' => 'Todas as bordas',
        ];

        $pieces[] = [
            'name' => 'Prateleira Superior',
            'width' => $project->width - ($material->thickness * 2),
            'height' => $project->depth - ($material->thickness * 2),
            'quantity' => 1,
            'edge_banding' => 'Frente',
        ];

        $pieces[] = [
            'name' => 'Prateleira Inferior',
            'width' => $project->width - ($material->thickness * 2),
            'height' => $project->depth - ($material->thickness * 2),
            'quantity' => 1,
            'edge_banding' => 'Frente',
        ];

        return [
            'sheets_needed' => $sheetsNeeded,
            'sheet_area' => $sheetArea,
            'total_area' => $projectArea,
            'waste_percentage' => round((($sheetsNeeded * $sheetArea) - $projectArea) / ($sheetsNeeded * $sheetArea) * 100, 2),
            'pieces' => $pieces,
        ];
    }

    /**
     * Calculate material list for the project.
     */
    private function calculateMaterialList(Project $project): array
    {
        $material = $project->material;
        $cuttingDetails = $this->calculateCuttingDetails($project);

        $items = [];

        // Main material
        $items[] = [
            'category' => 'Material Principal',
            'name' => $material->name,
            'description' => "{$material->type} - {$material->thickness}mm - {$material->color}",
            'quantity' => $cuttingDetails['sheets_needed'],
            'unit' => 'chapa',
            'unit_price' => $material->price_per_sheet,
            'total_price' => $cuttingDetails['sheets_needed'] * $material->price_per_sheet,
        ];

        // Edge banding (simplified calculation)
        $totalEdgeBanding = (
            ($project->width * 4) + // Front and back panels
            ($project->height * 4) + // Side panels
            ($project->depth * 4) + // Side panels depth
            ($project->width * 2) // Shelves front
        ) / 1000; // Convert to meters

        $items[] = [
            'category' => 'Acabamento',
            'name' => 'Fita de Borda',
            'description' => "Cor: {$material->color}",
            'quantity' => ceil($totalEdgeBanding),
            'unit' => 'metros',
            'unit_price' => 5.50, // Example price per meter
            'total_price' => ceil($totalEdgeBanding) * 5.50,
        ];

        // Hardware
        $items[] = [
            'category' => 'Ferragens',
            'name' => 'Dobradiças',
            'description' => '35mm com amortecedor',
            'quantity' => 4,
            'unit' => 'unidade',
            'unit_price' => 12.90,
            'total_price' => 4 * 12.90,
        ];

        $items[] = [
            'category' => 'Ferragens',
            'name' => 'Puxadores',
            'description' => 'Inox escovado 128mm',
            'quantity' => 2,
            'unit' => 'unidade',
            'unit_price' => 8.90,
            'total_price' => 2 * 8.90,
        ];

        $items[] = [
            'category' => 'Ferragens',
            'name' => 'Parafusos e Cavilhas',
            'description' => 'Kit completo para montagem',
            'quantity' => 1,
            'unit' => 'kit',
            'unit_price' => 25.00,
            'total_price' => 25.00,
        ];

        $totalCost = array_sum(array_column($items, 'total_price'));

        return [
            'items' => $items,
            'total_cost' => $totalCost,
            'categories' => array_unique(array_column($items, 'category')),
        ];
    }
}
