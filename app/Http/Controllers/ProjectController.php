<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateProjectJob;
use App\Models\Material;
use App\Models\Project;
use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(Request $request): Response
    {
        $projects = $request->user()
            ->projects()
            ->with(['template', 'material'])
            ->latest()
            ->paginate(12);

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
        ]);
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(): Response
    {
        $templates = Template::where('active', true)->get();
        $materials = Material::where('active', true)->get();

        return Inertia::render('Projects/Create', [
            'templates' => $templates,
            'materials' => $materials,
        ]);
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:templates,id',
            'material_id' => 'required|exists:materials,id',
            'name' => 'required|string|max:255',
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

        GenerateProjectJob::dispatch($project);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Projeto criado! Estamos processando seus cortes...');
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project): Response
    {
        $this->authorize('view', $project);

        $project->load(['template', 'material', 'pieces', 'sheets', 'files']);

        return Inertia::render('Projects/Show', [
            'project' => $project,
        ]);
    }

    /**
     * Regenerate the project (re-process cutlist and files).
     */
    public function regenerate(Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        // Reset project status
        $project->update(['status' => 'pending']);

        // Delete existing pieces, sheets, and files
        $project->pieces()->delete();
        $project->sheets()->delete();
        $project->files()->delete();

        // Dispatch job to regenerate
        GenerateProjectJob::dispatch($project);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Projeto em processamento. Aguarde alguns instantes...');
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Projeto excluído com sucesso!');
    }
}
