<?php

namespace App\Services;

use App\Models\Prescription;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Servicio principal para crear recetas digitales conformes con ReNaPDiS
 * Orquesta todos los servicios necesarios para cumplir con la Ley 27.553
 */
class DigitalPrescriptionService
{
  protected CUIRGeneratorService $cuirGenerator;
  protected QRCodeGeneratorService $qrGenerator;
  protected REFEPSValidationService $refepsValidator;
  protected RENAPERValidationService $renaperValidator;
  protected ElectronicSignatureService $signatureService;

  public function __construct(
    CUIRGeneratorService $cuirGenerator,
    QRCodeGeneratorService $qrGenerator,
    REFEPSValidationService $refepsValidator,
    RENAPERValidationService $renaperValidator,
    ElectronicSignatureService $signatureService
  ) {
    $this->cuirGenerator = $cuirGenerator;
    $this->qrGenerator = $qrGenerator;
    $this->refepsValidator = $refepsValidator;
    $this->renaperValidator = $renaperValidator;
    $this->signatureService = $signatureService;
  }

  /**
   * Crea una receta digital completa conforme a ReNaPDiS
   *
   * @param array $data
   * @param User $doctor
   * @param Patient $patient
   * @return Prescription|null
   */
  public function createDigitalPrescription(array $data, User $doctor, Patient $patient): ?Prescription
  {
    DB::beginTransaction();

    try {
      // 1. Validar profesional con REFEPS
      $matricula = $doctor->matricula_nacional ?? $doctor->matricula_provincial;
      $tipoMatricula = $doctor->matricula_nacional ? 'nacional' : 'provincial';

      $refepsValidation = $this->refepsValidator->validateMatricula(
        $matricula,
        $tipoMatricula,
        $doctor->provincia_matricula
      );

      if (!$refepsValidation) {
        Log::error('Validación REFEPS fallida', ['doctor_id' => $doctor->id]);
        throw new \Exception('La matrícula profesional no pudo ser validada con REFEPS.');
      }

      // 2. Validar paciente con RENAPER
      $renaperValidation = null;
      if ($patient->dni && $patient->cuil) {
        $renaperValidation = $this->renaperValidator->validateIdentity(
          $patient->dni,
          $patient->cuil
        );

        if (!$renaperValidation) {
          Log::warning('Validación RENAPER fallida', ['patient_id' => $patient->id]);
        }
      }

      // 3. Generar CUIR único
      $cuir = $this->cuirGenerator->generateUnique(
        $matricula,
        $this->getProvinciaCode($doctor->provincia_matricula ?? 'Tucumán')
      );

      // 4. Preparar datos de medicamentos genéricos (Ley 25.649)
      $medicamentosGenericos = $this->prepareMedicamentosGenericos($data['medications'] ?? []);

      // 5. Calcular fechas de emisión y vencimiento (30 días)
      $fechaEmision = now();
      $fechaVencimiento = now()->addDays(30);

      // 6. Crear la receta
      $prescription = Prescription::create([
        // Datos básicos
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'appointment_id' => $data['appointment_id'] ?? null,
        'medical_record_id' => $data['medical_record_id'] ?? null,
        'diagnosis' => $data['diagnosis'] ?? '',
        'notes' => $data['notes'] ?? null,

        // CUIR y fechas
        'cuir' => $cuir,
        'fecha_emision' => $fechaEmision,
        'fecha_vencimiento' => $fechaVencimiento,

        // Datos del profesional
        'matricula_profesional' => $matricula,
        'matricula_tipo' => $tipoMatricula,
        'profesional_nombre_completo' => $doctor->name,
        'profesional_especialidad' => $doctor->specialty,
        'consultorio_direccion' => $doctor->consultorio_direccion ?? $doctor->address,

        // Datos del paciente
        'paciente_cuil' => $patient->cuil,
        'paciente_nombre_completo' => $patient->first_name . ' ' . $patient->last_name,
        'paciente_fecha_nacimiento' => $patient->birth_date,
        'obra_social' => $data['obra_social'] ?? null,
        'numero_afiliado' => $data['numero_afiliado'] ?? null,

        // Medicamentos
        'medications' => $data['medications'] ?? [],
        'medicamentos_genericos' => $medicamentosGenericos,
        'instructions' => $data['instructions'] ?? [],

        // Diagnóstico CIE-10
        'cie10_codigo' => $data['cie10_codigo'] ?? null,
        'cie10_descripcion' => $data['cie10_descripcion'] ?? null,

        // Estado
        'status' => 'generated',
        'estado_dispensacion' => 'pendiente',

        // Validaciones
        'validado_refeps' => true,
        'validado_renaper' => $renaperValidation !== null,
        'fecha_validacion_externa' => now(),
      ]);

      // 7. Generar código QR
      $qrData = $this->qrGenerator->generateForPrescription($cuir, [
        'DOCTOR' => $doctor->name,
        'PACIENTE' => $patient->first_name . ' ' . $patient->last_name,
        'MATRICULA' => $matricula,
      ]);

      $prescription->update([
        'qr_code_path' => $qrData['path'],
        'qr_code_data' => $qrData['data'],
      ]);

      // 8. Firmar electrónicamente (si se proporciona contraseña)
      if (isset($data['doctor_password'])) {
        $this->signatureService->signPrescription(
          $prescription,
          $doctor,
          $data['doctor_password'],
          $data['otp'] ?? null
        );
      }

      DB::commit();

      Log::info('Receta digital creada exitosamente', [
        'prescription_id' => $prescription->id,
        'cuir' => $cuir,
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
      ]);

      return $prescription->fresh();
    } catch (\Exception $e) {
      DB::rollBack();

      Log::error('Error creando receta digital', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return null;
    }
  }

  /**
   * Prepara medicamentos con nombre genérico según Ley 25.649
   *
   * @param array $medications
   * @return array
   */
  protected function prepareMedicamentosGenericos(array $medications): array
  {
    $genericos = [];

    foreach ($medications as $med) {
      $genericos[] = [
        'nombre_generico' => $med['nombre_generico'] ?? $med['name'],
        'nombre_comercial' => $med['nombre_comercial'] ?? null,
        'forma_farmaceutica' => $med['forma_farmaceutica'] ?? 'comprimidos',
        'presentacion' => $med['dosage'] ?? '',
        'cantidad' => $med['cantidad'] ?? 1,
        'cantidad_letras' => $this->convertNumberToWords($med['cantidad'] ?? 1),
        'codigo_troquel' => $med['codigo_troquel'] ?? null,
        'codigo_barra' => $med['codigo_barra'] ?? null,
        'posologia' => $med['frequency'] ?? '',
        'duracion' => $med['duration'] ?? '',
      ];
    }

    return $genericos;
  }

  /**
   * Convierte un número a palabras (español)
   *
   * @param int $number
   * @return string
   */
  protected function convertNumberToWords(int $number): string
  {
    $words = [
      1 => 'uno',
      2 => 'dos',
      3 => 'tres',
      4 => 'cuatro',
      5 => 'cinco',
      6 => 'seis',
      7 => 'siete',
      8 => 'ocho',
      9 => 'nueve',
      10 => 'diez',
      11 => 'once',
      12 => 'doce',
      13 => 'trece',
      14 => 'catorce',
      15 => 'quince',
      16 => 'dieciséis',
      17 => 'diecisiete',
      18 => 'dieciocho',
      19 => 'diecinueve',
      20 => 'veinte',
      30 => 'treinta',
      40 => 'cuarenta',
      50 => 'cincuenta',
      60 => 'sesenta',
      70 => 'setenta',
      80 => 'ochenta',
      90 => 'noventa',
      100 => 'cien'
    ];

    if ($number <= 20 || isset($words[$number])) {
      return $words[$number] ?? (string)$number;
    }

    if ($number < 100) {
      $tens = floor($number / 10) * 10;
      $units = $number % 10;
      return $words[$tens] . ' y ' . $words[$units];
    }

    return (string)$number;
  }

  /**
   * Obtiene el código de provincia de 3 letras
   *
   * @param string $provincia
   * @return string
   */
  protected function getProvinciaCode(string $provincia): string
  {
    $codes = [
      'Buenos Aires' => 'BUE',
      'Catamarca' => 'CAT',
      'Chaco' => 'CHA',
      'Chubut' => 'CHU',
      'Córdoba' => 'COR',
      'Corrientes' => 'CRR',
      'Entre Ríos' => 'ENR',
      'Formosa' => 'FOR',
      'Jujuy' => 'JUJ',
      'La Pampa' => 'LPA',
      'La Rioja' => 'LRI',
      'Mendoza' => 'MEN',
      'Misiones' => 'MIS',
      'Neuquén' => 'NEU',
      'Río Negro' => 'RNE',
      'Salta' => 'SAL',
      'San Juan' => 'SJU',
      'San Luis' => 'SLU',
      'Santa Cruz' => 'SCR',
      'Santa Fe' => 'SFE',
      'Santiago del Estero' => 'SDE',
      'Tierra del Fuego' => 'TDF',
      'Tucumán' => 'TUC',
    ];

    return $codes[$provincia] ?? 'TUC';
  }

  /**
   * Verifica el estado de una receta por CUIR
   *
   * @param string $cuir
   * @return array|null
   */
  public function verifyPrescription(string $cuir): ?array
  {
    $prescription = Prescription::where('cuir', $cuir)->first();

    if (!$prescription) {
      return null;
    }

    return [
      'cuir' => $prescription->cuir,
      'valida' => $prescription->isValid(),
      'estado' => $prescription->estado_dispensacion,
      'vencida' => $prescription->isExpired(),
      'fecha_emision' => $prescription->fecha_emision,
      'fecha_vencimiento' => $prescription->fecha_vencimiento,
      'doctor' => $prescription->profesional_nombre_completo,
      'paciente' => $prescription->paciente_nombre_completo,
      'conforme_renapdis' => $prescription->isReNaPDiSCompliant(),
    ];
  }
}
