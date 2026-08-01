<?php

namespace App\Http\Controllers;

use App\Http\Requests\Plans\CopyRoutineTemplateRequest;
use App\Http\Requests\Plans\RoutineRequest;
use App\Models\Plan;
use App\Models\Routine;
use App\Models\RoutineTemplate;
use App\Modules\Plans\Actions\CopyRoutineTemplate;
use App\Modules\Plans\Actions\DeleteRoutine;
use App\Modules\Plans\Actions\SaveRoutine;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlanRoutineController extends Controller
{
    public function create(Plan $plan): View
    {
        return view('plans.routines.create', compact('plan'));
    }

    public function store(RoutineRequest $request, Plan $plan, SaveRoutine $action): RedirectResponse
    {
        $routine = $action->handle($plan, null, $request->validated());

        return redirect()->route('plans.show', $plan)->with('status', "Rutina «{$routine->name}» agregada.");
    }

    public function edit(Plan $plan, Routine $routine): View
    {
        abort_unless($routine->plan_id === $plan->id, 404);
        $routine->load('exercises');

        return view('plans.routines.edit', compact('plan', 'routine'));
    }

    public function update(RoutineRequest $request, Plan $plan, Routine $routine, SaveRoutine $action): RedirectResponse
    {
        abort_unless($routine->plan_id === $plan->id, 404);
        $action->handle($plan, $routine, $request->validated());

        return redirect()->route('plans.show', $plan)->with('status', 'Rutina y ejercicios actualizados.');
    }

    public function destroy(Plan $plan, Routine $routine, DeleteRoutine $action): RedirectResponse
    {
        abort_unless($routine->plan_id === $plan->id, 404);
        $action->handle($plan, $routine);

        return redirect()->route('plans.show', $plan)->with('status', 'Rutina retirada del plan.');
    }

    public function copyForm(Plan $plan): View
    {
        $templates = RoutineTemplate::query()->where('status', RoutineTemplate::STATUS_ACTIVE)->withCount('exercises')->orderBy('name')->get();

        return view('plans.routines.copy', compact('plan', 'templates'));
    }

    public function copy(CopyRoutineTemplateRequest $request, Plan $plan, CopyRoutineTemplate $action): RedirectResponse
    {
        $action->handle($plan, $request->validated());

        return redirect()->route('plans.show', $plan)->with('status', 'Plantilla copiada como rutina independiente.');
    }
}
