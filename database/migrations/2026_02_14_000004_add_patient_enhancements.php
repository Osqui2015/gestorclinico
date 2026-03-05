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
    Schema::table('patients', function (Blueprint $table) {
      // Información adicional para clínica
      $table->string('address')->nullable()->after('email');
      $table->string('city')->nullable()->after('address');
      $table->string('zip_code')->nullable()->after('city');
      $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable()->after('birth_date');
      $table->string('emergency_contact_name')->nullable()->after('zip_code');
      $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
      $table->text('allergies')->nullable()->after('emergency_contact_phone')->comment('Alergias separadas por coma');
      $table->text('notes')->nullable()->after('allergies')->comment('Notas generales del paciente');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('patients', function (Blueprint $table) {
      $table->dropColumn([
        'address',
        'city',
        'zip_code',
        'gender',
        'emergency_contact_name',
        'emergency_contact_phone',
        'allergies',
        'notes'
      ]);
    });
  }
};
