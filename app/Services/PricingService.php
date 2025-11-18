<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Log;

class PricingService
{
    // Custos base (podem ser configuráveis via config ou database)
    protected float $cutCostPerMeter = 5.0; // R$ por metro linear de corte
    protected float $edgeBandingCostPerMeter = 8.0; // R$ por metro de fita de borda
    protected float $laborCostPerHour = 50.0; // R$ por hora de trabalho
    protected float $markupPercent = 30.0; // Margem de lucro %

    /**
     * Calculate estimated price for a project.
     *
     * @param Project $project
     * @return array
     */
    public function calculateProjectPrice(Project $project): array
    {
        Log::info("Calculating price for project {$project->id}");

        $costs = [
            'material' => $this->calculateMaterialCost($project),
            'cutting' => $this->calculateCuttingCost($project),
            'edge_banding' => $this->calculateEdgeBandingCost($project),
            'labor' => $this->calculateLaborCost($project),
        ];

        $subtotal = array_sum($costs);
        $markup = $subtotal * ($this->markupPercent / 100);
        $total = $subtotal + $markup;

        return [
            'costs' => $costs,
            'subtotal' => round($subtotal, 2),
            'markup' => round($markup, 2),
            'total' => round($total, 2),
            'markup_percent' => $this->markupPercent,
        ];
    }

    /**
     * Calculate material cost based on sheets needed.
     *
     * @param Project $project
     * @return float
     */
    protected function calculateMaterialCost(Project $project): float
    {
        $material = $project->material;
        if (!$material || !$material->price_per_sheet) {
            Log::warning("No material or price defined for project {$project->id}");
            return 0;
        }

        $sheetsCount = $project->sheets()->count();
        if ($sheetsCount === 0) {
            // Estimate based on total area if sheets not generated yet
            $sheetsCount = $this->estimateSheetsCount($project);
        }

        $cost = $sheetsCount * (float) $material->price_per_sheet;

        Log::info("Material cost: {$sheetsCount} sheets x R$ {$material->price_per_sheet} = R$ {$cost}");

        return $cost;
    }

    /**
     * Calculate cutting cost based on total cut length.
     *
     * @param Project $project
     * @return float
     */
    protected function calculateCuttingCost(Project $project): float
    {
        $pieces = $project->pieces;
        $totalCutLength = 0;

        foreach ($pieces as $piece) {
            $qty = $piece->qty;
            $perimeter = 2 * ((float) $piece->width_mm + (float) $piece->height_mm);
            $totalCutLength += ($perimeter * $qty);
        }

        // Convert mm to meters
        $totalCutLengthMeters = $totalCutLength / 1000;
        $cost = $totalCutLengthMeters * $this->cutCostPerMeter;

        Log::info("Cutting cost: {$totalCutLengthMeters}m x R$ {$this->cutCostPerMeter} = R$ {$cost}");

        return $cost;
    }

    /**
     * Calculate edge banding cost.
     *
     * @param Project $project
     * @return float
     */
    protected function calculateEdgeBandingCost(Project $project): float
    {
        $pieces = $project->pieces;
        $totalEdgeLength = 0;

        foreach ($pieces as $piece) {
            $edgeBanding = $piece->options['edge_banding'] ?? [];
            if (empty($edgeBanding)) {
                continue;
            }

            $qty = $piece->qty;
            $width = (float) $piece->width_mm;
            $height = (float) $piece->height_mm;

            // Calculate edge length based on which sides need banding
            $edgeLength = 0;
            foreach ($edgeBanding as $side) {
                $edgeLength += match (strtolower($side)) {
                    'top', 'bottom' => $width,
                    'left', 'right' => $height,
                    'all' => ($width * 2) + ($height * 2),
                    default => 0,
                };
            }

            $totalEdgeLength += ($edgeLength * $qty);
        }

        // Convert mm to meters
        $totalEdgeLengthMeters = $totalEdgeLength / 1000;
        $cost = $totalEdgeLengthMeters * $this->edgeBandingCostPerMeter;

        Log::info("Edge banding cost: {$totalEdgeLengthMeters}m x R$ {$this->edgeBandingCostPerMeter} = R$ {$cost}");

        return $cost;
    }

    /**
     * Calculate labor cost based on estimated hours.
     *
     * @param Project $project
     * @return float
     */
    protected function calculateLaborCost(Project $project): float
    {
        $piecesCount = $project->pieces()->sum('qty');

        // Estimate labor hours based on pieces count and complexity
        // Simple formula: 0.5 hours per piece + 2 hours setup
        $estimatedHours = 2 + ($piecesCount * 0.5);

        $cost = $estimatedHours * $this->laborCostPerHour;

        Log::info("Labor cost: {$estimatedHours}h x R$ {$this->laborCostPerHour} = R$ {$cost}");

        return $cost;
    }

    /**
     * Estimate sheets count if not generated yet.
     *
     * @param Project $project
     * @return int
     */
    protected function estimateSheetsCount(Project $project): int
    {
        $material = $project->material;
        if (!$material) {
            return 1;
        }

        $totalPiecesArea = $project->pieces->sum(function ($piece) {
            return $piece->getArea() * $piece->qty;
        });

        $sheetDimensions = $material->getSheetDimensions();
        $sheetArea = $sheetDimensions['width'] * $sheetDimensions['height'];

        // Assuming 75% utilization efficiency
        $utilizationFactor = 0.75;
        $effectiveSheetArea = $sheetArea * $utilizationFactor;

        $sheetsCount = ceil($totalPiecesArea / $effectiveSheetArea);

        return max($sheetsCount, 1);
    }

    /**
     * Update project with calculated price.
     *
     * @param Project $project
     * @return Project
     */
    public function updateProjectPrice(Project $project): Project
    {
        $pricing = $this->calculateProjectPrice($project);

        $project->estimated_price = $pricing['total'];
        $project->metrics = array_merge($project->metrics ?? [], [
            'pricing' => $pricing,
            'last_calculated_at' => now()->toIso8601String(),
        ]);

        $project->save();

        return $project;
    }

    /**
     * Set custom rates.
     *
     * @param array $rates
     * @return self
     */
    public function setRates(array $rates): self
    {
        if (isset($rates['cut_per_meter'])) {
            $this->cutCostPerMeter = $rates['cut_per_meter'];
        }

        if (isset($rates['edge_banding_per_meter'])) {
            $this->edgeBandingCostPerMeter = $rates['edge_banding_per_meter'];
        }

        if (isset($rates['labor_per_hour'])) {
            $this->laborCostPerHour = $rates['labor_per_hour'];
        }

        if (isset($rates['markup_percent'])) {
            $this->markupPercent = $rates['markup_percent'];
        }

        return $this;
    }
}
