<?php

/**
 * Script de ejemplo para configurar permisos de módulos en usuarios existentes
 *
 * Uso: php configure_user_modules.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║   Configurador de Permisos de Módulos por Usuario         ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Ejemplo 1: Doctor con acceso solo a Pacientes y Citas
echo "📋 Ejemplo 1: Configurar doctor con acceso limitado\n";
echo "─────────────────────────────────────────────────────────────\n";

$doctor = User::where('email', 'doctor@gestor.com')->first();
if ($doctor) {
  $doctor->update([
    'allowed_modules' => ['patients', 'appointments', 'calendar']
  ]);
  echo "✅ Doctor actualizado: solo acceso a Pacientes, Citas y Calendario\n";
  echo "   Email: doctor@gestor.com\n\n";
} else {
  echo "⚠️  Doctor no encontrado\n\n";
}

// Ejemplo 2: Enfermera con acceso solo a Internación
echo "📋 Ejemplo 2: Configurar enfermera con acceso a Internación\n";
echo "─────────────────────────────────────────────────────────────\n";

$nurse = User::where('email', 'enfermera@gestor.com')->first();
if ($nurse) {
  $nurse->update([
    'allowed_modules' => ['patients', 'hospitalizations']
  ]);
  echo "✅ Enfermera actualizada: solo acceso a Pacientes e Internación\n";
  echo "   Email: enfermera@gestor.com\n\n";
} else {
  echo "⚠️  Enfermera no encontrada\n\n";
}

// Ejemplo 3: Restaurar acceso completo (eliminar restricciones)
echo "📋 Ejemplo 3: Restaurar acceso completo\n";
echo "─────────────────────────────────────────────────────────────\n";

$secretary = User::where('email', 'secretaria@gestor.com')->first();
if ($secretary) {
  $secretary->update([
    'allowed_modules' => null  // o [] para array vacío
  ]);
  echo "✅ Secretaria actualizada: acceso completo restaurado\n";
  echo "   Email: secretaria@gestor.com\n\n";
} else {
  echo "⚠️  Secretaria no encontrada\n\n";
}

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  Módulos disponibles para configurar:                     ║\n";
echo "╠════════════════════════════════════════════════════════════╣\n";
echo "║  • patients          - Pacientes                          ║\n";
echo "║  • appointments      - Citas                              ║\n";
echo "║  • calendar          - Calendario                         ║\n";
echo "║  • reports           - Reportes                           ║\n";
echo "║  • schedules         - Horarios                           ║\n";
echo "║  • pharmacy_requests - Solicitudes Farmacia               ║\n";
echo "║  • operations        - Quirófanos                         ║\n";
echo "║  • hospitalizations  - Internación                        ║\n";
echo "║  • pre_admissions    - Pre-Internación                    ║\n";
echo "║  • emergency         - Emergencias                        ║\n";
echo "║  • accounting        - Contabilidad                       ║\n";
echo "║  • maintenance       - Mantenimiento                      ║\n";
echo "║  • paramedic         - Paramédicos                        ║\n";
echo "║  • admin             - Administración                     ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "💡 Tip: Los administradores siempre tienen acceso total.\n";
echo "💡 Tip: Si allowed_modules está vacío, el usuario tiene acceso completo.\n\n";

echo "✅ Script completado\n";
