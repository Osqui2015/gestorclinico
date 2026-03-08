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
    Schema::create('rooms', function (Blueprint $table) {
      $table->id();
      $table->string('name'); // Nombre de la sala/habitación (ej: "Sala 301", "Habitación 204")
      $table->string('code', 20)->unique(); // Código de identificación (ej: "S301", "H204")
      $table->enum('room_type', [
        'standard',         // Habitación estándar
        'intensive_care',   // Terapia intensiva
        'isolation',        // Aislamiento
        'pediatric',        // Pediátrica
        'psychiatric',      // Psiquiátrica
        'recovery'          // Recuperación post-quirúrgica
      ])->default('standard');
      $table->integer('floor')->nullable(); // Piso/Planta
      $table->string('wing')->nullable(); // Ala/Sector (ej: "Norte", "Sur", "A", "B")
      $table->integer('max_beds')->default(1); // Capacidad máxima de camas
      $table->text('description')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();

      // Índices
      $table->index('room_type');
      $table->index(['floor', 'wing']);
      $table->index('is_active');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('rooms');
  }
};
