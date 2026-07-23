<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = CarbonImmutable::now('America/Lima')->toDateString();
        $plans = Plan::query()->where('status', Plan::STATUS_ACTIVE)->whereDate('ends_on', '>=', $today)
            ->whereHas('patient', fn ($query) => $query->whereNull('patients.deleted_at'))
            ->with('patient:id,first_names,last_names,whatsapp_phone')->orderBy('starts_on')->orderBy('id')->get();

        return view('dashboard', compact('plans'));
    }
}
