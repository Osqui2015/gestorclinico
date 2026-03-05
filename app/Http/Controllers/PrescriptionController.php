<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\MedicalRecord;
use App\Services\DigitalPrescriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class PrescriptionController extends Controller
{
    protected DigitalPrescriptionService $digitalPrescriptionService;

    public function __construct(DigitalPrescriptionService $digitalPrescriptionService)
    {
        $this->digitalPrescriptionService = $digitalPrescriptionService;
    }

    /**
     * Display prescription form for a patient
     */
    public function createForPatient($patientId)
    {
        $patient = Patient::findOrFail($patientId);

        // Only doctors can create prescriptions
        if (Auth::user()->role !== 'doctor') {
            abort(403, 'Solo los médicos pueden crear recetas.');
        }

        return Inertia::render('Prescriptions/Create', [
            'appointment' => null,
            'medicalRecord' => null,
            'patient' => $patient,
            'doctor' => Auth::user(),
        ]);
    }

    /**
     * Display prescription form for a medical record
     */
    public function createForMedicalRecord($patientId, $medicalRecordId)
    {
        $patient = Patient::findOrFail($patientId);
        $medicalRecord = MedicalRecord::findOrFail($medicalRecordId);

        // Check if medical record belongs to patient
        if ($medicalRecord->patient_id !== $patient->id) {
            abort(404);
        }

        // Check if user is the doctor for this record
        if ($medicalRecord->doctor_id !== Auth::id()) {
            abort(403, 'No tienes permiso para crear una receta para este registro.');
        }

        return Inertia::render('Prescriptions/Create', [
            'appointment' => null,
            'medicalRecord' => $medicalRecord,
            'patient' => $patient,
            'doctor' => Auth::user(),
        ]);
    }

    /**
     * Display prescription for the appointment
     */
    public function create($appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);

        // Check if user is the doctor for this appointment
        if ($appointment->doctor_id !== Auth::id()) {
            abort(403, 'No tienes permiso para crear una receta para esta cita.');
        }

        return Inertia::render('Prescriptions/Create', [
            'appointment' => $appointment,
            'medicalRecord' => null,
            'patient' => $appointment->patient,
            'doctor' => $appointment->doctor,
        ]);
    }

    /**
     * Store a newly created prescription in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'diagnosis' => 'required|string',
            'medications' => 'required|array|min:1',
            'medications.*.name' => 'required|string',
            'medications.*.nombre_generico' => 'nullable|string',
            'medications.*.nombre_comercial' => 'nullable|string',
            'medications.*.dosage' => 'required|string',
            'medications.*.frequency' => 'required|string',
            'medications.*.duration' => 'required|string',
            'medications.*.forma_farmaceutica' => 'nullable|string',
            'medications.*.cantidad' => 'nullable|integer|min:1',
            'instructions' => 'required|array|min:1',
            'instructions.*.description' => 'required|string',
            'notes' => 'nullable|string',
            // Campos adicionales ReNaPDiS
            'cie10_codigo' => 'nullable|string|max:10',
            'cie10_descripcion' => 'nullable|string',
            'obra_social' => 'nullable|string',
            'numero_afiliado' => 'nullable|string',
            'doctor_password' => 'nullable|string', // Para firma electrónica
            'otp' => 'nullable|string|size:6', // Código 2FA
        ]);

        $doctor = Auth::user();
        $patient = Patient::findOrFail($validated['patient_id']);

        // Crear receta digital conforme a ReNaPDiS
        $prescription = $this->digitalPrescriptionService->createDigitalPrescription(
            $validated,
            $doctor,
            $patient
        );

        if (!$prescription) {
            return back()->withErrors([
                'error' => 'No se pudo crear la receta digital. Verifique que su matrícula esté validada.'
            ])->withInput();
        }

        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', 'Receta digital creada exitosamente con CUIR: ' . $prescription->cuir);
    }

    /**
     * Display the prescription
     */
    public function show(Prescription $prescription)
    {
        // Check authorization
        if ($prescription->doctor_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403, 'No tienes permiso para ver esta receta.');
        }

        $prescription->load('patient', 'doctor', 'appointment', 'medicalRecord');

        return Inertia::render('Prescriptions/Show', [
            'prescription' => $prescription,
            'patient' => $prescription->patient,
            'doctor' => $prescription->doctor,
            'appointment' => $prescription->appointment,
            'medicalRecord' => $prescription->medicalRecord,
            'qr_code_url' => $prescription->qr_code_path ? asset('storage/' . $prescription->qr_code_path) : null,
            'is_valid' => $prescription->isValid(),
            'is_expired' => $prescription->isExpired(),
            'days_until_expiration' => $prescription->daysUntilExpiration(),
            'is_renapdis_compliant' => $prescription->isReNaPDiSCompliant(),
        ]);
    }

    /**
     * Generate prescription PDF
     */
    public function generatePrescriptionPDF(Prescription $prescription)
    {
        // Check authorization
        if ($prescription->doctor_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403, 'No tienes permiso para descargar esta receta.');
        }

        $prescription->load('patient', 'doctor');

        $pdf = Pdf::loadView('prescriptions.prescription-pdf', [
            'prescription' => $prescription,
            'patient' => $prescription->patient,
            'doctor' => $prescription->doctor,
        ])->setPaper('a4');

        return $pdf->download('receta_' . $prescription->patient->dni . '_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Generate instructions PDF
     */
    public function generateInstructionsPDF(Prescription $prescription)
    {
        // Check authorization
        if ($prescription->doctor_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403, 'No tienes permiso para descargar estas indicaciones.');
        }

        $prescription->load('patient', 'doctor');

        $pdf = Pdf::loadView('prescriptions.instructions-pdf', [
            'prescription' => $prescription,
            'patient' => $prescription->patient,
            'doctor' => $prescription->doctor,
        ])->setPaper('a4');

        return $pdf->download('indicaciones_' . $prescription->patient->dni . '_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $prescriptions = Prescription::where('doctor_id', Auth::id())
            ->with('patient', 'doctor')
            ->latest()
            ->paginate(20);

        return Inertia::render('Prescriptions/Index', [
            'prescriptions' => $prescriptions,
        ]);
    }

    /**
     * Show the form for editing a resource.
     */
    public function edit(Prescription $prescription)
    {
        if ($prescription->doctor_id !== Auth::id()) {
            abort(403, 'No tienes permiso para editar esta receta.');
        }

        return Inertia::render('Prescriptions/Edit', [
            'prescription' => $prescription,
            'patient' => $prescription->patient,
            'doctor' => $prescription->doctor,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prescription $prescription)
    {
        if ($prescription->doctor_id !== Auth::id()) {
            abort(403, 'No tienes permiso para editar esta receta.');
        }

        $validated = $request->validate([
            'diagnosis' => 'required|string',
            'medications' => 'required|array|min:1',
            'medications.*.name' => 'required|string',
            'medications.*.dosage' => 'required|string',
            'medications.*.frequency' => 'required|string',
            'medications.*.duration' => 'required|string',
            'instructions' => 'required|array|min:1',
            'instructions.*.description' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $prescription->update($validated);

        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', 'Receta actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prescription $prescription)
    {
        if ($prescription->doctor_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403, 'No tienes permiso para eliminar esta receta.');
        }

        $prescription->delete();

        return redirect()->route('prescriptions.index')
            ->with('success', 'Receta eliminada exitosamente.');
    }

    /**
     * Generate PDF from draft prescription data (before saving to database)
     */
    public function generateDraftPDF(Request $request)
    {
        try {
            $validated = $request->validate([
                'patient_id' => 'required|exists:patients,id',
                'diagnosis' => 'required|string',
                'medications' => 'required|array|min:1',
                'medications.*.name' => 'required|string',
                'medications.*.dosage' => 'required|string',
                'medications.*.frequency' => 'required|string',
                'medications.*.duration' => 'required|string',
                'instructions' => 'required|array|min:1',
                'instructions.*.description' => 'required|string',
                'notes' => 'nullable|string',
            ]);

            // Load patient
            $patient = Patient::findOrFail($validated['patient_id']);
            $doctor = Auth::user();

            // Create simple object with prescription data
            $prescription = (object) [
                'diagnosis' => $validated['diagnosis'],
                'medications' => $validated['medications'],
                'instructions' => $validated['instructions'],
                'notes' => $validated['notes'] ?? '',
            ];

            // Generate PDF using loadHTML instead of loadView to avoid Blade issues
            $html = view('prescriptions.draft-prescription-pdf', [
                'prescription' => $prescription,
                'patient' => $patient,
                'doctor' => $doctor,
            ])->render();

            $pdf = Pdf::loadHTML($html)->setPaper('a4');

            // Generate unique token
            $token = bin2hex(random_bytes(16));
            $filename = "temp_prescription_{$token}.pdf";

            // Save to temp storage
            $path = storage_path("app/temp/{$filename}");

            // Create temp directory if it doesn't exist
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $pdf->save($path);

            return response()->json([
                'token' => $token,
                'url' => route('prescriptions.temp', ['token' => $token])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error generating draft prescription PDF: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ], 500);
        }
    }

    /**
     * Serve and delete temporary PDF
     */
    public function serveTempPDF($token)
    {
        $filename = "temp_prescription_{$token}.pdf";
        $path = storage_path("app/temp/{$filename}");

        if (!file_exists($path)) {
            abort(404, 'El archivo temporal no existe o ya fue eliminado.');
        }

        // Return PDF and schedule deletion
        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="receta_borrador.pdf"',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Verify prescription by CUIR (Public endpoint for pharmacies)
     */
    public function verifyCUIR(Request $request)
    {
        $request->validate([
            'cuir' => 'required|string|max:100',
        ]);

        $verification = $this->digitalPrescriptionService->verifyPrescription($request->cuir);

        if (!$verification) {
            return response()->json([
                'valid' => false,
                'message' => 'CUIR no encontrado en el sistema.',
            ], 404);
        }

        return response()->json([
            'valid' => true,
            'data' => $verification,
        ]);
    }

    /**
     * Mark prescription as dispensed (For pharmacies)
     */
    public function markAsDispensed(Request $request, $cuir)
    {
        $validated = $request->validate([
            'farmacia_dispensadora' => 'required|string|max:255',
            'farmaceutico_matricula' => 'nullable|string|max:50',
        ]);

        $prescription = Prescription::where('cuir', $cuir)->firstOrFail();

        // Verificar que la receta esté válida y pendiente
        if (!$prescription->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'La receta no es válida para dispensar (vencida, ya dispensada o anulada).',
            ], 400);
        }

        // Marcar como dispensada
        $prescription->update([
            'estado_dispensacion' => 'dispensada',
            'fecha_dispensacion' => now(),
            'farmacia_dispensadora' => $validated['farmacia_dispensadora'],
            'log_modificaciones' => array_merge(
                $prescription->log_modificaciones ?? [],
                [[
                    'accion' => 'dispensada',
                    'fecha' => now()->toDateTimeString(),
                    'farmacia' => $validated['farmacia_dispensadora'],
                    'farmaceutico_matricula' => $validated['farmaceutico_matricula'] ?? null,
                ]]
            ),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Receta marcada como dispensada exitosamente.',
            'prescription' => [
                'cuir' => $prescription->cuir,
                'estado' => $prescription->estado_dispensacion,
                'fecha_dispensacion' => $prescription->fecha_dispensacion,
            ],
        ]);
    }

    /**
     * Cancel/Annul prescription (Doctor only)
     */
    public function annul(Prescription $prescription, Request $request)
    {
        // Check authorization
        if ($prescription->doctor_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403, 'No tienes permiso para anular esta receta.');
        }

        $validated = $request->validate([
            'motivo' => 'required|string|max:500',
        ]);

        // No se puede anular una receta ya dispensada
        if ($prescription->estado_dispensacion === 'dispensada') {
            return back()->withErrors([
                'error' => 'No se puede anular una receta que ya fue dispensada.',
            ]);
        }

        $prescription->update([
            'estado_dispensacion' => 'anulada',
            'log_modificaciones' => array_merge(
                $prescription->log_modificaciones ?? [],
                [[
                    'accion' => 'anulada',
                    'fecha' => now()->toDateTimeString(),
                    'doctor_id' => Auth::id(),
                    'motivo' => $validated['motivo'],
                ]]
            ),
        ]);

        return back()->with('success', 'Receta anulada exitosamente.');
    }
}
