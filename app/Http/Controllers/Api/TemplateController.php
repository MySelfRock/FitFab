<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * Display a listing of templates.
     */
    public function index(Request $request): JsonResponse
    {
        $templates = Template::active()
            ->select('id', 'name', 'slug', 'description', 'default_options', 'created_at')
            ->orderBy('name')
            ->get();

        return response()->json([
            'templates' => $templates,
        ]);
    }

    /**
     * Display the specified template.
     */
    public function show(Template $template): JsonResponse
    {
        return response()->json([
            'template' => $template,
        ]);
    }
}
