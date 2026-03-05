<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            // CUIR - Clave Única de Identificación de Receta (Obligatorio ReNaPDiS)
            $table->string('cuir', 50)->unique()->nullable()->after('id');

            // Información del profesional (Validación REFEPS)
            $table->string('matricula_profesional', 50)->nullable()->after('doctor_id');
            $table->string('matricula_tipo', 20)->nullable()->after('matricula_profesional'); // nacional/provincial
            $table->string('profesional_nombre_completo')->nullable()->after('matricula_tipo');
            $table->string('profesional_especialidad')->nullable()->after('profesional_nombre_completo');
            $table->string('consultorio_direccion')->nullable()->after('profesional_especialidad');

            // Información del paciente (Validación RENAPER)
            $table->string('paciente_cuil', 11)->nullable()->after('patient_id');
            $table->string('paciente_nombre_completo')->nullable()->after('paciente_cuil');
            $table->date('paciente_fecha_nacimiento')->nullable()->after('paciente_nombre_completo');
            $table->string('obra_social')->nullable()->after('paciente_fecha_nacimiento');
            $table->string('numero_afiliado')->nullable()->after('obra_social');

            // Medicamentos con nombre genérico (Ley 25.649)
            $table->json('medicamentos_genericos')->nullable()->after('medications');
            // Estructura: [{
            //   nombre_generico: string (DCI - Denominación Común Internacional),
            //   nombre_comercial: string (opcional),
            //   forma_farmaceutica: string,
            //   presentacion: string,
            //   cantidad: number,
            //   cantidad_letras: string,
            //   codigo_troquel: string (opcional),
            //   codigo_barra: string (opcional)
            // }]

            // Diagnóstico CIE-10
            $table->string('cie10_codigo', 10)->nullable()->after('diagnosis');
            $table->string('cie10_descripcion')->nullable()->after('cie10_codigo');

            // Vigencia y validación de la receta
            $table->timestamp('fecha_emision')->nullable()->after('created_at');
            $table->timestamp('fecha_vencimiento')->nullable()->after('fecha_emision'); // 30 días desde emisión
            $table->enum('estado_dispensacion', ['pendiente', 'dispensada', 'anulada', 'vencida'])->default('pendiente')->after('status');
            $table->timestamp('fecha_dispensacion')->nullable()->after('estado_dispensacion');
            $table->string('farmacia_dispensadora')->nullable()->after('fecha_dispensacion');

            // Firma electrónica y seguridad
            $table->text('firma_electronica_hash')->nullable()->after('notes');
            $table->string('firma_metodo', 20)->nullable()->after('firma_electronica_hash'); // 2fa/digital
            $table->timestamp('firma_timestamp')->nullable()->after('firma_metodo');
            $table->string('firma_ip_address', 45)->nullable()->after('firma_timestamp');

            // QR y validación visual
            $table->string('qr_code_path')->nullable()->after('firma_ip_address');
            $table->text('qr_code_data')->nullable()->after('qr_code_path');

            // Validación con sistemas externos
            $table->boolean('validado_refeps')->default(false)->after('qr_code_data');
            $table->boolean('validado_renaper')->default(false)->after('validado_refeps');
            $table->timestamp('fecha_validacion_externa')->nullable()->after('validado_renaper');

            // Auditoría adicional
            $table->json('log_modificaciones')->nullable()->after('fecha_validacion_externa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn([
                'cuir',
                'matricula_profesional',
                'matricula_tipo',
                'profesional_nombre_completo',
                'profesional_especialidad',
                'consultorio_direccion',
                'paciente_cuil',
                'paciente_nombre_completo',
                'paciente_fecha_nacimiento',
                'obra_social',
                'numero_afiliado',
                'medicamentos_genericos',
                'cie10_codigo',
                'cie10_descripcion',
                'fecha_emision',
                'fecha_vencimiento',
                'estado_dispensacion',
                'fecha_dispensacion',
                'farmacia_dispensadora',
                'firma_electronica_hash',
                'firma_metodo',
                'firma_timestamp',
                'firma_ip_address',
                'qr_code_path',
                'qr_code_data',
                'validado_refeps',
                'validado_renaper',
                'fecha_validacion_externa',
                'log_modificaciones',
            ]);
        });
    }
};
