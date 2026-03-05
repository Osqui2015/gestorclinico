<?php

namespace App\Services;

use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Servicio para generar CUIR (Clave Única de Identificación de Receta)
 * según los estándares del ReNaPDiS
 */
class CUIRGeneratorService
{
  /**
   * Genera un CUIR único para una receta digital
   *
   * Formato: YYYY-MM-DD-PROVINCIA-MATRICULA-TIMESTAMP-RANDOM
   * Ejemplo: 2026-02-28-TUC-12345-1709078400-A1B2C3
   *
   * @param string $matriculaProfesional
   * @param string $provincia (código de 3 letras)
   * @return string
   */
  public function generate(string $matriculaProfesional, string $provincia = 'TUC'): string
  {
    $fecha = Carbon::now()->format('Y-m-d');
    $timestamp = Carbon::now()->timestamp;
    $random = strtoupper(Str::random(6));

    // Sanitizar matrícula para usar solo alfanuméricos
    $matriculaSanitized = preg_replace('/[^A-Za-z0-9]/', '', $matriculaProfesional);
    $matriculaSanitized = substr($matriculaSanitized, 0, 10); // Máximo 10 caracteres

    $cuir = sprintf(
      '%s-%s-%s-%s-%s',
      $fecha,
      strtoupper($provincia),
      $matriculaSanitized,
      $timestamp,
      $random
    );

    return $cuir;
  }

  /**
   * Valida el formato de un CUIR
   *
   * @param string $cuir
   * @return bool
   */
  public function validate(string $cuir): bool
  {
    // Patrón: YYYY-MM-DD-XXX-XXXXXXXXXX-XXXXXXXXXX-XXXXXX
    $pattern = '/^\d{4}-\d{2}-\d{2}-[A-Z]{3}-[A-Za-z0-9]{1,10}-\d{10}-[A-Z0-9]{6}$/';

    return preg_match($pattern, $cuir) === 1;
  }

  /**
   * Extrae información del CUIR
   *
   * @param string $cuir
   * @return array|null
   */
  public function parse(string $cuir): ?array
  {
    if (!$this->validate($cuir)) {
      return null;
    }

    $parts = explode('-', $cuir);

    return [
      'fecha' => $parts[0] . '-' . $parts[1] . '-' . $parts[2],
      'provincia' => $parts[3],
      'matricula' => $parts[4],
      'timestamp' => $parts[5],
      'random' => $parts[6],
    ];
  }

  /**
   * Genera un CUIR completo con verificación de unicidad
   *
   * @param string $matriculaProfesional
   * @param string $provincia
   * @return string
   */
  public function generateUnique(string $matriculaProfesional, string $provincia = 'TUC'): string
  {
    do {
      $cuir = $this->generate($matriculaProfesional, $provincia);
      // Verificar unicidad en la base de datos
      $exists = \App\Models\Prescription::where('cuir', $cuir)->exists();
    } while ($exists);

    return $cuir;
  }
}
