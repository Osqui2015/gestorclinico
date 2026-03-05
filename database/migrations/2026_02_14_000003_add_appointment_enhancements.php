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
    Schema::table('appointments', function (Blueprint $table) {
      // Mejoras al sistema de turnos
      $table->integer('duration')->default(30)->after('scheduled_at')->comment('Duration in minutes');
      $table->boolean('is_walk_in')->default(false)->after('duration')->comment('Sobreturno');
      $table->boolean('confirmed')->default(false)->after('is_walk_in');
      $table->timestamp('confirmed_at')->nullable()->after('confirmed');
      $table->timestamp('checked_in_at')->nullable()->after('confirmed_at')->comment('Hora de llegada');
      $table->integer('no_show_count')->default(0)->after('checked_in_at')->comment('Contador de inasistencias');
      $table->text('cancellation_reason')->nullable()->after('notes');
      $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('appointments', function (Blueprint $table) {
      $table->dropColumn([
        'duration',
        'is_walk_in',
        'confirmed',
        'confirmed_at',
        'checked_in_at',
        'no_show_count',
        'cancellation_reason',
        'cancelled_at'
      ]);
    });
  }
};
