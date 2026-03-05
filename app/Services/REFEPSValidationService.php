<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para validar profesionales médicos con REFEPS
 * (Red Federal de Registros de Profesionales de la Salud)
 */
class REFEPSValidationService
{
  protected string $apiUrl;
  protected string $apiKey;
  protected int $cacheMinutes = 1440; // 24 horas

  public function __construct()
  {
    $this->apiUrl = config('services.refeps.url', 'https://sisa.msal.gov.ar/sisadoc/docs/refeps');
    $this->apiKey = config('services.refeps.api_key', '');
  }

  /**
   * Valida la matrícula de un profesional con REFEPS
   *
   * @param string $matricula
   * @param string $tipo 'nacional' o 'provincial'
   * @param string|null $provincia Código de provincia (para matrículas provinciales)
   * @return array|null Datos del profesional si es válido, null si no existe
   */
  public function validateMatricula(string $matricula, string $tipo = 'nacional', ?string $provincia = null): ?array
  {
    $cacheKey = "refeps_validation_{$tipo}_{$matricula}_{$provincia}";

    // Verificar cache primero
    if (Cache::has($cacheKey)) {
      return Cache::get($cacheKey);
    }

    try {
      // TODO: Implementar la llamada real a la API de REFEPS cuando esté disponible
      // Por ahora, simulamos la validación

      if (config('app.env') === 'production') {
        $response = $this->callREFEPSAPI($matricula, $tipo, $provincia);
      } else {
        // En desarrollo, simulamos la respuesta
        $response = $this->mockValidation($matricula, $tipo, $provincia);
      }

      // Guardar en cache
      if ($response) {
        Cache::put($cacheKey, $response, now()->addMinutes($this->cacheMinutes));
      }

      return $response;
    } catch (\Exception $e) {
      Log::error('Error validando matrícula con REFEPS', [
        'matricula' => $matricula,
        'tipo' => $tipo,
        'error' => $e->getMessage(),
      ]);

      return null;
    }
  }

  /**
   * Llama a la API real de REFEPS
   *
   * @param string $matricula
   * @param string $tipo
   * @param string|null $provincia
   * @return array|null
   */
  protected function callREFEPSAPI(string $matricula, string $tipo, ?string $provincia): ?array
  {
    $params = [
      'matricula' => $matricula,
      'tipo' => $tipo,
    ];

    if ($provincia) {
      $params['provincia'] = $provincia;
    }

    $response = Http::withHeaders([
      'Authorization' => "Bearer {$this->apiKey}",
      'Accept' => 'application/json',
    ])->get($this->apiUrl . '/consulta', $params);

    if ($response->successful() && $response->json('valido') === true) {
      return [
        'valido' => true,
        'matricula' => $matricula,
        'tipo' => $tipo,
        'nombre_completo' => $response->json('nombre_completo'),
        'especialidad' => $response->json('especialidad'),
        'provincia' => $response->json('provincia'),
        'estado' => $response->json('estado'),
        'fecha_validacion' => now(),
      ];
    }

    return null;
  }

  /**
   * Simulación de validación para desarrollo
   *
   * @param string $matricula
   * @param string $tipo
   * @param string|null $provincia
   * @return array|null
   */
  protected function mockValidation(string $matricula, string $tipo, ?string $provincia): ?array
  {
    // En desarrollo, aceptamos cualquier matrícula que coincida con un patrón básico
    if (preg_match('/^[A-Z0-9]{4,15}$/', strtoupper($matricula))) {
      return [
        'valido' => true,
        'matricula' => $matricula,
        'tipo' => $tipo,
        'nombre_completo' => 'Profesional de Prueba',
        'especialidad' => 'Medicina General',
        'provincia' => $provincia ?? 'Tucumán',
        'estado' => 'activo',
        'fecha_validacion' => now(),
        'modo' => 'desarrollo',
      ];
    }

    return null;
  }

  /**
   * Verifica si un profesional tiene habilitación vigente
   *
   * @param string $matricula
   * @param string $tipo
   * @param string|null $provincia
   * @return bool
   */
  public function isActive(string $matricula, string $tipo = 'nacional', ?string $provincia = null): bool
  {
    $validation = $this->validateMatricula($matricula, $tipo, $provincia);

    return $validation !== null &&
      isset($validation['estado']) &&
      $validation['estado'] === 'activo';
  }

  /**
   * Limpia el cache de validación para una matrícula específica
   *
   * @param string $matricula
   * @param string $tipo
   * @param string|null $provincia
   * @return void
   */
  public function clearCache(string $matricula, string $tipo = 'nacional', ?string $provincia = null): void
  {
    $cacheKey = "refeps_validation_{$tipo}_{$matricula}_{$provincia}";
    Cache::forget($cacheKey);
  }
}
