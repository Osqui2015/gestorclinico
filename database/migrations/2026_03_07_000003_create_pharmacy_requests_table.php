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
    Schema::create('pharmacy_requests', function (Blueprint $table) {
      $table->id();
      $table->foreignId('requested_by')->constrained('users')->onDelete('cascade'); // Médico que solicita
      $table->foreignId('patient_id')->nullable()->constrained('patients')->onDelete('set null'); // Paciente relacionado
      $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
      $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null'); // Farmacéutico
      $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
      $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending');
      $table->text('notes')->nullable();
      $table->text('pharmacy_notes')->nullable(); // Notas del farmacéutico
      $table->timestamp('requested_at')->useCurrent();
      $table->timestamp('processed_at')->nullable();
      $table->timestamp('completed_at')->nullable();
      $table->timestamps();
      $table->softDeletes();

      // Indices
      $table->index('status');
      $table->index('priority');
      $table->index('requested_at');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('pharmacy_requests');
  }
};
