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
    Schema::create('pre_admissions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('operation_id')->constrained('operations')->onDelete('cascade');
      $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
      $table->foreignId('secretary_id')->nullable()->constrained('users')->onDelete('set null');
      $table->enum('status', [
        'pending_assignment',
        'data_pending',
        'documents_pending',
        'ready_for_surgery',
        'cancelled'
      ])->default('pending_assignment');

      // Patient verification data
      $table->string('urgent_number')->nullable()->comment('Número de urgencia/historia');
      $table->string('contact_phone')->nullable();
      $table->string('emergency_contact_name')->nullable();
      $table->string('emergency_contact_phone')->nullable();
      $table->enum('medical_history_verified', ['yes', 'no', 'pending'])->default('pending');
      $table->text('patient_observations')->nullable();

      // Timestamps for verification steps
      $table->timestamp('data_verified_at')->nullable();
      $table->timestamp('documentation_verified_at')->nullable();
      $table->timestamp('ready_for_surgery_at')->nullable();
      $table->timestamp('cancelled_at')->nullable();
      $table->text('cancellation_reason')->nullable();

      $table->timestamps();
      $table->softDeletes();

      $table->index('operation_id');
      $table->index('patient_id');
      $table->index('secretary_id');
      $table->index('status');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('pre_admissions');
  }
};
