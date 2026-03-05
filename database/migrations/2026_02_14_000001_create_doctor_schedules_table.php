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
    Schema::create('doctor_schedules', function (Blueprint $table) {
      $table->id();
      $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
      $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
      $table->time('start_time');
      $table->time('end_time');
      $table->integer('slot_duration')->default(30)->comment('Duration in minutes');
      $table->boolean('is_active')->default(true);
      $table->timestamps();

      // Un doctor puede tener múltiples horarios por día (mañana y tarde)
      $table->index(['doctor_id', 'day_of_week', 'is_active']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('doctor_schedules');
  }
};
