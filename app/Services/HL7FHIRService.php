<?php

namespace App\Services;

use App\Models\Prescription;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Servicio HL7 FHIR para interoperabilidad con farmacias y sistemas de salud
 * Implementa el estándar FHIR (Fast Healthcare Interoperability Resources)
 * requerido por el Ministerio de Salud para intercambiar datos médicos
 */
class HL7FHIRService
{
  /**
   * Convierte una prescripción a formato FHIR MedicationRequest
   *
   * @param Prescription $prescription
   * @return array
   */
  public function prescriptionToMedicationRequest(Prescription $prescription): array
  {
    $fhirUrl = config('app.url');

    return [
      'resourceType' => 'MedicationRequest',
      'id' => (string)$prescription->id,
      'meta' => [
        'profile' => [
          'http://hl7.org/fhir/StructureDefinition/MedicationRequest'
        ],
        'lastUpdated' => $prescription->updated_at->toIso8601String(),
      ],
      'identifier' => [
        [
          'system' => "{$fhirUrl}/prescriptions",
          'value' => $prescription->cuir,
        ]
      ],
      'status' => $this->mapPrescriptionStatus($prescription->estado_dispensacion),
      'intent' => 'order',
      'category' => [
        [
          'coding' => [
            [
              'system' => 'http://terminology.hl7.org/CodeSystem/medicationrequest-category',
              'code' => 'outpatient',
              'display' => 'Outpatient'
            ]
          ]
        ]
      ],
      'subject' => [
        'reference' => "Patient/{$prescription->patient_id}",
        'type' => 'Patient',
        'display' => $prescription->paciente_nombre_completo
      ],
      'authoredOn' => $prescription->fecha_emision->toIso8601String(),
      'requester' => [
        'reference' => "Practitioner/{$prescription->doctor_id}",
        'type' => 'Practitioner',
        'display' => $prescription->profesional_nombre_completo
      ],
      'reasonCode' => [
        [
          'coding' => [
            [
              'system' => 'http://hl7.org/fhir/sid/icd-10',
              'code' => $prescription->cie10_codigo,
              'display' => $prescription->cie10_descripcion
            ]
          ]
        ]
      ],
      'dosageInstruction' => $this->buildDosageInstructions($prescription),
      'messageSet' => [
        'value' => $prescription->paciente_cuil,
      ],
      'extension' => [
        [
          'url' => 'http://extensions.renapdis.msal.gov.ar/cuir',
          'valueString' => $prescription->cuir
        ],
        [
          'url' => 'http://extensions.renapdis.msal.gov.ar/matricula',
          'valueString' => $prescription->matricula_profesional
        ],
        [
          'url' => 'http://extensions.renapdis.msal.gov.ar/estado_dispensacion',
          'valueCode' => $prescription->estado_dispensacion
        ],
        [
          'url' => 'http://extensions.renapdis.msal.gov.ar/fecha_vencimiento',
          'valueDateTime' => $prescription->fecha_vencimiento->toIso8601String()
        ]
      ]
    ];
  }

  /**
   * Convierte un paciente a formato FHIR Patient
   *
   * @param Patient $patient
   * @return array
   */
  public function patientToFHIRPatient(Patient $patient): array
  {
    $fhirUrl = config('app.url');

    return [
      'resourceType' => 'Patient',
      'id' => (string)$patient->id,
      'meta' => [
        'profile' => [
          'http://hl7.org/fhir/StructureDefinition/Patient'
        ],
        'lastUpdated' => $patient->updated_at->toIso8601String(),
      ],
      'identifier' => [
        [
          'system' => 'http://www.acme.com/identifiers/patient',
          'value' => $patient->dni,
          'type' => [
            'coding' => [
              [
                'system' => 'http://terminology.hl7.org/CodeSystem/v2-0203',
                'code' => 'NI',
                'display' => 'National unique individual identifier'
              ]
            ]
          ]
        ],
        [
          'system' => 'http://identifier.renapdis.msal.gov.ar/cuil',
          'value' => $patient->cuil,
        ]
      ],
      'name' => [
        [
          'use' => 'official',
          'family' => $patient->last_name,
          'given' => [$patient->first_name]
        ]
      ],
      'gender' => strtolower($patient->gender ?? 'unknown'),
      'birthDate' => $patient->birth_date?->format('Y-m-d'),
      'contact' => $patient->emergency_contact_name ? [
        [
          'name' => [
            'text' => $patient->emergency_contact_name
          ],
          'telecom' => [
            [
              'system' => 'phone',
              'value' => $patient->emergency_contact_phone
            ]
          ]
        ]
      ] : [],
      'address' => [
        [
          'use' => 'home',
          'type' => 'physical',
          'text' => $patient->address,
          'city' => $patient->city,
          'postalCode' => $patient->zip_code,
        ]
      ],
      'telecom' => [
        [
          'system' => 'phone',
          'value' => $patient->phone,
          'use' => 'home'
        ],
        [
          'system' => 'email',
          'value' => $patient->email,
          'use' => 'work'
        ]
      ]
    ];
  }

