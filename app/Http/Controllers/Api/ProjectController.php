<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateProjectJob;
use App\Jobs\NotifyProsJob;
use App\Models\Project;
use App\Services\FileExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Project::with(['template', 'material', 'user'])
            ->where('user_id', $user->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $projects = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($projects);
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'template_id' => 'required|exists:templates,id',
            'material_id' => 'required|exists:materials,id',
            'options' => 'required|array',
        ]);

        $project = Project::create([
            'user_id' => $request->user()->id,
            'template_id' => $validated['template_id'],
            'material_id' => $validated['material_id'],
            'name' => $validated['name'],
            'options' => $validated['options'],
            'status' => 'pending',
        ]);

        // Dispatch generation job
        GenerateProjectJob::dispatch($project);

        Log::info("Project {$project->id} created and queued for generation");

        return response()->json([
            'message' => 'Projeto criado com sucesso e enfileirado para geração',
            'project' => $project->load(['template', 'material']),
        ], 201);
    }

    /**
     * Display the specified project.
     */
    public function show(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->load([
            'template',
            'material',
            'pieces',
            'sheets',
            'files',
            'offers.professional.user',
        ]);

        return response()->json([
            'project' => $project,
        ]);
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'options' => 'sometimes|array',
        ]);

        $project->update($validated);

        // If options changed, regenerate
        if (isset($validated['options'])) {
            GenerateProjectJob::dispatch($project);
        }

        return response()->json([
            'message' => 'Projeto atualizado com sucesso',
            'project' => $project,
        ]);
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Request $request, Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->json([
            'message' => 'Projeto excluído com sucesso',
        ]);
    }

    /**
     * Regenerate project (re-run generation job).
     */
    public function regenerate(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $project->update(['status' => 'pending']);

        GenerateProjectJob::dispatch($project);

        return response()->json([
            'message' => 'Projeto enfileirado para regeneração',
            'project' => $project,
        ]);
    }

    /**
     * Download project file.
     */
    public function download(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $validated = $request->validate([
            'type' => 'required|in:pdf,svg,dxf,csv',
        ]);

        $file = $project->files()
            ->where('type', $validated['type'])
            ->latest()
            ->first();

        if (!$file) {
            return response()->json([
                'message' => 'Arquivo não encontrado',
            ], 404);
        }

        return response()->json([
            'file' => [
                'id' => $file->id,
                'filename' => $file->filename,
                'type' => $file->type,
                'size' => $file->size,
                'human_size' => $file->getHumanSize(),
                'download_url' => $file->getDownloadUrl(),
                'url' => $file->getUrl(),
            ],
        ]);
    }

    /**
     * Request quotes from professionals.
     */
    public function requestQuote(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius_km' => 'nullable|integer|min:1|max:200',
        ]);

        $radiusKm = $validated['radius_km'] ?? 50;

        // Dispatch notification job
        NotifyProsJob::dispatch(
            $project,
            $validated['lat'],
            $validated['lng'],
            $radiusKm
        );

        $project->update(['status' => 'quoted']);

        return response()->json([
            'message' => 'Solicitação de orçamento enviada para profissionais próximos',
            'radius_km' => $radiusKm,
        ]);
    }

    /**
     * Get project statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = [
            'total' => Project::where('user_id', $user->id)->count(),
            'pending' => Project::where('user_id', $user->id)->where('status', 'pending')->count(),
            'processing' => Project::where('user_id', $user->id)->where('status', 'processing')->count(),
            'ready' => Project::where('user_id', $user->id)->where('status', 'ready')->count(),
            'quoted' => Project::where('user_id', $user->id)->where('status', 'quoted')->count(),
        ];

        return response()->json($stats);
    }
}
