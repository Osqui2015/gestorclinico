<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use App\Http\Requests\StoreMedicalRecordRequest;
use App\Http\Requests\UpdateMedicalRecordRequest;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class MedicalRecordController extends Controller
{
    /**
     * Display a listing of the resource (by patient).
     */
    public function index(Patient $patient)
    {
        $medicalRecords = $patient->medicalRecords()
            ->with('doctor:id,name')
            ->orderByDesc('created_at')
            ->paginate(10);

        return Inertia::render('Patients/MedicalRecords/Index', [
            'patient' => $patient,
            'medicalRecords' => $medicalRecords,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Patient $patient)
    {
        $isFirstConsultation = !MedicalRecord::where('patient_id', $patient->id)
            ->where('doctor_id', Auth::id())
            ->exists();

        return Inertia::render('Patients/MedicalRecords/Create', [
            'patient' => $patient,
            'isFirstConsultation' => $isFirstConsultation,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMedicalRecordRequest $request, Patient $patient)
    {
        // Automatically set the doctor_id from current user
        $validated = $request->validated();
        $validated['is_first_consultation'] = (bool) ($validated['is_first_consultation'] ?? false);

        $medicalRecord = MedicalRecord::create(array_merge($validated, [
            'patient_id' => $patient->id,
            'doctor_id' => Auth::id(),
        ]));

        if (!empty($validated['prescriptions'])) {
            foreach ($validated['prescriptions'] as $prescriptionData) {
                Prescription::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => Auth::id(),
                    'appointment_id' => null,
                    'medical_record_id' => $medicalRecord->id,
                    'diagnosis' => $prescriptionData['diagnosis'],
                    'medications' => $prescriptionData['medications'],
                    'instructions' => $prescriptionData['instructions'],
                    'notes' => $prescriptionData['notes'] ?? null,
                    'status' => 'pending',
                ]);
            }
        }

        return Redirect::route('patients.show', $patient->id)->with('success', 'Registro médico creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient, MedicalRecord $medicalRecord)
    {
        // Ensure medical record belongs to patient
        if ($medicalRecord->patient_id !== $patient->id) {
            abort(404);
        }

        $medicalRecord->load('doctor', 'prescriptions');

        $currentUser = Auth::user();
        $isAdmin = $currentUser instanceof User && $currentUser->isAdmin();

        $canEdit = Auth::id() === $medicalRecord->doctor_id || $isAdmin;
        $canDelete = Auth::id() === $medicalRecord->doctor_id || $isAdmin;

        return Inertia::render('Patients/MedicalRecords/Show', [
            'patient' => $patient,
            'medicalRecord' => $medicalRecord,
            'can' => [
                'edit' => $canEdit,
                'delete' => $canDelete,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient, MedicalRecord $medicalRecord)
    {
        // Ensure medical record belongs to patient
        // Only allow editing by the doctor who created it
        if ($medicalRecord->patient_id !== $patient->id) {
            abort(404);
        }

        $currentUser = Auth::user();
        $isAdmin = $currentUser instanceof User && $currentUser->isAdmin();

        if (Auth::id() !== $medicalRecord->doctor_id && !$isAdmin) {
            abort(403, 'No tienes permiso para editar este registro.');
        }

        return Inertia::render('Patients/MedicalRecords/Edit', [
            'patient' => $patient,
            'medicalRecord' => $medicalRecord,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMedicalRecordRequest $request, Patient $patient, MedicalRecord $medicalRecord)
    {
        // Ensure medical record belongs to patient
        if ($medicalRecord->patient_id !== $patient->id) {
            abort(404);
        }

        // Only allow updating by the doctor who created it or admin
        $currentUser = Auth::user();
        $isAdmin = $currentUser instanceof User && $currentUser->isAdmin();

        if (Auth::id() !== $medicalRecord->doctor_id && !$isAdmin) {
            abort(403, 'No tienes permiso para editar este registro.');
        }

        $medicalRecord->update($request->validated());

        return Redirect::route('patients.show', $patient->id)->with('success', 'Registro médico actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient, MedicalRecord $medicalRecord)
    {
        // Ensure medical record belongs to patient
        if ($medicalRecord->patient_id !== $patient->id) {
            abort(404);
        }

        // Only allow deleting by the doctor who created it or admin
        $currentUser = Auth::user();
        $isAdmin = $currentUser instanceof User && $currentUser->isAdmin();

        if (Auth::id() !== $medicalRecord->doctor_id && !$isAdmin) {
            abort(403, 'No tienes permiso para eliminar este registro.');
        }

        $medicalRecord->delete();

        return Redirect::route('patients.show', $patient->id)->with('success', 'Registro médico eliminado exitosamente.');
    }
}
