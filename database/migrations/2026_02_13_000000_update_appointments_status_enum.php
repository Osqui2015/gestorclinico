<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    // First, update any existing 'confirmed' statuses to 'called'
    if (DB::table('appointments')->where('status', 'confirmed')->exists()) {
      DB::table('appointments')
        ->where('status', 'confirmed')
        ->update(['status' => 'called']);
    }

    // En MySQL para cambiar enum, hay que recrear la columna
    if (DB::getDriverName() === 'mysql') {
      DB::statement("ALTER TABLE appointments MODIFY status ENUM('pending', 'called', 'attending', 'completed', 'cancelled') DEFAULT 'pending'");
    } else {
      Schema::table('appointments', function (Blueprint $table) {
        $table->enum('status', ['pending', 'called', 'attending', 'completed', 'cancelled'])->change();
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    // Update 'called' back to 'confirmed'
    if (DB::table('appointments')->where('status', 'called')->exists()) {
      DB::table('appointments')
        ->where('status', 'called')
        ->update(['status' => 'confirmed']);
    }

    if (DB::getDriverName() === 'mysql') {
      DB::statement("ALTER TABLE appointments MODIFY status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending'");
    }
  }
};
