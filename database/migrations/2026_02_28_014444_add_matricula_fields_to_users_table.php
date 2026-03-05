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
        Schema::table('users', function (Blueprint $table) {
            // Campos para profesionales médicos (Validación REFEPS)
            $table->string('matricula_nacional', 50)->nullable()->after('specialty');
            $table->string('matricula_provincial', 50)->nullable()->after('matricula_nacional');
            $table->string('provincia_matricula', 50)->nullable()->after('matricula_provincial');
            $table->string('consultorio_direccion')->nullable()->after('provincia_matricula');
            $table->string('consultorio_telefono', 20)->nullable()->after('consultorio_direccion');
            $table->string('cuil', 11)->nullable()->after('email');

            // Validaciones externas
            $table->boolean('validado_refeps')->default(false)->after('cuil');
            $table->timestamp('fecha_validacion_refeps')->nullable()->after('validado_refeps');

            // Firma electrónica
            $table->boolean('firma_electronica_habilitada')->default(false)->after('fecha_validacion_refeps');
            $table->string('firma_electronica_metodo', 20)->nullable()->after('firma_electronica_habilitada'); // 2fa/digital
            $table->text('firma_digital_certificado')->nullable()->after('firma_electronica_metodo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'matricula_nacional',
                'matricula_provincial',
                'provincia_matricula',
                'consultorio_direccion',
                'consultorio_telefono',
                'cuil',
                'validado_refeps',
                'fecha_validacion_refeps',
                'firma_electronica_habilitada',
                'firma_electronica_metodo',
                'firma_digital_certificado',
            ]);
        });
    }
};
