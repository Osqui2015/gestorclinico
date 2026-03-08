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
    Schema::create('emergency_evolutions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('emergency_admission_id')->constrained('emergency_admissions')->onDelete('cascade');
      $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');

      $table->timestamp('recorded_at');

      // Vital signs
      $table->decimal('systolic_pressure', 5, 2)->nullable();
      $table->decimal('diastolic_pressure', 5, 2)->nullable();
      $table->decimal('heart_rate', 5, 2)->nullable();
      $table->decimal('respiratory_rate', 5, 2)->nullable();
      $table->decimal('temperature', 5, 2)->nullable();
      $table->decimal('oxygen_saturation', 5, 2)->nullable();
      $table->decimal('glucose', 5, 2)->nullable();

      // Clinical notes
      $table->text('clinical_notes');
      $table->text('treatment_notes')->nullable();
      $table->text('medications_given')->nullable();

      // Tests performed
      $table->text('tests_performed')->nullable();

      $table->timestamps();

      // Índices
      $table->index('emergency_admission_id');
      $table->index('recorded_by');
      $table->index('recorded_at');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('emergency_evolutions');
  }
};
