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
    Schema::create('emergency_transfers', function (Blueprint $table) {
      $table->id();
      $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
      $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
      $table->foreignId('ambulance_id')->nullable()->constrained('ambulances')->nullOnDelete();
      $table->string('origin');
      $table->string('destination');
      $table->enum('transfer_type', ['emergency', 'scheduled', 'interhospital', 'discharge'])->default('emergency');
      $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
      $table->enum('status', ['requested', 'assigned', 'in_progress', 'completed', 'cancelled'])->default('requested');
      $table->timestamp('requested_at');
      $table->timestamp('assigned_at')->nullable();
      $table->timestamp('departed_at')->nullable();
      $table->timestamp('arrived_at')->nullable();
      $table->text('clinical_summary')->nullable();
      $table->text('crew_notes')->nullable();
      $table->timestamps();
      $table->softDeletes();

      $table->index(['status', 'priority']);
      $table->index('requested_at');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('emergency_transfers');
  }
};
