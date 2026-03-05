<?php

namespace App\Console\Commands;

use App\Models\HealthInsurance;
use Illuminate\Console\Command;

class ImportObrasSocialesFromXls extends Command
{
    protected $signature = 'obras-sociales:import-xls
                          {path : Ruta del archivo XLS/TSV descargado}
                          {--truncate : Vaciar tabla antes de importar}';

    protected $description = 'Importar obras sociales desde archivo XLS/TSV al modelo HealthInsurance';

    public function handle(): int
    {
        $path = (string) $this->argument('path');

        if (!is_file($path)) {
            $this->error("No se encontró el archivo: {$path}");
            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            HealthInsurance::query()->delete();
            $this->warn('Tabla health_insurances vaciada.');
        }

        $lines = @file($path, FILE_IGNORE_NEW_LINES);

        if (!$lines || count($lines) === 0) {
            $this->error('El archivo está vacío o no se pudo leer.');
            return self::FAILURE;
        }

        $headerIndex = $this->findHeaderIndex($lines);

        if ($headerIndex === null) {
            $this->error('No se encontró la cabecera con columna RNAS.');
            return self::FAILURE;
        }

        $headers = $this->parseDelimitedLine($lines[$headerIndex]);
        $headers = array_map(fn($h) => $this->normalizeHeader((string) $h), $headers);

        $created = 0;
        $updated = 0;
        $skipped = 0;

        for ($i = $headerIndex + 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line === '') {
                continue;
            }

            $row = $this->parseDelimitedLine($lines[$i]);

            if (count($row) < 2) {
                $skipped++;
                continue;
            }

            $data = array_combine(
                $headers,
                array_pad($row, count($headers), null)
            );

            if (!$data) {
                $skipped++;
                continue;
            }

            $code = $this->clean($data['rnas'] ?? null);
            $name = $this->clean($data['nombre'] ?? null);

            if (!$name) {
                $skipped++;
                continue;
            }

            $phone = $this->clean($data['telefono'] ?? null);
            $email = $this->normalizeEmail($data['e_mail'] ?? null);
            $habilita = strtoupper($this->clean($data['habilita_opcion'] ?? '') ?? '');
            $isActive = $habilita !== 'NO';

            $notes = $this->buildNotes($data);

            $payload = [
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'is_active' => $isActive,
                'notes' => $notes,
            ];

            if ($code) {
                $existing = HealthInsurance::query()->where('code', $code)->first();

                if ($existing) {
                    $existing->update($payload);
                    $updated++;
                } else {
                    HealthInsurance::create(array_merge($payload, ['code' => $code]));
                    $created++;
                }
            } else {
                $existing = HealthInsurance::query()->where('name', $name)->first();

                if ($existing) {
                    $existing->update($payload);
                    $updated++;
                } else {
                    HealthInsurance::create($payload);
                    $created++;
                }
            }
        }

        $this->info("Importación finalizada. Creadas: {$created}, actualizadas: {$updated}, omitidas: {$skipped}");

        return self::SUCCESS;
    }

    private function findHeaderIndex(array $lines): ?int
    {
        foreach ($lines as $index => $line) {
            $columns = $this->parseDelimitedLine((string) $line);
            $firstColumn = $this->normalizeHeader((string) ($columns[0] ?? ''));

            if ($firstColumn === 'rnas') {
                return $index;
            }
        }

        return null;
    }

    private function parseDelimitedLine(string $line): array
    {
        $utf8Line = $this->toUtf8($line);

        $utf8Line = preg_replace('/^\xEF\xBB\xBF/u', '', $utf8Line) ?? $utf8Line;

        if (!str_contains($utf8Line, "\t")) {
            $utf8Line = preg_replace('/\s{2,}/u', "\t", trim($utf8Line)) ?? trim($utf8Line);
        }

        return str_getcsv($utf8Line, "\t");
    }

    private function normalizeHeader(string $value): string
    {
        $value = trim($this->toUtf8($value));
        $value = mb_strtolower($value);
        $value = str_replace(['-', ' '], '_', $value);
        $value = preg_replace('/[^a-z0-9_]/u', '', $value) ?? $value;

        return $value;
    }

    private function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($this->toUtf8($value));

        return $value === '' ? null : preg_replace('/\s+/', ' ', $value);
    }

    private function toUtf8(string $value): string
    {
        $encoding = mb_detect_encoding($value, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true);
        if (!$encoding) {
            $encoding = 'Windows-1252';
        }

        return mb_convert_encoding($value, 'UTF-8', $encoding);
    }

    private function normalizeEmail(?string $raw): ?string
    {
        $email = $this->clean($raw);

        if (!$email) {
            return null;
        }

        if (str_contains($email, ' ') || str_contains($email, ';') || str_contains($email, ',')) {
            $parts = preg_split('/[\s;,]+/', $email);
            $email = $parts[0] ?? null;
        }

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return mb_strtolower($email);
    }

    private function buildNotes(array $data): ?string
    {
        $parts = [];

        $domicilio = $this->clean($data['domicilio'] ?? null);
        $localidad = $this->clean($data['localidad'] ?? null);
        $cp = $this->clean($data['cp'] ?? null);
        $provincia = $this->clean($data['provincia'] ?? null);
        $otrosTelefonos = $this->clean($data['otros_telefonos'] ?? null);
        $web = $this->clean($data['web'] ?? null);
        $habilita = $this->clean($data['habilita_opcion'] ?? null);
        $sigla = $this->clean($data['sigla'] ?? null);

        if ($sigla) $parts[] = "Sigla: {$sigla}";
        if ($domicilio) $parts[] = "Domicilio: {$domicilio}";
        if ($localidad) $parts[] = "Localidad: {$localidad}";
        if ($cp) $parts[] = "CP: {$cp}";
        if ($provincia) $parts[] = "Provincia: {$provincia}";
        if ($otrosTelefonos) $parts[] = "Otros teléfonos: {$otrosTelefonos}";
        if ($web) $parts[] = "Web: {$web}";
        if ($habilita) $parts[] = "Habilita opción: {$habilita}";

        if (count($parts) === 0) {
            return null;
        }

        return implode(' | ', $parts);
    }
}
