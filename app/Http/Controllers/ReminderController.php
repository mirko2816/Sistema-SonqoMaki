<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reminders\SaveReminderConfigurationRequest;
use App\Models\Plan;
use App\Modules\Reminders\Actions\SaveReminderConfiguration;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReminderController extends Controller
{
    public function index(): View
    {
        $plans = Plan::query()
            ->with(['patient:id,first_names,last_names', 'reminderConfiguration' => fn ($query) => $query->withCount('schedules')])
            ->orderByDesc('starts_on')->orderByDesc('id')->paginate(10);

        return view('reminders.index', compact('plans'));
    }

    public function edit(Plan $plan): View
    {
        $plan->load(['patient', 'reminderConfiguration.schedules']);

        return view('reminders.edit', compact('plan'));
    }

    public function update(SaveReminderConfigurationRequest $request, Plan $plan, SaveReminderConfiguration $action): RedirectResponse
    {
        $configuration = $action->handle($plan, $request->validated());

        return redirect()->route('reminders.edit', $plan)->with(
            'status',
            $configuration->is_active ? 'Recordatorios guardados y activados.' : 'Recordatorios guardados en pausa. Los horarios se conservaron.'
        );
    }
}
