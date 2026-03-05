<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Servicio para generar códigos QR para recetas digitales
 * según los estándares del ReNaPDiS
 */
class QRCodeGeneratorService
{
  /**
   * Genera un código QR para una receta con su CUIR
   *
   * @param string $cuir
   * @param array $additionalData Información adicional para incluir en el QR
   * @return array ['path' => string, 'data' => string]
   */
  public function generateForPrescription(string $cuir, array $additionalData = []): array
  {
    // Datos que se incluirán en el código QR
    $qrData = $this->buildQRData($cuir, $additionalData);

    // Generar el código QR como imagen
    $qrCode = QrCode::format('png')
      ->size(300)
      ->errorCorrection('H')
      ->generate($qrData);

    // Guardar la imagen en storage
    $filename = "qr_codes/prescription_{$cuir}.png";
    Storage::disk('public')->put($filename, $qrCode);

    return [
      'path' => $filename,
      'data' => $qrData,
      'full_url' => asset('storage/' . $filename),
    ];
  }

  /**
   * Construye los datos que irán dentro del código QR
   *
   * @param string $cuir
   * @param array $additionalData
   * @return string
   */
  protected function buildQRData(string $cuir, array $additionalData = []): string
  {
    $baseUrl = config('app.url');
    $verificationUrl = "{$baseUrl}/prescriptions/verify/{$cuir}";

    $data = [
      'CUIR' => $cuir,
      'TIPO' => 'RECETA_DIGITAL',
      'SISTEMA' => 'GestorClinico',
      'VERIFICACION' => $verificationUrl,
      'FECHA_GENERACION' => now()->format('Y-m-d H:i:s'),
    ];

    // Agregar datos adicionales
    if (!empty($additionalData)) {
      $data = array_merge($data, $additionalData);
    }

    // Convertir a formato JSON compacto
    return json_encode($data, JSON_UNESCAPED_SLASHES);
  }

  /**
   * Genera un código QR como Base64 (para incluir directamente en PDFs)
   *
   * @param string $cuir
   * @param array $additionalData
   * @return string Base64 encoded image
   */
  public function generateBase64(string $cuir, array $additionalData = []): string
  {
    $qrData = $this->buildQRData($cuir, $additionalData);

    $qrCode = QrCode::format('png')
      ->size(300)
      ->errorCorrection('H')
      ->generate($qrData);

    return base64_encode($qrCode);
  }

  /**
   * Genera un código QR en formato SVG (más escalable)
   *
   * @param string $cuir
   * @param array $additionalData
   * @return string SVG content
   */
  public function generateSVG(string $cuir, array $additionalData = []): string
  {
    $qrData = $this->buildQRData($cuir, $additionalData);

    return QrCode::format('svg')
      ->size(300)
      ->errorCorrection('H')
      ->generate($qrData);
  }

  /**
   * Valida el contenido de un código QR de receta
   *
   * @param string $qrData
   * @return bool
   */
  public function validateQRData(string $qrData): bool
  {
    $data = json_decode($qrData, true);

    if (!$data || !is_array($data)) {
      return false;
    }

    // Verificar campos obligatorios
    $requiredFields = ['CUIR', 'TIPO', 'SISTEMA', 'VERIFICACION'];
    foreach ($requiredFields as $field) {
      if (!isset($data[$field])) {
        return false;
      }
    }

    // Verificar que el CUIR existe en la base de datos
    return \App\Models\Prescription::where('cuir', $data['CUIR'])->exists();
  }
}
