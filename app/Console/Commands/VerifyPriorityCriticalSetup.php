<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use App\Models\HealthInsurance;
use App\Models\Invoice;

class VerifyPriorityCriticalSetup extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'verify:critical-setup';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Verifica que todas las funcionalidades de prioridad crítica estén instaladas correctamente';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $this->info('');
    $this->info('🔍 Verificando instalación de Prioridad Crítica...');
    $this->info('');

    $errors = 0;

    // Verificar conexión a base de datos
    $this->info('1. Verificando conexión a base de datos...');
    try {
      DB::connection()->getPdo();
      $this->info('   ✅ Conexión a MySQL exitosa');
    } catch (\Exception $e) {
      $this->error('   ❌ No se puede conectar a MySQL');
      $this->error('      Asegúrate de que Laragon esté corriendo');
      $errors++;
    }

    // Verificar tablas nuevas
    $this->info('');
    $this->info('2. Verificando nuevas tablas...');
    $requiredTables = [
      'doctor_schedules',
      'doctor_exceptions',
      'health_insurances',
      'patient_insurance',
      'invoices',
      'invoice_items',
      'payments',
    ];

    foreach ($requiredTables as $table) {
      if (Schema::hasTable($table)) {
        $this->info("   ✅ Tabla '{$table}' existe");
      } else {
        $this->error("   ❌ Tabla '{$table}' NO existe");
        $this->error("      Ejecuta: php artisan migrate");
        $errors++;
      }
    }

    // Verificar columnas en appointments
    $this->info('');
    $this->info('3. Verificando mejoras en appointments...');
    $appointmentColumns = [
      'duration',
      'is_walk_in',
      'confirmed',
      'confirmed_at',
      'checked_in_at',
      'no_show_count',
      'cancellation_reason',
      'cancelled_at',
    ];

    foreach ($appointmentColumns as $column) {
      if (Schema::hasColumn('appointments', $column)) {
        $this->info("   ✅ Columna 'appointments.{$column}' existe");
      } else {
        $this->error("   ❌ Columna 'appointments.{$column}' NO existe");
        $errors++;
      }
    }

    // Verificar columnas en patients
    $this->info('');
    $this->info('4. Verificando mejoras en patients...');
    $patientColumns = [
      'address',
      'city',
      'zip_code',
      'gender',
      'emergency_contact_name',
      'emergency_contact_phone',
      'allergies',
      'notes',
    ];

    foreach ($patientColumns as $column) {
      if (Schema::hasColumn('patients', $column)) {
        $this->info("   ✅ Columna 'patients.{$column}' existe");
      } else {
        $this->error("   ❌ Columna 'patients.{$column}' NO existe");
        $errors++;
      }
    }

    // Verificar modelos
    $this->info('');
    $this->info('5. Verificando modelos...');
    $models = [
      'App\Models\DoctorSchedule',
      'App\Models\DoctorException',
      'App\Models\HealthInsurance',
      'App\Models\Invoice',
      'App\Models\InvoiceItem',
      'App\Models\Payment',
    ];

    foreach ($models as $model) {
      if (class_exists($model)) {
        $this->info("   ✅ Modelo '{$model}' existe");
      } else {
        $this->error("   ❌ Modelo '{$model}' NO existe");
        $errors++;
      }
    }

    // Verificar controladores
    $this->info('');
    $this->info('6. Verificando controladores...');
    $controllers = [
      'App\Http\Controllers\DoctorScheduleController',
      'App\Http\Controllers\InvoiceController',
      'App\Http\Controllers\HealthInsuranceController',
      'App\Http\Controllers\ReceptionController',
    ];

    foreach ($controllers as $controller) {
      if (class_exists($controller)) {
        $this->info("   ✅ Controlador '{$controller}' existe");
      } else {
        $this->error("   ❌ Controlador '{$controller}' NO existe");
        $errors++;
      }
    }

    // Verificar datos de prueba (si se ejecutó el seeder)
    $this->info('');
    $this->info('7. Verificando datos de prueba (opcional)...');

    $userCount = User::count();
    $patientCount = Patient::count();
    $scheduleCount = DoctorSchedule::count();
    $insuranceCount = HealthInsurance::count();

    if ($userCount >= 5) {
      $this->info("   ✅ {$userCount} usuarios creados");
    } else {
      $this->warn("   ⚠️  Solo {$userCount} usuarios. Ejecuta el seeder para datos de prueba:");
      $this->warn("      php artisan db:seed --class=CriticalPrioritySeeder");
    }

    if ($patientCount >= 3) {
      $this->info("   ✅ {$patientCount} pacientes creados");
    } else {
      $this->warn("   ⚠️  Solo {$patientCount} pacientes");
    }

    if ($scheduleCount >= 10) {
      $this->info("   ✅ {$scheduleCount} horarios configurados");
    } else {
      $this->warn("   ⚠️  Solo {$scheduleCount} horarios");
    }

    if ($insuranceCount >= 3) {
      $this->info("   ✅ {$insuranceCount} obras sociales creadas");
    } else {
      $this->warn("   ⚠️  Solo {$insuranceCount} obras sociales");
    }

    // Verificar rutas
    $this->info('');
    $this->info('8. Verificando rutas principales...');
    $routes = [
      'doctor-schedules.index',
      'reception.dashboard',
      'invoices.index',
      'health-insurances.index',
    ];

    foreach ($routes as $route) {
      if (app('router')->getRoutes()->hasNamedRoute($route)) {
        $this->info("   ✅ Ruta '{$route}' registrada");
      } else {
        $this->error("   ❌ Ruta '{$route}' NO registrada");
        $errors++;
      }
    }

    // Resumen final
    $this->info('');
    $this->info('═══════════════════════════════════════════════════');

    if ($errors === 0) {
      $this->info('');
      $this->info('🎉 ¡TODO ESTÁ CORRECTAMENTE INSTALADO!');
      $this->info('');
      $this->info('Puedes empezar a usar el sistema:');
      $this->info('1. php artisan serve');
      $this->info('2. npm run dev');
      $this->info('3. Abre http://localhost:8000');
      $this->info('');
      $this->info('Usuarios de prueba (todos usan password: "password"):');
      $this->info('- admin@clinica.com (Admin)');
      $this->info('- secretaria@clinica.com (Secretaria)');
      $this->info('- carlosperez@clinica.com (Doctor)');
      $this->info('');
    } else {
      $this->error('');
      $this->error("❌ Se encontraron {$errors} errores");
      $this->error('');
      $this->error('Soluciones:');
      $this->error('- Si faltan tablas: php artisan migrate');
      $this->error('- Si faltan datos: php artisan db:seed --class=CriticalPrioritySeeder');
      $this->error('- Si faltan archivos: verifica que estén todos los archivos creados');
      $this->error('');
    }

    $this->info('═══════════════════════════════════════════════════');
    $this->info('');

    return $errors === 0 ? 0 : 1;
  }
}
