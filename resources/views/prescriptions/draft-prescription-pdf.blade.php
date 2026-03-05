<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receta Médica - BORRADOR</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            color: #000;
            background: white;
            padding: 10px;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 15px 20px;
            border: 1px solid #000;
            background: white;
            position: relative;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(255, 0, 0, 0.1);
            font-weight: bold;
            z-index: 0;
            pointer-events: none;
        }

        .content {
            position: relative;
            z-index: 1;
        }

        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .draft-badge {
            background: #ff0000;
            color: white;
            padding: 4px 8px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            margin-bottom: 5px;
        }

        .clinic-info {
            flex: 1;
            font-size: 11px;
            line-height: 1.3;
        }

        .clinic-logo {
            width: 80px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }

        .clinic-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .clinic-detail {
            font-size: 9px;
            margin: 1px 0;
        }

        .separation-line {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .section {
            margin-bottom: 10px;
            font-size: 10px;
        }

        .section-title {
            font-weight: bold;
            border-bottom: 1px solid #000;
            margin-bottom: 4px;
            padding-bottom: 2px;
            font-size: 11px;
        }

        .info-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 3px;
            font-size: 10px;
        }

        .info-item {
            display: flex;
        }

        .info-label {
            font-weight: bold;
            min-width: 80px;
            margin-right: 5px;
        }

        .info-value {
            flex: 1;
            border-bottom: 1px solid #000;
            padding-bottom: 1px;
        }

        .diagnosis-section {
            background: #f5f5f5;
            padding: 4px 6px;
            margin: 6px 0;
            border-left: 3px solid #000;
            font-size: 10px;
        }

        .medication-item {
            margin-bottom: 6px;
            padding: 3px;
            border-left: 2px solid #000;
            padding-left: 6px;
            font-size: 10px;
        }

        .med-name {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .med-details {
            font-size: 9px;
            margin-left: 10px;
            line-height: 1.2;
        }

        .footer-section {
            margin-top: 12px;
            border-top: 1px solid #000;
            padding-top: 8px;
            font-size: 9px;
        }

        .signature-area {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000;
            padding-top: 20px;
            font-size: 9px;
            font-weight: bold;
        }

        .info-data {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            margin-top: 4px;
        }

        .numbering {
            font-weight: bold;
            margin-right: 3px;
            min-width: 20px;
            display: inline-block;
        }

        .page-footer {
            text-align: center;
            font-size: 8px;
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 5px;
            color: #ff0000;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="watermark">BORRADOR</div>

        <div class="content">
            <!-- Draft Badge -->
            <div class="draft-badge">⚠ BORRADOR - NO VÁLIDO PARA USO OFICIAL</div>

            <!-- Header -->
            <div class="header">
                <div class="clinic-logo">[LOGO]</div>
                <div class="clinic-info">
                    <div class="clinic-name">{{ config('app.clinic_name', 'GESTOR CLÍNICO') }}</div>
                    <div class="clinic-detail">Dr. {{ $doctor->name }}</div>
                    @if ($doctor->specialty)
                        <div class="clinic-detail">{{ $doctor->specialty }}</div>
                    @endif
                    @if ($doctor->license_number)
                        <div class="clinic-detail">M.N. {{ $doctor->license_number }}</div>
                    @endif
                    @if ($doctor->professional_id)
                        <div class="clinic-detail">M.P. {{ $doctor->professional_id }}</div>
                    @endif
                    @if ($doctor->address)
                        <div class="clinic-detail">{{ $doctor->address }}</div>
                    @endif
                    @if ($doctor->phone)
                        <div class="clinic-detail">Tel: {{ $doctor->phone }}</div>
                    @endif
                </div>
            </div>

            <!-- Patient Information -->
            <div class="section">
                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">PACIENTE</span>
                        <span class="info-value">{{ $patient->first_name }} {{ $patient->last_name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">DNI</span>
                        <span class="info-value">{{ $patient->dni }}</span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Fecha Nac</span>
                        <span
                            class="info-value">{{ $patient->birth_date ? $patient->birth_date->format('d/m/Y') : '—' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Edad</span>
                        <span class="info-value">{{ $patient->birth_date ? $patient->birth_date->age : '—' }}
                            años</span>
                    </div>
                </div>

                <!-- Cobertura section removed for draft PDF -->
            </div>

            <div class="separation-line"></div>

            <!-- Diagnosis -->
            <div class="section">
                <div class="section-title">RP / PRESCRIPCIÓN</div>
                <div class="diagnosis-section">
                    <strong>Diagnóstico:</strong> {{ $prescription->diagnosis }}
                </div>
            </div>

            <!-- Medications -->
            <div class="section">
                @foreach ($prescription->medications as $index => $medication)
                    <div class="medication-item">
                        <div class="med-name">
                            <span class="numbering">{{ $index + 1 }}.</span>
                            {{ strtoupper($medication['name']) }}
                        </div>
                        <div class="med-details">
                            @if (isset($medication['dosage']))
                                <div>(Sugerencia: {{ $medication['dosage'] }})</div>
                            @endif
                            <div>Cantidad: [A definir] cajas de [A definir] comprimidos.</div>
                            <div>
                                Indicación:
                                @if (isset($medication['frequency']))
                                    Tomar {{ $medication['frequency'] }},
                                @endif
                                @if (isset($medication['duration']))
                                    durante {{ $medication['duration'] }}.
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="separation-line"></div>

            <!-- Footer Information -->
            <div class="footer-section">
                <div class="info-data">
                    <div>Lugar y Fecha: Buenos Aires, {{ now()->format('d \d\e F \d\e Y') }}</div>
                    <div></div>
                </div>

                <div style="margin-top: 10px; font-size: 9px; color: #666;">
                    <div>⚠ DOCUMENTO BORRADOR - NO TIENE VALIDEZ OFICIAL</div>
                    <div>Estado: PENDIENTE DE GUARDAR</div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="signature-area">
                <div class="signature-line">
                    DR. {{ strtoupper($doctor->name) }}<br />
                    Firma del Médico
                </div>
                <div class="signature-line">
                    Firma/Huella del Paciente
                </div>
            </div>

            <div class="page-footer">
                ⚠ BORRADOR - Este documento será válido solo después de guardar el registro médico
                <br />Documento generado por GESTOR CLÍNICO - {{ now()->format('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>
</body>

</html>
