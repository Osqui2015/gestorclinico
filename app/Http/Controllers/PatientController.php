<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\HealthInsurance;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $patients = Patient::query()
            ->with([
                'healthInsurances' => fn($query) => $query
                    ->select('health_insurances.id', 'health_insurances.name')
                    ->wherePivot('is_primary', true),
            ])
            ->when(
                request('search'),
                fn($query) =>
                $query->where('first_name', 'like', '%' . request('search') . '%')
                    ->orWhere('last_name', 'like', '%' . request('search') . '%')
                    ->orWhere('dni', 'like', '%' . request('search') . '%')
            )
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Patients/Index', [
            'patients' => $patients,
            'filters' => request()->only('search'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $healthInsurances = HealthInsurance::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return Inertia::render('Patients/Create', [
            'healthInsurances' => $healthInsurances,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePatientRequest $request)
    {
        $validated = $request->validated();
        $healthInsuranceId = $validated['health_insurance_id'] ?? null;
        $memberNumber = $validated['member_number'] ?? null;
        $obrasSocialesData = $validated['obra_social_data'] ?? null;
        $newHealthInsuranceName = $validated['new_health_insurance_name'] ?? null;
        $newHealthInsuranceCode = $validated['new_health_insurance_code'] ?? null;

        unset(
            $validated['health_insurance_id'],
            $validated['member_number'],
            $validated['obra_social_data'],
            $validated['new_health_insurance_name'],
            $validated['new_health_insurance_code']
        );

        $patient = Patient::create($validated);

        if ($newHealthInsuranceName) {
            $healthInsuranceId = $this->getOrCreateManualHealthInsurance($newHealthInsuranceName, $newHealthInsuranceCode);
        }

        // Si viene información de obra social seleccionada
        if ($obrasSocialesData) {
            $healthInsuranceId = $this->getOrCreateHealthInsurance($obrasSocialesData);
        }

        if ($healthInsuranceId) {
            $patient->healthInsurances()->sync([
                $healthInsuranceId => [
                    'is_primary' => true,
                    'member_number' => $memberNumber,
                ],
            ]);
        }

        return Redirect::route('patients.index')->with('success', 'Paciente creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        $medicalRecords = $patient->medicalRecords()
            ->with('doctor:id,name')
            ->orderByDesc('created_at')
            ->get();

        $primaryInsurance = $patient->healthInsurances()
            ->wherePivot('is_primary', true)
            ->first();

        return Inertia::render('Patients/ShowImproved', [
            'patient' => $patient,
            'medicalRecords' => $medicalRecords,
            'primaryInsurance' => $primaryInsurance
                ? [
                    'id' => $primaryInsurance->id,
                    'name' => $primaryInsurance->name,
                    'member_number' => $primaryInsurance->pivot?->member_number,
                ]
                : null,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient)
    {
        $healthInsurances = HealthInsurance::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
        $primaryInsurance = $patient->healthInsurances()
            ->wherePivot('is_primary', true)
            ->first();
        $primaryInsuranceId = optional($primaryInsurance)->id;
        $primaryMemberNumber = $primaryInsurance?->pivot?->member_number;

        return Inertia::render('Patients/Edit', [
            'patient' => $patient,
            'healthInsurances' => $healthInsurances,
            'primaryInsuranceId' => $primaryInsuranceId,
            'primaryMemberNumber' => $primaryMemberNumber,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $validated = $request->validated();
        $healthInsuranceId = $validated['health_insurance_id'] ?? null;
        $memberNumber = $validated['member_number'] ?? null;
        $obrasSocialesData = $validated['obra_social_data'] ?? null;
        $newHealthInsuranceName = $validated['new_health_insurance_name'] ?? null;
        $newHealthInsuranceCode = $validated['new_health_insurance_code'] ?? null;

        unset(
            $validated['health_insurance_id'],
            $validated['member_number'],
            $validated['obra_social_data'],
            $validated['new_health_insurance_name'],
            $validated['new_health_insurance_code']
        );

        $patient->update($validated);

        if ($newHealthInsuranceName) {
            $healthInsuranceId = $this->getOrCreateManualHealthInsurance($newHealthInsuranceName, $newHealthInsuranceCode);
        }

        // Si viene información de obra social seleccionada
        if ($obrasSocialesData) {
            $healthInsuranceId = $this->getOrCreateHealthInsurance($obrasSocialesData);
        }

        if ($healthInsuranceId) {
            $patient->healthInsurances()->sync([
                $healthInsuranceId => [
                    'is_primary' => true,
                    'member_number' => $memberNumber,
                ],
            ]);
        } else {
            $patient->healthInsurances()->detach();
        }

        return Redirect::route('patients.index')->with('success', 'Paciente actualizado exitosamente.');
    }

    /**
     * Obtener o crear una HealthInsurance desde datos de obra social
     *
     * @param array $obrasSocialesData Datos de la obra social (RNOS, nombre, etc.)
     * @return int|null ID de la HealthInsurance
     */
    protected function getOrCreateHealthInsurance(array $obrasSocialesData): ?int
    {
        if (empty($obrasSocialesData['rnos'])) {
            return null;
        }

        $rnos = $obrasSocialesData['rnos'];
        $nombre = $obrasSocialesData['nombre'] ?? 'Obra Social';

        // Buscar si ya existe en la BD por código RNOS
        $healthInsurance = HealthInsurance::where('code', $rnos)->first();

        if ($healthInsurance) {
            return $healthInsurance->id;
        }

        // Crear nueva HealthInsurance si no existe
        $healthInsurance = HealthInsurance::create([
            'name' => $nombre,
            'code' => $rnos,
            'phone' => $obrasSocialesData['telefonos'][0] ?? null,
            'email' => $obrasSocialesData['emails'][0] ?? null,
            'is_active' => true,
            'notes' => sprintf(
                'Importada de Obras Sociales Argentinas | Provincia: %s',
                $obrasSocialesData['provincia'] ?? 'N/A'
            ),
        ]);

        return $healthInsurance->id;
    }

    protected function getOrCreateManualHealthInsurance(string $name, ?string $code = null): int
    {
        $normalizedName = trim($name);
        $normalizedCode = $code ? trim($code) : null;

        if ($normalizedCode) {
            $existingByCode = HealthInsurance::where('code', $normalizedCode)->first();
            if ($existingByCode) {
                return $existingByCode->id;
            }
        }

        $existingByName = HealthInsurance::whereRaw('LOWER(name) = ?', [mb_strtolower($normalizedName)])->first();
        if ($existingByName) {
            if ($normalizedCode && !$existingByName->code) {
                $existingByName->update(['code' => $normalizedCode]);
            }
            return $existingByName->id;
        }

        $created = HealthInsurance::create([
            'name' => $normalizedName,
            'code' => $normalizedCode,
            'is_active' => true,
            'notes' => 'Creada manualmente desde formulario de paciente',
        ]);

        return $created->id;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        $patient->delete();

        return Redirect::route('patients.index')->with('success', 'Paciente eliminado exitosamente.');
    }
}
