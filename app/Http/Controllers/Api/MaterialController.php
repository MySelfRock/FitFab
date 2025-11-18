<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    /**
     * Display a listing of materials.
     */
    public function index(Request $request): JsonResponse
    {
        $materials = Material::active()
            ->orderBy('type')
            ->orderBy('thickness_mm')
            ->get();

        return response()->json([
            'materials' => $materials,
        ]);
    }

    /**
     * Display the specified material.
     */
    public function show(Material $material): JsonResponse
    {
        return response()->json([
            'material' => $material,
        ]);
    }
}
