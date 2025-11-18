<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Inertia\Inertia;
use Inertia\Response;

class TemplateController extends Controller
{
    /**
     * Display a listing of templates.
     */
    public function index(): Response
    {
        $templates = Template::where('active', true)->get();

        return Inertia::render('Templates/Index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Display the specified template.
     */
    public function show(Template $template): Response
    {
        return Inertia::render('Templates/Show', [
            'template' => $template,
        ]);
    }
}
