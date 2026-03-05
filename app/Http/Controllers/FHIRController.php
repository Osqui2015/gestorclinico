<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Services\HL7FHIRService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FHIRController extends Controller
{
  /**
   * Endpoint conformance de FHIR
   * GET /api/fhir/conformance
   */
  public function conformance()
  {
    return response()->json([
      'resourceType' => 'CapabilityStatement',
      'status' => 'active',
      'date' => now()->toIso8601String(),
      'publisher' => 'Gestor Clínico - ReNaPDiS Compliant',
      'kind' => 'instance',
      'software' => [
        'name' => 'Gestor Clínico',
        'version' => '1.0.0'
      ],
      'implementation' => [
        'description' => 'Digital Prescription System compliant with ReNaPDiS (Ley 27.553)',
        'url' => config('app.url')
      ],
      'fhirVersion' => '4.0.1',
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

  /**
   * Obtener MedicationRequest por CUIR
   * GET /api/fhir/MedicationRequest?cuir={cuir}
   */
  public function getMedicationRequest(Request $request)
  {
    $cuir = $request->query('cuir');

    if (!$cuir) {
      return response()->json(['error' => 'CUIR parameter required'], 400);
    }

    $prescription = Prescription::where('cuir', $cuir)->first();

    if (!$prescription) {
      return response()->json(['error' => 'Prescription not found'], 404);
    }

    $fhirService = app(HL7FHIRService::class);
    $medicationRequest = $fhirService->toMedicationRequest($prescription);

    return response()->json($medicationRequest);
  }

  /**
   * Obtener prescripción en formato FHIR JSON
   * GET /api/fhir/prescriptions/{cuir}/medication-request.json
   */
  public function getPrescriptionAsJSON($cuir)
  {
    $prescription = Prescription::where('cuir', $cuir)->first();

    if (!$prescription) {
      return response()->json(['error' => 'Prescription not found'], 404);
    }

    $fhirService = app(HL7FHIRService::class);
    $medicationRequest = $fhirService->toMedicationRequest($prescription);

    return response()->json($medicationRequest);
  }

  /**
   * Obtener prescripción en formato FHIR XML
   * GET /api/fhir/prescriptions/{cuir}/medication-request.xml
   */
  public function getPrescriptionAsXML($cuir)
  {
    $prescription = Prescription::where('cuir', $cuir)->first();

    if (!$prescription) {
      return response()->json(['error' => 'Prescription not found'], 404);
    }

    $fhirService = app(HL7FHIRService::class);
    $xml = $fhirService->toXML($prescription);

    return response($xml, 200)
      ->header('Content-Type', 'application/fhir+xml');
  }

  /**
   * Obtener Bundle FHIR con toda la prescripción
   * GET /api/fhir/prescriptions/{cuir}/bundle
   */
  public function getPrescriptionBundle($cuir)
  {
    $prescription = Prescription::where('cuir', $cuir)->first();

    if (!$prescription) {
      return response()->json(['error' => 'Prescription not found'], 404);
    }

    $fhirService = app(HL7FHIRService::class);
    $bundle = $fhirService->toBundle($prescription);

    return response()->json($bundle);
  }

  /**
   * Validar conformidad FHIR de una prescripción
   * POST /api/fhir/prescriptions/validate
   */
  public function validatePrescription(Request $request)
  {
    $data = $request->validate([
      'cuir' => 'required|string|exists:prescriptions,cuir'
    ]);

    $prescription = Prescription::where('cuir', $data['cuir'])->first();

    if (!$prescription->isReNaPDiSCompliant()) {
      return response()->json([
        'valid' => false,
        'errors' => [
          'prescription' => 'Prescription does not meet ReNaPDiS compliance requirements'
        ]
      ], 422);
    }

    $fhirService = app(HL7FHIRService::class);
    $medicationRequest = $fhirService->toMedicationRequest($prescription);

    return response()->json([
      'valid' => true,
      'message' => 'Prescription is valid and FHIR compliant',
      'medicationRequest' => $medicationRequest
    ]);
  }

  /**
   * Buscar prescripciones disponibles para una farmacia
   * GET /api/fhir/prescriptions?patient={patient_id}&status={status}&date_from={date}&date_to={date}
   */
  public function searchPrescriptions(Request $request)
  {
    $query = Prescription::query();

    if ($request->has('patient')) {
      $query->where('patient_id', $request->query('patient'));
    }

    if ($request->has('status')) {
      $query->where('estado_dispensacion', $request->query('status'));
    }

    if ($request->has('date_from')) {
      $query->whereDate('created_at', '>=', $request->query('date_from'));
    }

    if ($request->has('date_to')) {
      $query->whereDate('created_at', '<=', $request->query('date_to'));
    }

    $prescriptions = $query->get();

    $fhirService = app(HL7FHIRService::class);
    $bundle = $fhirService->toBundleCollection($prescriptions);

    return response()->json($bundle);
  }
}
