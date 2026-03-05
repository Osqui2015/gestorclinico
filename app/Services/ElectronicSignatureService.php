<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Prescription;

/**
 * Servicio para manejo de firma electrónica en recetas digitales
 * Cumple con los requisitos del ReNaPDiS
 */
class ElectronicSignatureService
{
  /**
   * Firma una receta digitalmente
   *
   * @param Prescription $prescription
   * @param User $doctor
   * @param string $password Contraseña del médico para validar la firma
   * @param string|null $otp Código OTP para 2FA
   * @return bool
   */
  public function signPrescription(Prescription $prescription, User $doctor, string $password, ?string $otp = null): bool
  {
    try {
      // Validar que el doctor es quien dice ser
      if (!Hash::check($password, $doctor->password)) {
        Log::warning('Intento de firma con contraseña incorrecta', [
          'doctor_id' => $doctor->id,
          'prescription_id' => $prescription->id,
        ]);
        return false;
      }

      // Si el doctor tiene 2FA habilitado, validar OTP
      if ($doctor->firma_electronica_metodo === '2fa' && $doctor->firma_electronica_habilitada) {
        if (!$this->validate2FA($doctor, $otp)) {
          Log::warning('Intento de firma con OTP incorrecto', [
            'doctor_id' => $doctor->id,
            'prescription_id' => $prescription->id,
          ]);
          return false;
        }
      }

      // Generar hash de firma
      $signatureData = $this->generateSignatureData($prescription, $doctor);
      $signatureHash = $this->createSignatureHash($signatureData);

      // Actualizar la receta con la firma
      $prescription->update([
        'firma_electronica_hash' => $signatureHash,
        'firma_metodo' => $doctor->firma_electronica_metodo ?? '2fa',
        'firma_timestamp' => now(),
        'firma_ip_address' => request()->ip(),
      ]);

      Log::info('Receta firmada electrónicamente', [
        'doctor_id' => $doctor->id,
        'prescription_id' => $prescription->id,
        'cuir' => $prescription->cuir,
      ]);

      return true;
    } catch (\Exception $e) {
      Log::error('Error al firmar receta', [
        'doctor_id' => $doctor->id,
        'prescription_id' => $prescription->id,
        'error' => $e->getMessage(),
      ]);

      return false;
    }
  }

  /**
   * Genera los datos que se incluirán en la firma
   *
   * @param Prescription $prescription
   * @param User $doctor
   * @return array
   */
  protected function generateSignatureData(Prescription $prescription, User $doctor): array
  {
    return [
      'cuir' => $prescription->cuir,
      'prescription_id' => $prescription->id,
      'doctor_id' => $doctor->id,
      'doctor_cuil' => $doctor->cuil,
      'matricula' => $prescription->matricula_profesional,
      'patient_id' => $prescription->patient_id,
      'paciente_cuil' => $prescription->paciente_cuil,
      'medications' => $prescription->medicamentos_genericos,
      'diagnosis' => $prescription->cie10_codigo,
      'fecha_emision' => $prescription->fecha_emision->toDateTimeString(),
      'timestamp' => now()->toDateTimeString(),
    ];
  }

  /**
   * Crea el hash de firma electrónica
   *
   * @param array $data
   * @return string
   */
  protected function createSignatureHash(array $data): string
  {
    $dataString = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // Usar SHA-256 para el hash
    return hash_hmac('sha256', $dataString, config('app.key'));
  }

  /**
   * Valida una firma electrónica
   *
   * @param Prescription $prescription
   * @return bool
   */
  public function verifySignature(Prescription $prescription): bool
  {
    if (!$prescription->firma_electronica_hash) {
      return false;
    }

    // Reconstruir los datos de la firma
    $signatureData = [
      'cuir' => $prescription->cuir,
      'prescription_id' => $prescription->id,
      'doctor_id' => $prescription->doctor_id,
      'doctor_cuil' => $prescription->doctor->cuil ?? null,
      'matricula' => $prescription->matricula_profesional,
      'patient_id' => $prescription->patient_id,
      'paciente_cuil' => $prescription->paciente_cuil,
      'medications' => $prescription->medicamentos_genericos,
      'diagnosis' => $prescription->cie10_codigo,
      'fecha_emision' => $prescription->fecha_emision?->toDateTimeString(),
      'timestamp' => $prescription->firma_timestamp?->toDateTimeString(),
    ];

    $expectedHash = $this->createSignatureHash($signatureData);

    return hash_equals($expectedHash, $prescription->firma_electronica_hash);
  }

  /**
   * Valida el código 2FA/OTP
   *
   * @param User $doctor
   * @param string|null $otp
   * @return bool
   */
  protected function validate2FA(User $doctor, ?string $otp): bool
  {
    if (!$otp) {
      return false;
    }

    // TODO: Implementar validación real de OTP
    // Por ahora, aceptamos cualquier código de 6 dígitos en desarrollo
    if (config('app.env') !== 'production') {
      return preg_match('/^\d{6}$/', $otp) === 1;
    }

    // En producción, aquí se validaría con Google Authenticator, Authy, etc.
    // usando una librería como pragmarx/google2fa

    return false;
  }

  /**
   * Verifica si un médico puede firmar recetas
   *
   * @param User $doctor
   * @return bool
   */
  public function canSign(User $doctor): bool
  {
    return $doctor->role === 'doctor' &&
      $doctor->firma_electronica_habilitada &&
      $doctor->validado_refeps &&
      ($doctor->matricula_nacional || $doctor->matricula_provincial);
  }

  /**
   * Genera un código de verificación de firma para mostrar al paciente
   *
   * @param Prescription $prescription
   * @return string
   */
  public function generateVerificationCode(Prescription $prescription): string
  {
    if (!$prescription->firma_electronica_hash) {
      return '';
    }

    // Primeros 8 caracteres del hash en mayúsculas
    return strtoupper(substr($prescription->firma_electronica_hash, 0, 8));
  }
}
