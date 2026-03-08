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
    Schema::create('operations', function (Blueprint $table) {
      $table->id();
      $table->foreignId('operation_room_id')->constrained('operation_rooms')->restrictOnDelete();
      $table->foreignId('doctor_id')->constrained('users')->restrictOnDelete();
      $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
      $table->string('operation_type');
      $table->dateTime('scheduled_start');
      $table->dateTime('scheduled_end');
      $table->unsignedInteger('estimated_duration_minutes');
      $table->unsignedInteger('cleaning_margin_minutes')->default(15);
      $table->enum('urgency', ['scheduled', 'urgent', 'emergency'])->default('scheduled');
      $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
      $table->text('clinical_notes')->nullable();
      $table->text('pharmacy_notes')->nullable();
      $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
      $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
      $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
      $table->timestamp('cancelled_at')->nullable();
      $table->text('cancellation_reason')->nullable();
      $table->timestamps();
      $table->softDeletes();

      $table->index(['operation_room_id', 'scheduled_start']);
      $table->index(['doctor_id', 'scheduled_start']);
      $table->index(['status', 'urgency']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('operations');
  }
};
