<?php

namespace App\Http\Controllers;

use App\Http\Requests\Plans\ChangePlanStatusRequest;
use App\Http\Requests\Plans\DuplicatePlanRequest;
use App\Http\Requests\Plans\StorePlanRequest;
use App\Http\Requests\Plans\UpdatePlanRequest;
use App\Models\Patient;
use App\Models\Plan;
use App\Modules\Plans\Actions\ChangePlanStatus;
use App\Modules\Plans\Actions\CreatePlan;
use App\Modules\Plans\Actions\DuplicatePlan;
use App\Modules\Plans\Actions\UpdatePlan;
use App\Modules\Plans\Support\PlanActivationValidator;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $plans = Plan::query()->with('patient')->withCount('routines')
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'ilike', '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search).'%')->orWhereHas('patient', fn ($q) => $q->whereRaw("concat(first_names, ' ', last_names) ilike ? escape '\\\\'", ['%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search).'%']))))
            ->orderByDesc('starts_on')->orderByDesc('id')->paginate(10)->withQueryString();

        return view('plans.index', compact('plans', 'search'));
    }

    public function create(Request $request): View
    {
        $selectedPatient = $request->integer('patient') ? Patient::query()->find($request->integer('patient')) : null;
        $patients = Patient::query()->where('status', Patient::STATUS_ACTIVE)->orderBy('last_names')->orderBy('first_names')->get();

        return view('plans.create', compact('patients', 'selectedPatient'));
    }

    public function store(StorePlanRequest $request, CreatePlan $action): RedirectResponse
    {
        $plan = $action->handle($request->validated());

        return redirect()->route('plans.show', $plan)->with('status', 'Plan creado en pausa. Agrega sus rutinas para poder activarlo.');
    }

    public function show(Plan $plan, PlanActivationValidator $validator): View
    {
        $plan->load(['patient', 'routines.exercises', 'currentPublicLink']);
        $problems = $validator->problems($plan);
        $today = CarbonImmutable::now('America/Lima')->startOfDay();
        $currentRoutine = $plan->routines->first(fn ($routine) => $routine->starts_on->lte($today) && $routine->ends_on->gte($today));

        return view('plans.show', compact('plan', 'problems', 'currentRoutine'));
    }

    public function edit(Plan $plan): View
    {
        return view('plans.edit', compact('plan'));
    }

    public function update(UpdatePlanRequest $request, Plan $plan, UpdatePlan $action): RedirectResponse
    {
        $action->handle($plan, $request->validated());

        return redirect()->route('plans.show', $plan)->with('status', 'Plan actualizado correctamente.');
    }

    public function changeStatus(ChangePlanStatusRequest $request, Plan $plan, ChangePlanStatus $action): RedirectResponse
    {
        $updated = $action->handle($plan, $request->validated('status'));

        return redirect()->route('plans.show', $updated)->with('status', match ($updated->status) {
            Plan::STATUS_ACTIVE => 'Plan activado correctamente y enlace seguro preparado.', Plan::STATUS_PAUSED => 'Plan pausado. Su configuración y enlace se conservan.', default => 'Plan finalizado. Este estado es irreversible.'
        });
    }

    public function duplicateForm(Plan $plan): View
    {
        $patients = Patient::query()->where('status', Patient::STATUS_ACTIVE)->orderBy('last_names')->orderBy('first_names')->get();

        return view('plans.duplicate', compact('plan', 'patients'));
    }

    public function duplicate(DuplicatePlanRequest $request, Plan $plan, DuplicatePlan $action): RedirectResponse
    {
        $copy = $action->handle($plan, $request->validated());

        return redirect()->route('plans.show', $copy)->with('status', 'Plan duplicado como copia independiente en pausa. Revisa sus fechas antes de activarlo.');
    }
}
