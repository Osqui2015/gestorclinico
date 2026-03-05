<?php

namespace App\Services;

use App\Models\HealthInsurance;

class ObrasSocialesService
{
  /**
   * Obtener todas las obras sociales desde base de datos
   */
  public function all(): array
  {
    return HealthInsurance::query()
      ->orderBy('name')
      ->get()
      ->map(fn($insurance) => $this->mapInsurance($insurance))
      ->toArray();
  }

  /**
   * Buscar obras sociales por término
   */
  public function search(string $query, int $limit = 20): array
  {
    if (empty($query)) {
      return [];
    }

    return HealthInsurance::query()
      ->where(function ($q) use ($query) {
        $q->where('name', 'like', "%{$query}%")
          ->orWhere('code', 'like', "%{$query}%")
          ->orWhere('notes', 'like', "%{$query}%");
      })
      ->orderBy('name')
      ->limit($limit)
      ->get()
      ->map(fn($insurance) => $this->mapInsurance($insurance))
      ->toArray();
  }

  /**
   * Obtener obra social por RNOS (código)
   */
  public function getByRnos(string $rnos): ?array
  {
    $insurance = HealthInsurance::query()
      ->where('code', $rnos)
      ->first();

    return $insurance ? $this->mapInsurance($insurance) : null;
  }

  /**
   * Obtener obras sociales por provincia (buscando en notes)
   */
  public function getByProvincia(string $provincia): array
  {
    return HealthInsurance::query()
      ->where('notes', 'like', "%Provincia: {$provincia}%")
      ->orderBy('name')
      ->get()
      ->map(fn($insurance) => $this->mapInsurance($insurance))
      ->toArray();
  }

  /**
   * Obtener lista de provincias (extraídas de notes)
   */
  public function getProvincias(): array
  {
    $provincias = [];

    foreach (HealthInsurance::query()->whereNotNull('notes')->pluck('notes') as $notes) {
      if (preg_match('/Provincia:\s*([^|\n]+)/i', $notes, $matches)) {
        $provincia = trim($matches[1]);
        if ($provincia !== '' && !in_array($provincia, $provincias, true)) {
          $provincias[] = $provincia;
        }
      }
    }

    sort($provincias);

    return $provincias;
  }

  /**
   * Mantener compatibilidad con interfaz anterior
   */
  public function dataExists(): bool
  {
    return HealthInsurance::query()->exists();
  }

  /**
   * Mantener compatibilidad con interfaz anterior
   */
  public function getDataFilePath(): string
  {
    return 'database:health_insurances';
  }

  protected function mapInsurance(HealthInsurance $insurance): array
  {
    return [
      'id' => $insurance->id,
      'rnos' => $insurance->code,
      'nombre' => $insurance->name,
      'name' => $insurance->name,
      'code' => $insurance->code,
      'sigla' => null,
      'provincia' => $this->extractFromNotes($insurance->notes, 'Provincia'),
      'localidad' => $this->extractFromNotes($insurance->notes, 'Localidad'),
      'domicilio' => $this->extractFromNotes($insurance->notes, 'Domicilio'),
      'telefonos' => $insurance->phone ? [$insurance->phone] : [],
      'emails' => $insurance->email ? [$insurance->email] : [],
      'is_active' => (bool) $insurance->is_active,
    ];
  }

  protected function extractFromNotes(?string $notes, string $label): ?string
  {
    if (!$notes) {
      return null;
    }

    $pattern = '/' . preg_quote($label, '/') . ':\s*([^|\n]+)/i';
    if (preg_match($pattern, $notes, $matches)) {
      return trim($matches[1]);
    }

    return null;
  }
}
