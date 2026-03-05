<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class DownloadObrasSociales extends Command
{
  protected $signature = 'obras-sociales:download';

  protected $description = 'Descargar la lista de obras sociales argentinas desde GitHub';

  public function handle(): int
  {
    $this->info('Descargando obras sociales argentinas...');

    try {
      // URL del repositorio de cluster311
      $url = 'https://raw.githubusercontent.com/cluster311/obras-sociales-argentinas/master/oss_ar/data/obras_sociales.json';

      // Alternativa: si lo anterior no funciona, intentar otra fuente
      $this->info('Intentando descargar desde: ' . $url);

      $response = Http::timeout(30)->get($url);

      if (!$response->successful()) {
        // Intentar con otra estructura de GitHub
        $url = 'https://api.github.com/repos/cluster311/obras-sociales-argentinas/contents/oss_ar/data/obras_sociales.json';
        $this->info('Intentando con GitHub API...');
        $response = Http::timeout(30)->get($url);

        if (!$response->successful()) {
          $this->error('No se pudo descargar el archivo desde GitHub');
          return self::FAILURE;
        }

        // Si viene de API, está base64 codificado
        $data = json_decode($response->json()['content'], true);
        $content = base64_decode($response->json()['content']);
      } else {
        $content = $response->body();
      }

      // Validar que sea JSON válido
      $decoded = json_decode($content, true);
      if ($decoded === null) {
        $this->error('El archivo descargado no es JSON válido');
        return self::FAILURE;
      }

      // Guardar en storage
      $path = storage_path('app/obras_sociales.json');
      File::ensureDirectoryExists(dirname($path));
      File::put($path, $content);

      $count = is_array($decoded) ? count($decoded) : 0;
      $this->info("✓ Obras sociales descargadas exitosamente: {$count} registros");
      $this->info("Guardado en: {$path}");

      return self::SUCCESS;
    } catch (\Exception $e) {
      $this->error('Error al descargar: ' . $e->getMessage());
      return self::FAILURE;
    }
  }
}
