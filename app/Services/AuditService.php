<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Servicio para registrar auditoría de todas las acciones en el sistema
 * Cumple con requisitos de trazabilidad de ReNaPDiS y Ley de Protección de Datos
 */
class AuditService
{
  /**
   * Registra una acción en el audit log
   *
   * @param string $action Acción realizada (created, updated, deleted, viewed, etc)
   * @param string $model Nombre del modelo (ej: 'Prescription', 'User')
   * @param int $modelId ID del modelo
   * @param string|null $description Descripción de la acción
   * @param array|null $oldValues Datos anteriores (para actualizaciones)
   * @param array|null $newValues Datos nuevos (para actualizaciones)
   * @param string|null $cuir CUIR si es relevante a ReNaPDiS
   * @return AuditLog
   */
  public function log(
    string $action,
    string $model,
    int $modelId,
    ?string $description = null,
    ?array $oldValues = null,
    ?array $newValues = null,
    ?string $cuir = null
  ): AuditLog {
    $user = Auth::user();

    $auditLog = AuditLog::create([
      'user_id' => $user?->id,
      'user_name' => $user?->name ?? 'Sistema',
      'user_role' => $user?->role ?? 'sistema',
      'model' => $model,
      'model_id' => $modelId,
      'action' => $action,
      'description' => $description,
      'old_values' => $oldValues ? $this->sanitizeData($oldValues) : null,
      'new_values' => $newValues ? $this->sanitizeData($newValues) : null,
      'ip_address' => Request::ip() ?? '127.0.0.1',
      'user_agent' => Request::userAgent() ?? 'unknown',
      'method' => Request::method(),
      'url' => Request::fullUrl(),
      'cuir' => $cuir,
      'renapdis_relevant' => $cuir !== null,
    ]);

    return $auditLog;
  }

  /**
   * Registra una creación de recurso
   *
   * @param string $model
   * @param int $modelId
   * @param array $data
   * @param string|null $cuir
   * @return AuditLog
   */
  public function logCreate(string $model, int $modelId, array $data, ?string $cuir = null): AuditLog
  {
    return $this->log(
      'created',
      $model,
      $modelId,
      "Se creó nuevo registro de {$model}",
      null,
      $this->sanitizeData($data),
      $cuir
    );
  }

  /**
   * Registra una actualización de recurso
   *
   * @param string $model
   * @param int $modelId
   * @param array $oldData
   * @param array $newData
   * @param string|null $cuir
   * @return AuditLog
   */
  public function logUpdate(
    string $model,
    int $modelId,
    array $oldData,
    array $newData,
    ?string $cuir = null
  ): AuditLog {
    $changes = $this->getChanges($oldData, $newData);

    return $this->log(
      'updated',
      $model,
      $modelId,
      "Se actualizó registro de {$model}. Campos cambiados: " . implode(', ', array_keys($changes)),
      $this->sanitizeData($oldData),
      $this->sanitizeData($newData),
      $cuir
    );
  }

  /**
   * Registra una eliminación (soft delete)
   *
   * @param string $model
   * @param int $modelId
   * @param array $data
   * @param string|null $cuir
   * @return AuditLog
   */
  public function logDelete(string $model, int $modelId, array $data, ?string $cuir = null): AuditLog
  {
    return $this->log(
      'deleted',
      $model,
      $modelId,
      "Se eliminó registro de {$model}",
      $this->sanitizeData($data),
      null,
      $cuir
    );
  }

  /**
   * Registra una visualización de recurso
   *
   * @param string $model
   * @param int $modelId
   * @param string|null $cuir
   * @return AuditLog
   */
  public function logView(string $model, int $modelId, ?string $cuir = null): AuditLog
  {
    return $this->log(
      'viewed',
      $model,
      $modelId,
      "{$model}#{$modelId} fue consultado",
      null,
      null,
      $cuir
    );
  }

