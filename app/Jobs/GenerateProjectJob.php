<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\Sheet;
use App\Services\CutlistService;
use App\Services\FileExportService;
use App\Services\PricingService;
use App\Services\ProjectBuilderService;
use App\Services\SheetLayoutService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateProjectJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Project $project
    ) {
        $this->onQueue('projects');
    }

    /**
     * Execute the job.
     */
    public function handle(
        ProjectBuilderService $builderService,
        CutlistService $cutlistService,
        SheetLayoutService $layoutService,
        FileExportService $fileExportService,
        PricingService $pricingService
    ): void {
        Log::info("Starting project generation for project {$this->project->id}");

        try {
            DB::beginTransaction();

            // Update status to processing
            $this->project->update(['status' => 'processing']);

            // Step 1: Build pieces from template
            Log::info("Step 1: Building pieces");
            $builderService->build($this->project);

            // Step 2: Optimize cutlist
            Log::info("Step 2: Optimizing cutlist");
            $this->project->load(['pieces', 'material']);

            if (!$this->project->material) {
                throw new \Exception('Project has no material assigned');
            }

            $optimization = $cutlistService->optimize(
                $this->project->pieces,
                $this->project->material
            );

            // Step 3: Create sheet records and generate SVG layouts
            Log::info("Step 3: Creating sheets and layouts");
            $this->createSheets($optimization['sheets'], $layoutService);

            // Step 4: Calculate pricing
            Log::info("Step 4: Calculating pricing");
            $pricingService->updateProjectPrice($this->project);

            // Step 5: Update project metrics
            Log::info("Step 5: Updating metrics");
            $this->updateMetrics($optimization);

            // Step 6: Export files
            Log::info("Step 6: Exporting files");
            $fileExportService->exportProjectFiles($this->project, ['csv', 'svg']);

            // Update status to ready
            $this->project->update(['status' => 'ready']);

            DB::commit();

            Log::info("Project {$this->project->id} generated successfully");

            // Dispatch PDF rendering job (async)
            RenderPdfJob::dispatch($this->project);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Error generating project {$this->project->id}: {$e->getMessage()}");
            Log::error($e->getTraceAsString());

            $this->project->update([
                'status' => 'failed',
                'metrics' => array_merge($this->project->metrics ?? [], [
                    'error' => $e->getMessage(),
                    'failed_at' => now()->toIso8601String(),
                ]),
            ]);

            throw $e;
        }
    }

    /**
     * Create sheet records from optimization results.
     *
     * @param array $sheets
     * @param SheetLayoutService $layoutService
     * @return void
     */
    protected function createSheets(array $sheets, SheetLayoutService $layoutService): void
    {
        // Delete existing sheets
        $this->project->sheets()->delete();

        foreach ($sheets as $sheetData) {
            // Generate SVG for this sheet
            $svg = $layoutService->generateSvg($sheetData);

            Sheet::create([
                'project_id' => $this->project->id,
                'sheet_number' => $sheetData['sheet_number'],
                'sheet_type' => $sheetData['sheet_type'],
                'layout_data' => [
                    'pieces' => $sheetData['placements'],
                    'metrics' => [
                        'used_area' => $sheetData['used_area'],
                        'total_area' => $sheetData['total_area'],
                    ],
                ],
                'layout_svg' => $svg,
                'waste_percent' => $sheetData['waste_percent'],
                'utilization_percent' => $sheetData['utilization_percent'],
            ]);
        }

        Log::info("Created " . count($sheets) . " sheet records");
    }

    /**
     * Update project metrics.
     *
     * @param array $optimization
     * @return void
     */
    protected function updateMetrics(array $optimization): void
    {
        $metrics = $this->project->metrics ?? [];

        $metrics['cutlist'] = [
            'total_sheets' => $optimization['total_sheets'],
            'average_waste_percent' => $optimization['total_waste_percent'],
            'generated_at' => now()->toIso8601String(),
        ];

        $metrics['pieces'] = [
            'total_pieces' => $this->project->pieces()->sum('qty'),
            'unique_pieces' => $this->project->pieces()->count(),
        ];

        $this->project->update(['metrics' => $metrics]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateProjectJob failed for project {$this->project->id}: {$exception->getMessage()}");

        $this->project->update([
            'status' => 'failed',
            'metrics' => array_merge($this->project->metrics ?? [], [
                'error' => $exception->getMessage(),
                'failed_at' => now()->toIso8601String(),
            ]),
        ]);
    }
}
