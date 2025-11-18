<?php

namespace App\Services;

use App\Models\Piece;
use App\Models\Project;
use App\Models\Template;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectBuilderService
{
    /**
     * Build project pieces from template and options.
     *
     * @param Project $project
     * @return array
     * @throws \Exception
     */
    public function build(Project $project): array
    {
        DB::beginTransaction();

        try {
            Log::info("Building project {$project->id} from template");

            $template = $project->template;
            if (!$template) {
                throw new \Exception('Project has no template');
            }

            $pieces = $this->generatePiecesFromTemplate($project, $template);

            Log::info("Generated {$pieces->count()} pieces for project {$project->id}");

            DB::commit();

            return [
                'success' => true,
                'pieces_count' => $pieces->count(),
                'pieces' => $pieces->toArray(),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error building project {$project->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Generate pieces from template definition.
     *
     * @param Project $project
     * @param Template $template
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function generatePiecesFromTemplate(Project $project, Template $template)
    {
        $piecesDefinition = $template->pieces_definition;
        $options = $project->options;
        $pieces = collect();

        foreach ($piecesDefinition as $index => $pieceDefinition) {
            $piece = $this->createPieceFromDefinition(
                $project,
                $pieceDefinition,
                $options,
                $index
            );

            if ($piece) {
                $pieces->push($piece);
            }
        }

        return $pieces;
    }

    /**
     * Create a piece from definition and options.
     *
     * @param Project $project
     * @param array $definition
     * @param array $options
     * @param int $sortOrder
     * @return Piece
     */
    protected function createPieceFromDefinition(
        Project $project,
        array $definition,
        array $options,
        int $sortOrder
    ): Piece {
        // Calculate dimensions based on definition and user options
        $width = $this->calculateDimension($definition['width'] ?? 0, $options);
        $height = $this->calculateDimension($definition['height'] ?? 0, $options);
        $thickness = $project->material ?
            (float) $project->material->thickness_mm :
            (float) ($definition['thickness'] ?? 15);

        // Determine quantity
        $qty = $this->calculateQuantity($definition, $options);

        // Build piece options (edge banding, holes, etc)
        $pieceOptions = [
            'edge_banding' => $definition['edge_banding'] ?? [],
            'holes' => $definition['holes'] ?? [],
            'grooves' => $definition['grooves'] ?? [],
        ];

        return Piece::create([
            'project_id' => $project->id,
            'name' => $definition['name'] ?? "Peça {$sortOrder}",
            'qty' => $qty,
            'width_mm' => $width,
            'height_mm' => $height,
            'thickness_mm' => $thickness,
            'options' => $pieceOptions,
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Calculate dimension based on formula and options.
     *
     * @param mixed $dimension
     * @param array $options
     * @return float
     */
    protected function calculateDimension($dimension, array $options): float
    {
        // If dimension is a number, return it
        if (is_numeric($dimension)) {
            return (float) $dimension;
        }

        // If dimension is a formula (string), evaluate it
        if (is_string($dimension)) {
            return $this->evaluateFormula($dimension, $options);
        }

        // If dimension is an array with formula
        if (is_array($dimension) && isset($dimension['formula'])) {
            return $this->evaluateFormula($dimension['formula'], $options);
        }

        return 0;
    }

    /**
     * Evaluate formula with options values.
     * Simple formula parser for expressions like "width - 40" or "height * 2".
     *
     * @param string $formula
     * @param array $options
     * @return float
     */
    protected function evaluateFormula(string $formula, array $options): float
    {
        // Replace option variables with their values
        $expression = $formula;

        foreach ($options as $key => $value) {
            if (is_numeric($value)) {
                $expression = str_replace($key, $value, $expression);
            }
        }

        // Security: only allow numbers and basic math operators
        if (!preg_match('/^[\d\s\+\-\*\/\(\)\.]+$/', $expression)) {
            Log::warning("Invalid formula: {$formula}");
            return 0;
        }

        try {
            // Safe evaluation using PHP's built-in eval with restrictions
            // Note: In production, consider using a proper math parser library
            $result = eval("return {$expression};");
            return (float) $result;
        } catch (\Throwable $e) {
            Log::error("Error evaluating formula '{$formula}': {$e->getMessage()}");
            return 0;
        }
    }

    /**
     * Calculate quantity based on definition and options.
     *
     * @param array $definition
     * @param array $options
     * @return int
     */
    protected function calculateQuantity(array $definition, array $options): int
    {
        if (!isset($definition['qty'])) {
            return 1;
        }

        $qty = $definition['qty'];

        // If qty is a number
        if (is_numeric($qty)) {
            return (int) $qty;
        }

        // If qty is a reference to an option
        if (is_string($qty) && isset($options[$qty])) {
            return (int) $options[$qty];
        }

        // If qty is a formula
        if (is_string($qty)) {
            return (int) $this->evaluateFormula($qty, $options);
        }

        return 1;
    }

    /**
     * Recalculate project pieces when options change.
     *
     * @param Project $project
     * @return array
     */
    public function rebuild(Project $project): array
    {
        // Delete existing pieces
        $project->pieces()->delete();

        // Rebuild
        return $this->build($project);
    }
}
