<?php

namespace App\Http\Controllers;

use App\Http\Requests\Exercises\RetireExerciseRequest;
use App\Http\Requests\Exercises\StoreExerciseRequest;
use App\Http\Requests\Exercises\UpdateExerciseRequest;
use App\Models\Exercise;
use App\Modules\Exercises\Actions\CreateExercise;
use App\Modules\Exercises\Actions\RetireExercise;
use App\Modules\Exercises\Actions\UpdateExercise;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExerciseController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $exercises = Exercise::query()
            ->when($search !== '', function ($query) use ($search): void {
                $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);

                $query->where(function ($query) use ($escaped): void {
                    $query->where('name', 'ilike', "%{$escaped}%")
                        ->orWhere('description', 'ilike', "%{$escaped}%");
                });
            })
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('exercises.index', compact('exercises', 'search'));
    }

    public function create(): View
    {
        return view('exercises.create');
    }

    public function store(StoreExerciseRequest $request, CreateExercise $action): RedirectResponse
    {
        $exercise = $action->handle($request->validated());

        return redirect()->route('exercises.show', $exercise)
            ->with('status', 'Ejercicio registrado correctamente.');
    }

    public function show(Exercise $exercise): View
    {
        return view('exercises.show', compact('exercise'));
    }

    public function edit(Exercise $exercise): View
    {
        return view('exercises.edit', compact('exercise'));
    }

    public function update(UpdateExerciseRequest $request, Exercise $exercise, UpdateExercise $action): RedirectResponse
    {
        $exercise = $action->handle($exercise, $request->validated());

        return redirect()->route('exercises.show', $exercise)
            ->with('status', 'Ejercicio actualizado correctamente.');
    }

    public function destroy(RetireExerciseRequest $request, int $exercise, RetireExercise $action): RedirectResponse
    {
        $retired = $action->handle($exercise);

        return redirect()->route('exercises.index')->with(
            'status',
            $retired
                ? 'Ejercicio retirado correctamente. Las copias existentes no fueron modificadas.'
                : 'El ejercicio ya había sido retirado; no se realizó ningún cambio.'
        );
    }
}
