<?php

namespace Database\Seeders;

use App\Models\RequiredDocument;
use Illuminate\Database\Seeder;

class RequiredDocumentSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $documents = [
      [
        'name' => 'Documento de Identidad',
        'code' => 'DNI',
        'description' => 'Cédula Nacional de Identidad o Pasaporte válido',
        'applicability' => 'all_surgeries',
        'is_mandatory' => true,
        'requires_upload' => true,
        'status' => 'active',
      ],
      [
        'name' => 'Consentimiento Informado',
        'code' => 'FORM_CONSENT',
        'description' => 'Formulario de consentimiento informado firmado',
        'applicability' => 'all_surgeries',
        'is_mandatory' => true,
        'requires_upload' => true,
        'status' => 'active',
      ],
      [
        'name' => 'Historia Clínica Completa',
        'code' => 'MEDICAL_HISTORY',
        'description' => 'Historia médica del paciente con antecedentes',
        'applicability' => 'all_surgeries',
        'is_mandatory' => true,
        'requires_upload' => false,
        'status' => 'active',
      ],
      [
        'name' => 'Análisis de Laboratorio',
        'code' => 'LAB_TESTS',
        'description' => 'Análisis de sangre, coagulación, glucosa',
        'applicability' => 'all_surgeries',
        'is_mandatory' => true,
        'requires_upload' => true,
        'status' => 'active',
      ],
      [
        'name' => 'Radiografía/Imaging',
        'code' => 'IMAGING',
        'description' => 'Radiografías, TAC, RMN u otros estudios de imagen',
        'applicability' => 'by_operation_type',
        'is_mandatory' => false,
        'requires_upload' => true,
        'status' => 'active',
      ],
      [
        'name' => 'Evaluación Cardiológica',
        'code' => 'CARDIO_EVAL',
        'description' => 'ECG y/o evaluación cardiológica si es necesario',
        'applicability' => 'by_operation_type',
        'is_mandatory' => false,
        'requires_upload' => true,
        'status' => 'active',
      ],
      [
        'name' => 'Autorización de Obra Social',
        'code' => 'INSURANCE_AUTH',
        'description' => 'Autorización de la obra social o seguro privado',
        'applicability' => 'by_insurance',
        'is_mandatory' => true,
        'requires_upload' => true,
        'status' => 'active',
      ],
      [
        'name' => 'Declaración de Ayuno',
        'code' => 'FASTING_DECL',
        'description' => 'Declaración del paciente sobre cumplimiento de ayuno',
        'applicability' => 'all_surgeries',
        'is_mandatory' => true,
        'requires_upload' => false,
        'status' => 'active',
      ],
      [
        'name' => 'Control de Presión Arterial',
        'code' => 'BP_CHECK',
        'description' => 'Presión arterial registrada antes de la internación',
        'applicability' => 'all_surgeries',
        'is_mandatory' => true,
        'requires_upload' => false,
        'status' => 'active',
      ],
      [
        'name' => 'Testeo COVID-19',
        'code' => 'COVID_TEST',
        'description' => 'Resultado negativo de testeo COVID-19 (si aplica)',
        'applicability' => 'custom',
        'is_mandatory' => false,
        'requires_upload' => true,
        'status' => 'active',
      ],
    ];

    foreach ($documents as $doc) {
      RequiredDocument::updateOrCreate(
        ['code' => $doc['code']],
        $doc
      );
    }
  }
}
