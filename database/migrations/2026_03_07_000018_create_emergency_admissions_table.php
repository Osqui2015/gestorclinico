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
        Schema::create('emergency_admissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('attending_doctor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('nurse_id')->nullable()->constrained('users')->onDelete('set null');

            // Timestamps
            $table->timestamp('admission_time');
            $table->timestamp('triage_time')->nullable();
            $table->timestamp('discharged_at')->nullable();

            // Triage information
            $table->enum('triage_level', ['1', '2', '3', '4', '5'])->nullable();
            $table->string('chief_complaint')->nullable(); // Motivo de consulta
            $table->text('triage_notes')->nullable();

            // Vital signs at admission
            $table->decimal('systolic_pressure', 5, 2)->nullable();
            $table->decimal('diastolic_pressure', 5, 2)->nullable();
            $table->decimal('heart_rate', 5, 2)->nullable();
            $table->decimal('respiratory_rate', 5, 2)->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('oxygen_saturation', 5, 2)->nullable();
            $table->decimal('glucose', 5, 2)->nullable();
            $table->string('consciousness_level')->nullable(); // GCS score o similar

            // Status in emergency
            $table->enum('status', [
                'waiting',           // Esperando ser atendido
                'in_care',          // Siendo atendido
                'observation',      // En observación
                'discharged',       // Dado de alta
                'admitted',         // Internado
                'transferred'       // Trasladado a otro centro
            ])->default('waiting');

            // Diagnosis and treatment
            $table->text('preliminary_diagnosis')->nullable();
            $table->text('treatment_given')->nullable();
            $table->text('discharge_diagnosis')->nullable();
            $table->text('discharge_instructions')->nullable();

            // Observations
            $table->text('observations')->nullable();
            $table->text('clinical_evolution')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('patient_id');
            $table->index('attending_doctor_id');
            $table->index('status');
            $table->index('triage_level');
            $table->index('admission_time');
            $table->index(['patient_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_admissions');
    }
};
