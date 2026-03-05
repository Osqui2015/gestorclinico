<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FHIRController;
use App\Http\Controllers\ObrasSocialesController;

Route::get('/user', function (Request $request) {
  return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Appointments API Routes
|--------------------------------------------------------------------------
|
| Rutas para gestionar citas médicas
|
*/

Route::prefix('appointments')->group(function () {
  // Obtener slots disponibles de un doctor en una fecha
  Route::get('available-slots', [App\Http\Controllers\AppointmentController::class, 'getAvailableSlots'])
    ->name('api.appointments.available-slots');

  // Obtener información del seguro primario del paciente (para citas)
  Route::get('patient-insurance', [App\Http\Controllers\AppointmentController::class, 'getPatientInsurance'])
    ->name('api.appointments.patient-insurance');
});

/*
|--------------------------------------------------------------------------
| Obras Sociales API Routes
|--------------------------------------------------------------------------
|
| Rutas para consultar y buscar obras sociales argentinas
|
*/

Route::prefix('obras-sociales')->group(function () {
  // Buscar obras sociales
  Route::get('search', [ObrasSocialesController::class, 'search'])
    ->name('api.obras-sociales.search');

  // Obtener lista de provincias
  Route::get('provincias', [ObrasSocialesController::class, 'provincias'])
    ->name('api.obras-sociales.provincias');

  // Obtener obras sociales por provincia
  Route::get('provincia/{provincia}', [ObrasSocialesController::class, 'byProvincia'])
    ->name('api.obras-sociales.by-provincia');

  // Obtener obra social específica por RNOS
  Route::get('{rnos}', [ObrasSocialesController::class, 'show'])
    ->name('api.obras-sociales.show');
});

/*
|--------------------------------------------------------------------------
| FHIR API Routes for ReNaPDiS Interoperability
|--------------------------------------------------------------------------
|
| Estas rutas permiten a farmacias y otros sistemas de salud obtener
| recetas en formato HL7 FHIR compatible.
|
*/

Route::prefix('fhir')->group(function () {
  // Conformance/Capability Statement
  Route::get('conformance', [FHIRController::class, 'conformance'])
    ->name('api.fhir.conformance');

  // Buscar recetas por CUIR
  Route::get('MedicationRequest', [FHIRController::class, 'getMedicationRequest'])
    ->name('api.fhir.search');

  // Obtener Receta como MedicationRequest (JSON)
  Route::get('prescriptions/{cuir}/medication-request.json', [FHIRController::class, 'getPrescriptionAsJSON'])
    ->name('api.fhir.medication-request.json');

  // Obtener Receta como MedicationRequest (XML)
  Route::get('prescriptions/{cuir}/medication-request.xml', [FHIRController::class, 'getPrescriptionAsXML'])
    ->name('api.fhir.medication-request.xml');

  // Obtener Receta como Bundle
  Route::get('prescriptions/{cuir}/bundle', [FHIRController::class, 'getPrescriptionBundle'])
    ->name('api.fhir.bundle');

  // Validar prescripción
  Route::post('prescriptions/validate', [FHIRController::class, 'validatePrescription'])
    ->name('api.fhir.validate');

  // Buscar prescripciones
  Route::get('prescriptions', [FHIRController::class, 'searchPrescriptions'])
    ->name('api.fhir.search-prescriptions');
});
