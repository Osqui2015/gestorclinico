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
        Schema::create('hospitalizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('bed_id')->constrained('beds')->onDelete('cascade');
            $table->foreignId('operation_id')->nullable()->constrained('operations')->onDelete('set null');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            
            // Fechas
            $table->timestamp('admission_date'); // Fecha de ingreso
            $table->date('expected_discharge_date')->nullable(); // Fecha estimada de alta (por médico)
            $table->timestamp('actual_discharge_date')->nullable(); // Fecha real de alta
            
            // Información de la internación
            $table->text('admission_reason'); // Motivo de internación
            $table->enum('admission_type', [
                'emergency',        // Emergencia
                'scheduled',        // Programada
                'post_surgical',    // Post-quirúrgica
                'transfer'          // Transferencia
            ])->default('scheduled');
            
            $table->enum('status', [
                'active',           // Internado actualmente
                'discharged',       // Alta médica
                'transferred',      // Transferido a otra cama
                'deceased'          // Fallecido
            ])->default('active');
            
            // Notas de alta
            $table->text('discharge_notes')->nullable();
            $table->foreignId('discharge_authorized_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('discharged_by')->nullable()->constrained('users')->onDelete('set null'); // Usuario que ejecutó el alta
            
            // Información clínica
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->text('daily_observations')->nullable(); // Observaciones diarias del personal
            
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('status');
            $table->index('admission_date');
            $table->index(['patient_id', 'status']);
            $table->index(['doctor_id', 'status']);
            $table->index(['bed_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospitalizations');
    }
};
