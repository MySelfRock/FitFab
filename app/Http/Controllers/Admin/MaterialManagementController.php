<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MaterialManagementController extends Controller
{
    /**
     * Display a listing of materials.
     */
    public function index(): Response
    {
        $materials = Material::withCount('projects')
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Materials/Index', [
            'materials' => $materials,
        ]);
    }

    /**
     * Show the form for creating a new material.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Materials/Create');
    }

    /**
     * Store a newly created material.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'thickness' => 'required|numeric|min:0',
            'width' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'price_per_sheet' => 'required|numeric|min:0',
            'density' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:100',
            'finish' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'active' => 'boolean',
            'properties' => 'nullable|array',
        ]);

        $validated['active'] = $validated['active'] ?? true;

        Material::create($validated);

        return redirect()->route('admin.materials.index')
            ->with('success', 'Material criado com sucesso!');
    }

    /**
     * Show the form for editing the material.
     */
    public function edit(Material $material): Response
    {
        return Inertia::render('Admin/Materials/Edit', [
            'material' => $material,
        ]);
    }

    /**
     * Update the specified material.
     */
    public function update(Request $request, Material $material): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'thickness' => 'required|numeric|min:0',
            'width' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'price_per_sheet' => 'required|numeric|min:0',
            'density' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:100',
            'finish' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'active' => 'boolean',
            'properties' => 'nullable|array',
        ]);

        $material->update($validated);

        return redirect()->route('admin.materials.index')
            ->with('success', 'Material atualizado com sucesso!');
    }

    /**
     * Remove the specified material.
     */
    public function destroy(Material $material): RedirectResponse
    {
        // Check if material has projects
        if ($material->projects()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Não é possível excluir um material que possui projetos associados.');
        }

        $material->delete();

        return redirect()->route('admin.materials.index')
            ->with('success', 'Material excluído com sucesso!');
    }

    /**
     * Toggle material active status.
     */
    public function toggleActive(Material $material): RedirectResponse
    {
        $material->update([
            'active' => !$material->active,
        ]);

        $status = $material->active ? 'ativado' : 'desativado';

        return redirect()->back()
            ->with('success', "Material {$status} com sucesso!");
    }
}
