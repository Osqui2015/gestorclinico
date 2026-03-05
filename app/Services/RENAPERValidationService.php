<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para validar identidad de pacientes con RENAPER
 * (Registro Nacional de las Personas)
 */
class RENAPERValidationService
{
  protected string $apiUrl;
  protected string $apiKey;
  protected int $cacheMinutes = 10080; // 7 días

  public function __construct()
  {
    $this->apiUrl = config('services.renaper.url', 'https://api.renaper.gob.ar');
    $this->apiKey = config('services.renaper.api_key', '');
  }

  /**
   * Valida la identidad de un paciente con RENAPER por DNI y CUIL
   *
   * @param string $dni
   * @param string $cuil
   * @return array|null Datos del paciente si es válido, null si no existe
   */
  public function validateIdentity(string $dni, string $cuil): ?array
  {
    $cacheKey = "renaper_validation_{$dni}_{$cuil}";

    // Verificar cache primero
    if (Cache::has($cacheKey)) {
      return Cache::get($cacheKey);
    }

    try {
      if (config('app.env') === 'production') {
        $response = $this->callRENAPERAPI($dni, $cuil);
      } else {
        // En desarrollo, simulamos la respuesta
        $response = $this->mockValidation($dni, $cuil);
      }

      // Guardar en cache
      if ($response) {
        Cache::put($cacheKey, $response, now()->addMinutes($this->cacheMinutes));
      }

      return $response;
    } catch (\Exception $e) {
      Log::error('Error validando identidad con RENAPER', [
        'dni' => $dni,
        'cuil' => $cuil,
        'error' => $e->getMessage(),
      ]);

      return null;
    }
  }

  /**
   * Llama a la API real de RENAPER
   *
   * @param string $dni
   * @param string $cuil
   * @return array|null
   */
  protected function callRENAPERAPI(string $dni, string $cuil): ?array
  {
    $response = Http::withHeaders([
      'Authorization' => "Bearer {$this->apiKey}",
      'Accept' => 'application/json',
    ])->post($this->apiUrl . '/v1/personas/renaper/consulta-simple', [
      'numero' => $dni,
      'cuil' => $cuil,
    ]);

    if ($response->successful() && $response->json('status') === 'ok') {
      $data = $response->json('data');

      return [
        'valido' => true,
        'dni' => $dni,
        'cuil' => $cuil,
        'nombre_completo' => $data['nombre'] . ' ' . $data['apellido'],
        'nombres' => $data['nombre'] ?? '',
        'apellidos' => $data['apellido'] ?? '',
        'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
        'sexo' => $data['sexo'] ?? null,
        'fecha_validacion' => now(),
      ];
    }

    return null;
  }

  /**
   * Simulación de validación para desarrollo
   *
   * @param string $dni
   * @param string $cuil
   * @return array|null
   */
  protected function mockValidation(string $dni, string $cuil): ?array
  {
    // Validación básica de formato
    if (!$this->validateDNIFormat($dni) || !$this->validateCUILFormat($cuil)) {
      return null;
    }

    // Validar que el DNI esté en el CUIL
    if (!str_contains($cuil, $dni)) {
      return null;
    }

    return [
      'valido' => true,
      'dni' => $dni,
      'cuil' => $cuil,
      'nombre_completo' => 'Paciente de Prueba',
      'nombres' => 'Juan',
      'apellidos' => 'Pérez',
      'fecha_nacimiento' => '1980-01-01',
      'sexo' => $this->getSexoFromCUIL($cuil),
      'fecha_validacion' => now(),
      'modo' => 'desarrollo',
    ];
  }

  /**
   * Valida el formato de un DNI argentino
   *
   * @param string $dni
   * @return bool
   */
  public function validateDNIFormat(string $dni): bool
  {
    // DNI argentino: entre 7 y 8 dígitos
    return preg_match('/^\d{7,8}$/', $dni) === 1;
  }

  /**
   * Valida el formato de un CUIL argentino
   *
   * @param string $cuil
   * @return bool
   */
  public function validateCUILFormat(string $cuil): bool
  {
    // CUIL argentino: 11 dígitos (XX-XXXXXXXX-X)
    $cuil = str_replace('-', '', $cuil);

    if (strlen($cuil) !== 11 || !ctype_digit($cuil)) {
      return false;
    }

    // Validar dígito verificador
    return $this->validateCUILCheckDigit($cuil);
  }

  /**
   * Valida el dígito verificador del CUIL
   *
   * @param string $cuil
   * @return bool
   */
  protected function validateCUILCheckDigit(string $cuil): bool
  {
    $multiplicadores = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
    $suma = 0;

    for ($i = 0; $i < 10; $i++) {
      $suma += (int)$cuil[$i] * $multiplicadores[$i];
    }

    $resto = $suma % 11;
    $digitoVerificador = 11 - $resto;

    if ($digitoVerificador === 11) {
      $digitoVerificador = 0;
    } elseif ($digitoVerificador === 10) {
      $digitoVerificador = 9;
    }

    return (int)$cuil[10] === $digitoVerificador;
  }

  /**
   * Obtiene el sexo a partir del CUIL
   *
   * @param string $cuil
   * @return string 'M' o 'F'
   */
  protected function getSexoFromCUIL(string $cuil): string
  {
    $prefijo = substr($cuil, 0, 2);

    // 20: Masculino
    // 27: Masculino (extranjero)
    // 23: Masculino (nuevos)
    // 24: Masculino (nuevos)
    if (in_array($prefijo, ['20', '27', '23', '24'])) {
      return 'M';
    }

    // 27: Femenino
    // 23: Femenino (extranjero)
    // 25: Femenino (nuevos)
    // 26: Femenino (nuevos)
    return 'F';
  }

  /**
   * Genera un CUIL a partir de un DNI y sexo (útil para testing)
   *
   * @param string $dni
   * @param string $sexo 'M' o 'F'
   * @return string
   */
  public function generateCUIL(string $dni, string $sexo): string
  {
    $prefijo = $sexo === 'M' ? '20' : '27';
    $base = $prefijo . $dni;

    $multiplicadores = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
    $suma = 0;

    for ($i = 0; $i < 10; $i++) {
      $suma += (int)$base[$i] * $multiplicadores[$i];
    }

    $resto = $suma % 11;
    $digitoVerificador = 11 - $resto;

    if ($digitoVerificador === 11) {
      $digitoVerificador = 0;
    } elseif ($digitoVerificador === 10) {
      $prefijo = $sexo === 'M' ? '23' : '23';
      return $this->generateCUIL($dni, $sexo);
    }

    return $base . $digitoVerificador;
  }

  /**
   * Limpia el cache de validación
   *
   * @param string $dni
   * @param string $cuil
   * @return void
   */
  public function clearCache(string $dni, string $cuil): void
  {
    $cacheKey = "renaper_validation_{$dni}_{$cuil}";
    Cache::forget($cacheKey);
  }
}