  /**
   * Convierte un médico a formato FHIR Practitioner
   *
   * @param User $doctor
   * @return array
   */
  public function doctorToFHIRPractitioner(User $doctor): array
  {
    return [
      'resourceType' => 'Practitioner',
      'id' => (string)$doctor->id,
      'meta' => [
        'profile' => [
          'http://hl7.org/fhir/StructureDefinition/Practitioner'
        ],
        'lastUpdated' => $doctor->updated_at->toIso8601String(),
      ],
      'identifier' => [
        [
          'system' => 'http://identifier.renapdis.msal.gov.ar/matricula-nacional',
          'value' => $doctor->matricula_nacional
        ],
        [
          'system' => 'http://identifier.renapdis.msal.gov.ar/matricula-provincial',
          'value' => $doctor->matricula_provincial
        ],
        [
          'system' => 'http://identifier.renapdis.msal.gov.ar/cuil',
          'value' => $doctor->cuil
        ]
      ],
      'name' => [
        [
          'use' => 'official',
          'text' => $doctor->name
        ]
      ],
      'telecom' => [
        [
          'system' => 'phone',
          'value' => $doctor->consultorio_telefono,
          'use' => 'work'
        ],
        [
          'system' => 'email',
          'value' => $doctor->email,
          'use' => 'work'
        ]
      ],
      'address' => [
        [
          'use' => 'work',
          'text' => $doctor->consultorio_direccion,
        ]
      ],
      'qualification' => [
        [
          'identifier' => [
            [
              'system' => 'http://identifier.renapdis.msal.gov.ar/especialidad',
              'value' => $doctor->specialty || 'Medicina General'
            ]
          ],
          'code' => [
            'coding' => [
              [
                'system' => 'http://terminology.hl7.org/CodeSystem/v2-0360',
                'code' => 'MD',
                'display' => 'Doctor of Medicine'
              ]
            ]
          ]
        ]
      ]
    ];
  }

  /**
   * Construye un Bundle FHIR completo para una receta
   *
   * @param Prescription $prescription
   * @return array
   */
  public function createPrescriptionBundle(Prescription $prescription): array
  {
    $bundleId = Str::uuid();

    return [
      'resourceType' => 'Bundle',
      'id' => $bundleId,
      'type' => 'collection',
      'timestamp' => now()->toIso8601String(),
      'entry' => [
        [
          'resource' => $this->medicationRequestToFHIR($prescription),
          'search' => [
            'mode' => 'match'
          ]
        ],
        [
          'resource' => $this->patientToFHIRPatient($prescription->patient),
          'search' => [
            'mode' => 'match'
          ]
        ],
        [
          'resource' => $this->doctorToFHIRPractitioner($prescription->doctor),
          'search' => [
            'mode' => 'match'
          ]
        ]
      ]
    ];
  }

  /**
   * Mapea el estado de dispensación a estado FHIR
   *
   * @param string $status
   * @return string
   */
  protected function mapPrescriptionStatus(string $status): string
  {
    return match ($status) {
      'pendiente' => 'active',
      'dispensada' => 'completed',
      'anulada' => 'cancelled',
      'vencida' => 'stopped',
      default => 'unknown'
    };
  }

  /**
   * Construye las instrucciones de dosificación en formato FHIR
   *
   * @param Prescription $prescription
   * @return array
   */
  protected function buildDosageInstructions(Prescription $prescription): array
  {
    $dosages = [];

    foreach ($prescription->medicamentos_genericos ?? [] as $med) {
      $dosages[] = [
        'sequence' => count($dosages) + 1,
        'text' => "{$med['presentacion']} - {$med['posologia']}",
        'timing' => [
          'repeat' => [
            'frequency' => 1,
            'period' => $this->parseDuration($med['duracion'] ?? ''),
            'periodUnit' => 'd'
          ]
        ],
        'route' => [
          'coding' => [
            [
              'system' => 'http://snomed.info/sct',
              'code' => '26643006',
              'display' => 'Oral route'
            ]
          ]
        ],
        'doseAndRate' => [
          [
            'type' => [
              'coding' => [
                [
                  'system' => 'http://terminology.hl7.org/CodeSystem/dose-rate-type',
                  'code' => 'ordered',
                  'display' => 'Ordered'
                ]
              ]
            ],
            'doseQuantity' => [
              'value' => $med['cantidad'],
              'unit' => $med['forma_farmaceutica'],
            ]
          ]
        ]
      ];
    }

    return $dosages;
  }

  /**
   * Parsea la duración a número de días
   *
   * @param string $duration
   * @return int
   */
  protected function parseDuration(string $duration): int
  {
    if (preg_match('/(\d+)\s*(?:días?|d|days?)/i', $duration, $matches)) {
      return (int)$matches[1];
    }

    return 1;
  }

  /**
   * Exporta una receta a JSON FHIR
   *
   * @param Prescription $prescription
   * @return string
   */
  public function exportToJSON(Prescription $prescription): string
  {
    $bundle = $this->createPrescriptionBundle($prescription);

    return json_encode($bundle, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
  }

  /**
   * Exporta una receta a XML FHIR
   *
   * @param Prescription $prescription
   * @return string
   */
  public function exportToXML(Prescription $prescription): string
  {
    $bundle = $this->createPrescriptionBundle($prescription);

    $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Bundle></Bundle>');
    $this->arrayToXml($bundle, $xml);

    return $xml->asXML();
  }

  /**
   * Convierte un array a XML
   *
   * @param array $data
   * @param \SimpleXMLElement $xml
   * @return void
   */
  protected function arrayToXml(array $data, \SimpleXMLElement &$xml): void
  {
    foreach ($data as $key => $value) {
      if (is_array($value)) {
        $this->arrayToXml($value, $xml->addChild($key));
      } else {
        $xml->addChild($key, htmlspecialchars((string)$value));
      }
    }
  }

  /**
   * Alias para prescriptionToMedicationRequest
   */
  public function medicationRequestToFHIR(Prescription $prescription): array
  {
    return $this->prescriptionToMedicationRequest($prescription);
  }
}
