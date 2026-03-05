<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Services\HL7FHIRService;
use App\Services\AuditService;
use Illuminate\Http\Request;

class FHIRController extends Controller
{
    protected HL7FHIRService $fhirService;
    protected AuditService $auditService;

    public function __construct(HL7FHIRService $fhirService, AuditService $auditService)
    {
        $this->fhirService = $fhirService;
        $this->auditService = $auditService;
    }

    /**
     * Get prescription as FHIR MedicationRequest (JSON)
     *
     * @param string $cuir
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMedicationRequestJSON($cuir)
    {
        $prescription = Prescription::where('cuir', $cuir)->firstOrFail();

        // Audit log
        $this->auditService->logView('Prescription', $prescription->id, $cuir);
        $this->auditService->logExported('Prescription', $prescription->id, 'hl7-fhir-json', $cuir);

        return response()->json(
            $this->fhirService->prescriptionToMedicationRequest($prescription),
            200,
            ['Content-Type' => 'application/fhir+json']
        );
    }

    /**
     * Get prescription as FHIR MedicationRequest (XML)
     *
     * @param string $cuir
     * @return \Illuminate\Http\Response
     */
    public function getMedicationRequestXML($cuir)
    {
        $prescription = Prescription::where('cuir', $cuir)->firstOrFail();

        // Audit log
        $this->auditService->logView('Prescription', $prescription->id, $cuir);
        $this->auditService->logExported('Prescription', $prescription->id, 'hl7-fhir-xml', $cuir);

        return response(
            $this->fhirService->exportToXML($prescription),
            200,
            ['Content-Type' => 'application/fhir+xml']
        );
    }

    /**
     * Get prescription bundle (FHIR collection)
     *
     * @param string $cuir
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPrescriptionBundle($cuir)
    {
        $prescription = Prescription::where('cuir', $cuir)->firstOrFail();

        // Audit log
        $this->auditService->logView('Prescription', $prescription->id, $cuir);
        $this->auditService->logExported('Prescription', $prescription->id, 'hl7-fhir-bundle', $cuir);

        return response()->json(
            $this->fhirService->createPrescriptionBundle($prescription),
            200,
            ['Content-Type' => 'application/fhir+json']
        );
    }

    /**
     * Get patient as FHIR Patient resource
     *
     * @param int $patientId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPatient($patientId)
    {
        $prescription = Prescription::where('patient_id', $patientId)->firstOrFail();

        return response()->json(
            $this->fhirService->patientToFHIRPatient($prescription->patient),
            200,
            ['Content-Type' => 'application/fhir+json']
        );
    }

    /**
     * Get practitioner/doctor as FHIR Practitioner resource
     *
     * @param int $doctorId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPractitioner($doctorId)
    {
        $doctor = $prescription = Prescription::where('doctor_id', $doctorId)->firstOrFail()->doctor;

        return response()->json(
            $this->fhirService->doctorToFHIRPractitioner($doctor),
            200,
            ['Content-Type' => 'application/fhir+json']
        );
    }

    /**
     * Search prescriptions by CUIR
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $cuir = $request->query('cuir');

        if (!$cuir) {
            return response()->json([
                'error' => 'El parámetro CUIR es requerido',
            ], 400);
        }

        $prescription = Prescription::where('cuir', $cuir)->first();

        if (!$prescription) {
            return response()->json([
                'resourceType' => 'OperationOutcome',
                'issue' => [
                    [
                        'severity' => 'error',
                        'code' => 'not-found',
                        'diagnostics' => 'No se encontró receta con el CUIR especificado'
                    ]
                ]
            ], 404);
        }

        // Audit log
        $this->auditService->logView('Prescription', $prescription->id, $cuir);

        return response()->json([
            'resourceType' => 'Bundle',
            'type' => 'searchset',
            'total' => 1,
            'entry' => [
                [
                    'fullUrl' => route('api.fhir.medication-request.json', ['cuir' => $cuir]),
                    'resource' => $this->fhirService->prescriptionToMedicationRequest($prescription),
                ]
            ]
        ], 200, ['Content-Type' => 'application/fhir+json']);
    }

    /**
     * Get prescription metadata (conformance)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function conformance()
    {
        return response()->json([
            'resourceType' => 'CapabilityStatement',
            'status' => 'active',
            'date' => now()->toIso8601String(),
            'publisher' => 'GestorClinico',
            'description' => 'FHIR API para recetas digitales conforme a ReNaPDiS',
            'kind' => 'instance',
            'implementation' => [
                'description' => 'GestorClinico - Gestor de Recetas Digitales',
                'url' => config('app.url')
            ],
            'fhirVersion' => '4.0.0',
            'rest' => [
                [
                    'mode' => 'server',
                    'resource' => [
                        [
                            'type' => 'MedicationRequest',
                            'interaction' => [
                                ['code' => 'read'],
                                ['code' => 'search-type']
                            ]
                        ],
                        [
                            'type' => 'Patient',
                            'interaction' => [
                                ['code' => 'read']
                            ]
                        ],
                        [
                            'type' => 'Practitioner',
                            'interaction' => [
                                ['code' => 'read']
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }
