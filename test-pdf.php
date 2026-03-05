<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Patient;
use App\Models\User;

try {
  echo "Testing PDF generation...\n";

  $patient = Patient::with('healthInsurances')->first();
  $doctor = User::where('role', 'doctor')->first();

  $prescription = (object)[
    'diagnosis' => 'test diagnosis',
    'medications' => [
      [
        'name' => 'Medication Test',
        'dosage' => '500mg',
        'frequency' => 'cada 8 horas',
        'duration' => '7 días'
      ]
    ],
    'instructions' => [
      ['description' => 'Test instruction']
    ],
    'notes' => 'test notes',
    'patient' => $patient,
    'doctor' => $doctor,
  ];

  echo "Loading view...\n";
  $pdf = \PDF::loadView('prescriptions.draft-prescription-pdf', [
    'prescription' => $prescription,
    'patient' => $patient,
    'doctor' => $doctor,
  ]);

  echo "Success! PDF generated.\n";
} catch (Exception $e) {
  echo "Error: " . $e->getMessage() . "\n";
  echo "File: " . $e->getFile() . "\n";
  echo "Line: " . $e->getLine() . "\n";
  echo "\nStack trace:\n" . $e->getTraceAsString();
}
