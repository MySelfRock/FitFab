<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TemplateManagementController extends Controller
{
    /**
     * Display a listing of templates.
     */
    public function index(): Response
    {
        $templates = Template::withCount('projects')
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Templates/Index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Show the form for creating a new template.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Templates/Create');
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'default_width' => 'required|numeric|min:0',
            'default_height' => 'required|numeric|min:0',
            'default_depth' => 'required|numeric|min:0',
            'thumbnail_url' => 'nullable|url|max:500',
            'active' => 'boolean',
            'configuration' => 'nullable|array',
        ]);

        $validated['active'] = $validated['active'] ?? true;

        Template::create($validated);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template criado com sucesso!');
    }

    /**
     * Show the form for editing the template.
     */
    public function edit(Template $template): Response
    {
        return Inertia::render('Admin/Templates/Edit', [
            'template' => $template,
        ]);
    }

    /**
     * Update the specified template.
     */
    public function update(Request $request, Template $template): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'default_width' => 'required|numeric|min:0',
            'default_height' => 'required|numeric|min:0',
            'default_depth' => 'required|numeric|min:0',
            'thumbnail_url' => 'nullable|url|max:500',
            'active' => 'boolean',
            'configuration' => 'nullable|array',
        ]);

        $template->update($validated);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template atualizado com sucesso!');
    }

    /**
     * Remove the specified template.
     */
    public function destroy(Template $template): RedirectResponse
    {
        // Check if template has projects
        if ($template->projects()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Não é possível excluir um template que possui projetos associados.');
        }

        $template->delete();

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template excluído com sucesso!');
    }

    /**
     * Toggle template active status.
     */
    public function toggleActive(Template $template): RedirectResponse
    {
        $template->update([
            'active' => !$template->active,
        ]);

        $status = $template->active ? 'ativado' : 'desativado';

        return redirect()->back()
            ->with('success', "Template {$status} com sucesso!");
    }
}
