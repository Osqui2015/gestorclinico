<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indicaciones Médicas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }

        .clinic-name {
            font-size: 24px;
            font-weight: bold;
            color: #1a5490;
            margin-bottom: 5px;
        }

        .contact-info {
            font-size: 10px;
            color: #666;
        }

        .document-title {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            border: 2px solid #27ae60;
            padding: 10px;
            background-color: #e8f8f5;
            color: #27ae60;
        }

        .patient-info {
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            font-size: 12px;
        }

        .info-section {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-weight: bold;
            color: #27ae60;
            margin-bottom: 5px;
        }

        .info-value {
            padding: 5px;
            border-bottom: 1px solid #ccc;
            min-height: 20px;
        }

        .instructions-section {
            margin: 30px 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #27ae60;
            background-color: #e8f8f5;
            padding: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #27ae60;
        }

        .instruction-item {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #27ae60;
            border-radius: 4px;
            background-color: #f9fdf8;
            font-size: 12px;
        }

        .instruction-number {
            display: inline-block;
            width: 25px;
            height: 25px;
            background-color: #27ae60;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 25px;
            font-weight: bold;
            margin-right: 10px;
            font-size: 14px;
        }

        .instruction-text {
            display: inline-block;
            vertical-align: middle;
            line-height: 1.5;
        }

        .important-box {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 12px;
        }

        .important-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 10px;
        }

        .important-content {
            color: #856404;
            line-height: 1.6;
        }

        .warning-box {
            background-color: #f8d7da;
            border: 2px solid #f5c6cb;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 12px;
        }

        .warning-title {
            font-weight: bold;
            color: #721c24;
            margin-bottom: 10px;
        }

        .warning-content {
            color: #721c24;
            line-height: 1.6;
        }

        .follow-up-section {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 20px 0;
            font-size: 12px;
        }

        .follow-up-title {
            font-weight: bold;
            color: #1565c0;
            margin-bottom: 10px;
        }

        .follow-up-content {
            color: #1565c0;
            line-height: 1.6;
        }

        .footer {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            font-size: 11px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000;
            padding-top: 10px;
            margin-bottom: 5px;
        }

        .doctor-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .date-section {
            text-align: right;
            font-size: 11px;
            margin-bottom: 20px;
        }

        .date-time {
            display: block;
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .bullet-list {
            margin-left: 20px;
            margin-top: 10px;
            font-size: 12px;
            line-height: 1.8;
        }

        .bullet-list li {
            margin-bottom: 8px;
            color: #333;
        }

        .section-separator {
            border-top: 2px solid #27ae60;
            margin: 30px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="clinic-name">GESTOR CLÍNICO</div>
            <div class="contact-info">
                Consultorio Médico | Atención Profesional
            </div>
        </div>

        <!-- Date -->
        <div class="date-section">
            <strong>Fecha:</strong> {{ now()->format('d/m/Y') }}
        </div>

        <!-- Document Title -->
        <div class="document-title">
            📋 INDICACIONES Y RECOMENDACIONES MÉDICAS
        </div>

        <!-- Patient Information -->
        <div class="patient-info">
            <div class="info-section">
                <div class="info-label">PACIENTE</div>
                <div class="info-value">{{ $patient->first_name }} {{ $patient->last_name }}</div>

                <div class="info-label" style="margin-top: 10px;">DNI / ID</div>
                <div class="info-value">{{ $patient->dni }}</div>
            </div>

            <div class="info-section">
                <div class="info-label">MÉDICO TRATANTE</div>
                <div class="info-value">{{ $doctor->name }}</div>

                <div class="info-label" style="margin-top: 10px;">ESPECIALIDAD</div>
                <div class="info-value">Medicina General</div>
            </div>
        </div>

        <div class="section-separator"></div>

        <!-- Instructions -->
        <div class="instructions-section">
            <div class="section-title">✓ INDICACIONES A SEGUIR</div>

            @foreach ($prescription->instructions as $index => $instruction)
                <div class="instruction-item">
                    <span class="instruction-number">{{ $index + 1 }}</span>
                    <span class="instruction-text">
                        {!! nl2br(e($instruction['description'])) !!}
                    </span>
                </div>
            @endforeach
        </div>

        <!-- Important Information -->
        <div class="important-box">
            <div class="important-title">⚠️ INFORMACIÓN IMPORTANTE</div>
            <div class="important-content">
                <ul class="bullet-list">
                    <li>Sigue las indicaciones exactamente como se describe</li>
                    <li>Si experimenta reacciones adversas, contacta inmediatamente al médico</li>
                    <li>Mantén una copia de este documento para futuras referencias</li>
                    <li>No suspendas el tratamiento sin consultar al médico</li>
                </ul>
            </div>
        </div>

        <!-- Medications Info (referenced from prescription) -->
        <div class="follow-up-section">
            <div class="follow-up-title">💊 RECORDATORIO DE MEDICAMENTOS</div>
            <div class="follow-up-content">
                <p>Consulta la receta adjunta para la información detallada de los medicamentos prescritos, incluyendo
                    dosis, frecuencia y duración del tratamiento.</p>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="important-box">
            <div class="important-title">📞 CONTACTO DE EMERGENCIA</div>
            <div class="important-content">
                Si tienes problemas o necesitas asistencia médica, contacta:
                <ul class="bullet-list">
                    <li><strong>Consultorio:</strong> [Número de contacto]</li>
                    <li><strong>Emergencias:</strong> 911</li>
                    <li><strong>Horario de atención:</strong> Lunes a Viernes, 8:00 AM - 6:00 PM</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>
                <div class="signature-line"></div>
                <div class="doctor-name">{{ $doctor->name }}</div>
                <div>Firma del Médico</div>
            </div>
            <div>
                <div class="signature-line"></div>
                <div>Firma o Huella del Paciente</div>
            </div>
        </div>

        <div class="date-time">
            <p>Documento generado por GESTOR CLÍNICO - {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Ref: {{ $prescription->id }} | Este documento tiene validez legal</p>
        </div>
    </div>
</body>

</html>
