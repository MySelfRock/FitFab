<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Piece;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CutlistService
{
    protected float $kerfWidth = 3.0; // Largura do corte em mm
    protected bool $allowRotation = true;

    /**
     * Optimize pieces into sheets using guillotine algorithm.
     *
     * @param Collection $pieces
     * @param Material $material
     * @return array
     */
    public function optimize(Collection $pieces, Material $material): array
    {
        Log::info("Starting cutlist optimization for " . $pieces->count() . " unique pieces");

        $sheetDimensions = $material->getSheetDimensions();
        $sheets = [];
        $unplacedPieces = $this->expandPieces($pieces);

        // Sort pieces by area (largest first) for better optimization
        $unplacedPieces = $unplacedPieces->sortByDesc(function ($piece) {
            return $piece['width'] * $piece['height'];
        })->values();

        $sheetNumber = 1;

        while ($unplacedPieces->isNotEmpty()) {
            $sheet = $this->packSheet(
                $unplacedPieces,
                $sheetDimensions,
                $sheetNumber
            );

            $sheets[] = $sheet;
            $sheetNumber++;

            // Remove placed pieces
            $placedPieceIds = collect($sheet['placements'])->pluck('piece_id')->toArray();
            $unplacedPieces = $unplacedPieces->filter(function ($piece) use ($placedPieceIds) {
                return !in_array($piece['id'], $placedPieceIds);
            })->values();

            // Safety check to prevent infinite loop
            if ($sheetNumber > 100) {
                Log::warning("Exceeded maximum sheets limit (100). Stopping optimization.");
                break;
            }
        }

        Log::info("Optimization complete. Used {$sheetNumber} sheets.");

        return [
            'sheets' => $sheets,
            'total_sheets' => count($sheets),
            'total_waste_percent' => $this->calculateAverageWaste($sheets),
        ];
    }

    /**
     * Expand pieces by quantity (create individual items).
     *
     * @param Collection $pieces
     * @return Collection
     */
    protected function expandPieces(Collection $pieces): Collection
    {
        $expanded = collect();
        $id = 1;

        foreach ($pieces as $piece) {
            for ($i = 0; $i < $piece->qty; $i++) {
                $expanded->push([
                    'id' => $id++,
                    'piece_id' => $piece->id,
                    'name' => $piece->name,
                    'width' => (float) $piece->width_mm,
                    'height' => (float) $piece->height_mm,
                    'index' => $i + 1,
                ]);
            }
        }

        return $expanded;
    }

    /**
     * Pack pieces into a single sheet using guillotine algorithm.
     *
     * @param Collection $pieces
     * @param array $sheetDimensions
     * @param int $sheetNumber
     * @return array
     */
    protected function packSheet(Collection $pieces, array $sheetDimensions, int $sheetNumber): array
    {
        $sheetWidth = $sheetDimensions['width'];
        $sheetHeight = $sheetDimensions['height'];
        $placements = [];
        $freeRectangles = [
            ['x' => 0, 'y' => 0, 'width' => $sheetWidth, 'height' => $sheetHeight]
        ];

        foreach ($pieces as $piece) {
            $placed = false;

            // Try to place piece in each free rectangle
            foreach ($freeRectangles as $index => $rect) {
                $placement = $this->tryPlacePiece($piece, $rect);

                if ($placement) {
                    $placements[] = $placement;

                    // Split the free rectangle
                    $newRects = $this->splitRectangle($rect, $placement);

                    // Remove used rectangle and add new ones
                    unset($freeRectangles[$index]);
                    $freeRectangles = array_merge(array_values($freeRectangles), $newRects);

                    $placed = true;
                    break;
                }
            }

            if ($placed) {
                continue;
            }

            // If we couldn't place more pieces, break
            break;
        }

        $sheetArea = $sheetWidth * $sheetHeight;
        $usedArea = collect($placements)->sum(function ($p) {
            return $p['width'] * $p['height'];
        });

        $wastePercent = $sheetArea > 0 ? (1 - ($usedArea / $sheetArea)) * 100 : 0;
        $utilizationPercent = $sheetArea > 0 ? ($usedArea / $sheetArea) * 100 : 0;

        return [
            'sheet_number' => $sheetNumber,
            'sheet_type' => "{$sheetWidth}x{$sheetHeight}",
            'placements' => $placements,
            'waste_percent' => round($wastePercent, 2),
            'utilization_percent' => round($utilizationPercent, 2),
            'used_area' => $usedArea,
            'total_area' => $sheetArea,
        ];
    }

    /**
     * Try to place a piece in a rectangle.
     *
     * @param array $piece
     * @param array $rect
     * @return array|null
     */
    protected function tryPlacePiece(array $piece, array $rect): ?array
    {
        $pieceWidth = $piece['width'] + $this->kerfWidth;
        $pieceHeight = $piece['height'] + $this->kerfWidth;

        // Try normal orientation
        if ($pieceWidth <= $rect['width'] && $pieceHeight <= $rect['height']) {
            return [
                'piece_id' => $piece['piece_id'],
                'id' => $piece['id'],
                'name' => $piece['name'],
                'x' => $rect['x'],
                'y' => $rect['y'],
                'width' => $piece['width'],
                'height' => $piece['height'],
                'rotated' => false,
            ];
        }

        // Try rotated orientation (if allowed)
        if ($this->allowRotation && $pieceHeight <= $rect['width'] && $pieceWidth <= $rect['height']) {
            return [
                'piece_id' => $piece['piece_id'],
                'id' => $piece['id'],
                'name' => $piece['name'],
                'x' => $rect['x'],
                'y' => $rect['y'],
                'width' => $piece['height'],
                'height' => $piece['width'],
                'rotated' => true,
            ];
        }

        return null;
    }

    /**
     * Split rectangle after placing a piece (Guillotine algorithm).
     *
     * @param array $rect
     * @param array $placement
     * @return array
     */
    protected function splitRectangle(array $rect, array $placement): array
    {
        $newRects = [];

        $placementWidth = $placement['width'] + $this->kerfWidth;
        $placementHeight = $placement['height'] + $this->kerfWidth;

        // Right rectangle
        if ($rect['width'] > $placementWidth) {
            $newRects[] = [
                'x' => $rect['x'] + $placementWidth,
                'y' => $rect['y'],
                'width' => $rect['width'] - $placementWidth,
                'height' => $placementHeight,
            ];
        }

        // Top rectangle
        if ($rect['height'] > $placementHeight) {
            $newRects[] = [
                'x' => $rect['x'],
                'y' => $rect['y'] + $placementHeight,
                'width' => $rect['width'],
                'height' => $rect['height'] - $placementHeight,
            ];
        }

        return $newRects;
    }

    /**
     * Calculate average waste across all sheets.
     *
     * @param array $sheets
     * @return float
     */
    protected function calculateAverageWaste(array $sheets): float
    {
        if (empty($sheets)) {
            return 0;
        }

        $totalWaste = array_sum(array_column($sheets, 'waste_percent'));
        return round($totalWaste / count($sheets), 2);
    }

    /**
     * Set kerf width (blade thickness).
     *
     * @param float $width
     * @return self
     */
    public function setKerfWidth(float $width): self
    {
        $this->kerfWidth = $width;
        return $this;
    }

    /**
     * Enable or disable rotation.
     *
     * @param bool $allow
     * @return self
     */
    public function setAllowRotation(bool $allow): self
    {
        $this->allowRotation = $allow;
        return $this;
    }
}
