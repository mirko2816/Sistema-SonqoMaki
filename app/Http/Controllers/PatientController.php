<?php

namespace App\Http\Controllers;

use App\Http\Requests\Patients\ArchivePatientRequest;
use App\Http\Requests\Patients\ChangePatientStatusRequest;
use App\Http\Requests\Patients\StorePatientRequest;
use App\Http\Requests\Patients\UpdatePatientRequest;
use App\Models\Patient;
use App\Modules\Patients\Actions\ArchivePatient;
use App\Modules\Patients\Actions\ChangePatientStatus;
use App\Modules\Patients\Actions\CreatePatient;
use App\Modules\Patients\Actions\UpdatePatient;
use App\Modules\Patients\Support\PatientDataNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function archived(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $patients = Patient::onlyTrashed()
            ->when($search !== '', function ($query) use ($search): void {
                $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
                $phone = PatientDataNormalizer::phone($search);

                $query->where(function ($query) use ($escaped, $phone): void {
                    $query->where('first_names', 'ilike', "%{$escaped}%")
                        ->orWhere('last_names', 'ilike', "%{$escaped}%")
                        ->orWhere('dni', 'like', "%{$escaped}%")
                        ->orWhere('whatsapp_phone', 'like', "%{$escaped}%");

                    if (is_string($phone) && $phone !== $escaped) {
                        $query->orWhere('whatsapp_phone', 'like', "%{$phone}%");
                    }
                });
            })
            ->orderBy('last_names')
            ->orderBy('first_names')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('patients.archived', compact('patients', 'search'));
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = in_array($request->query('status'), [Patient::STATUS_ACTIVE, Patient::STATUS_INACTIVE], true)
            ? $request->query('status')
            : null;

        $patients = Patient::query()
            ->when($search !== '', function ($query) use ($search): void {
                $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
                $phone = PatientDataNormalizer::phone($search);

                $query->where(function ($query) use ($escaped, $phone): void {
                    $query->where('first_names', 'ilike', "%{$escaped}%")
                        ->orWhere('last_names', 'ilike', "%{$escaped}%")
                        ->orWhere('dni', 'like', "%{$escaped}%")
                        ->orWhere('whatsapp_phone', 'like', "%{$escaped}%");

                    if (is_string($phone) && $phone !== $escaped) {
                        $query->orWhere('whatsapp_phone', 'like', "%{$phone}%");
                    }
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderBy('last_names')
            ->orderBy('first_names')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('patients.index', compact('patients', 'search', 'status'));
    }

    public function create(): View
    {
        return view('patients.create');
    }

    public function store(StorePatientRequest $request, CreatePatient $action): RedirectResponse
    {
        $patient = $action->handle($request->validated());

        return redirect()->route('patients.show', $patient)
            ->with('status', 'Paciente registrado correctamente.');
    }

    public function show(Patient $patient): View
    {
        $patient->load(['plans' => fn ($query) => $query->withCount('routines')]);

        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient): View
    {
        return view('patients.edit', compact('patient'));
    }

    public function update(UpdatePatientRequest $request, Patient $patient, UpdatePatient $action): RedirectResponse
    {
        $patient = $action->handle($patient, $request->validated());

        return redirect()->route('patients.show', $patient)
            ->with('status', 'Datos del paciente actualizados correctamente.');
    }

    public function changeStatus(ChangePatientStatusRequest $request, Patient $patient, ChangePatientStatus $action): RedirectResponse
    {
        $status = $request->validated('status');
        $unchanged = $patient->status === $status;
        $action->handle($patient, $status);

        return redirect()->route('patients.show', $patient)
            ->with('status', $unchanged
                ? 'El paciente ya tenía el estado solicitado.'
                : ($status === Patient::STATUS_ACTIVE ? 'Paciente activado correctamente.' : 'Paciente inactivado correctamente.'));
    }

    public function destroy(ArchivePatientRequest $request, Patient $patient, ArchivePatient $action): RedirectResponse
    {
        $action->handle($patient);

        return redirect()->route('patients.index')
            ->with('status', 'Paciente archivado correctamente. Sus datos permanecen conservados.');
    }
}