  /**
   * Registra una descarga de recurso
   *
   * @param string $model
   * @param int $modelId
   * @param string $fileType
   * @param string|null $cuir
   * @return AuditLog
   */
  public function logDownload(string $model, int $modelId, string $fileType = 'pdf', ?string $cuir = null): AuditLog
  {
    return $this->log(
      'downloaded',
      $model,
      $modelId,
      "{$model}#{$modelId} fue descargado en formato {$fileType}",
      null,
      null,
      $cuir
    );
  }

  /**
   * Registra firma electrónica
   *
   * @param string $model
   * @param int $modelId
   * @param string|null $cuir
   * @return AuditLog
   */
  public function logSigned(string $model, int $modelId, ?string $cuir = null): AuditLog
  {
    return $this->log(
      'signed',
      $model,
      $modelId,
      "{$model}#{$modelId} fue firmado electrónicamente",
      null,
      null,
      $cuir
    );
  }

  /**
   * Registra verificación de CUIR
   *
   * @param string $cuir
   * @param bool $valid
   * @return AuditLog
   */
  public function logVerified(string $cuir, bool $valid = true): AuditLog
  {
    return $this->log(
      'verified',
      'Prescription',
      0,
      "Receta con CUIR {$cuir} fue " . ($valid ? 'verificada correctamente' : 'verificación falló'),
      null,
      null,
      $cuir
    );
  }

  /**
   * Registra dispensación de receta
   *
   * @param int $prescriptionId
   * @param string $cuir
   * @param string $farmacia
   * @return AuditLog
   */
  public function logDispensed(int $prescriptionId, string $cuir, string $farmacia): AuditLog
  {
    return $this->log(
      'dispensed',
      'Prescription',
      $prescriptionId,
      "Receta dispensada en farmacia: {$farmacia}",
      null,
      ['farmacia' => $farmacia],
      $cuir
    );
  }

  /**
   * Registra anulación de receta
   *
   * @param int $prescriptionId
   * @param string $cuir
   * @param string $motivo
   * @return AuditLog
   */
  public function logAnnulled(int $prescriptionId, string $cuir, string $motivo): AuditLog
  {
    return $this->log(
      'annulled',
      'Prescription',
      $prescriptionId,
      "Receta anulada. Motivo: {$motivo}",
      null,
      ['motivo' => $motivo],
      $cuir
    );
  }

  /**
   * Registra exportación/envío de datos
   *
   * @param string $model
   * @param int $modelId
   * @param string $format
   * @param string|null $cuir
   * @return AuditLog
   */
  public function logExported(string $model, int $modelId, string $format = 'hl7-fhir', ?string $cuir = null): AuditLog
  {
    return $this->log(
      'exported',
      $model,
      $modelId,
      "{$model}#{$modelId} fue exportado en formato {$format}",
      null,
      null,
      $cuir
    );
  }

  /**
   * Obtiene el historial de auditoría para una receta
   *
   * @param int $prescriptionId
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function getPrescriptionAuditTrail(int $prescriptionId)
  {
    return AuditLog::forModel('Prescription', $prescriptionId)
      ->orderByDesc('created_at')
      ->get();
  }

  /**
   * Obtiene el historial de auditoría por CUIR
   *
   * @param string $cuir
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function getCUIRAuditTrail(string $cuir)
  {
    return AuditLog::byCUIR($cuir)
      ->orderByDesc('created_at')
      ->get();
  }

  /**
   * Limpia datos sensibles antes de guardar en auditoría
   *
   * @param array $data
   * @return array
   */
  protected function sanitizeData(array $data): array
  {
    $sensitiveFields = ['password', 'token', 'secret', 'api_key', 'firma_electronica_hash'];

    foreach ($sensitiveFields as $field) {
      if (isset($data[$field])) {
        $data[$field] = '[REDACTED]';
      }
    }

    return $data;
  }

  /**
   * Obtiene las diferencias entre dos arrays
   *
   * @param array $oldData
   * @param array $newData
   * @return array
   */
  protected function getChanges(array $oldData, array $newData): array
  {
    $changes = [];

    foreach ($newData as $key => $value) {
      $oldValue = $oldData[$key] ?? null;

      if ($oldValue !== $value) {
        $changes[$key] = [
          'old' => $oldValue,
          'new' => $value,
        ];
      }
    }

    return $changes;
  }
}
