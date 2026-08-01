<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoutineTemplates\ArchiveRoutineTemplateRequest;
use App\Http\Requests\RoutineTemplates\StoreRoutineTemplateRequest;
use App\Http\Requests\RoutineTemplates\UpdateRoutineTemplateRequest;
use App\Models\Exercise;
use App\Models\RoutineTemplate;
use App\Modules\RoutineTemplates\Actions\ArchiveRoutineTemplate;
use App\Modules\RoutineTemplates\Actions\CreateRoutineTemplate;
use App\Modules\RoutineTemplates\Actions\UpdateRoutineTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoutineTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $templates = RoutineTemplate::query()
            ->where('status', RoutineTemplate::STATUS_ACTIVE)
            ->when($search !== '', function ($query) use ($search): void {
                $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
                $query->where('name', 'ilike', "%{$escaped}%");
            })
            ->withCount('exercises')
            ->orderBy('name')->orderBy('id')
            ->paginate(10)->withQueryString();

        return view('routine-templates.index', compact('templates', 'search'));
    }

    public function create(): View
    {
        return view('routine-templates.create');
    }

    public function store(StoreRoutineTemplateRequest $request, CreateRoutineTemplate $action): RedirectResponse
    {
        $template = $action->handle($request->validated());

        return redirect()->route('routine-templates.show', $template)
            ->with('status', 'Plantilla creada correctamente.');
    }

    public function show(RoutineTemplate $routineTemplate): View
    {
        $routineTemplate->load('exercises');

        return view('routine-templates.show', compact('routineTemplate'));
    }

    public function edit(RoutineTemplate $routineTemplate): View
    {
        $routineTemplate->load('exercises');

        return view('routine-templates.edit', compact('routineTemplate'));
    }

    public function update(UpdateRoutineTemplateRequest $request, RoutineTemplate $routineTemplate, UpdateRoutineTemplate $action): RedirectResponse
    {
        $template = $action->handle($routineTemplate, $request->validated());

        return redirect()->route('routine-templates.show', $template)
            ->with('status', 'Plantilla actualizada correctamente.');
    }

    public function destroy(ArchiveRoutineTemplateRequest $request, int $routineTemplate, ArchiveRoutineTemplate $action): RedirectResponse
    {
        $archived = $action->handle($routineTemplate);

        return redirect()->route('routine-templates.index')->with('status', $archived
            ? 'Plantilla archivada correctamente. Sus ejercicios y futuras copias independientes se conservan.'
            : 'La plantilla ya estaba archivada; no se realizó ningún cambio.');
    }

    public function exerciseSearch(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $exercises = Exercise::query()
            ->when($search !== '', function ($query) use ($search): void {
                $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
                $query->where(fn ($query) => $query->where('name', 'ilike', "%{$escaped}%")
                    ->orWhere('description', 'ilike', "%{$escaped}%"));
            })
            ->orderBy('name')->orderBy('id')->limit(10)
            ->get(['id', 'name', 'description', 'duration_seconds', 'sets', 'repetitions', 'material_url']);

        return response()->json(['data' => $exercises]);
    }
}
