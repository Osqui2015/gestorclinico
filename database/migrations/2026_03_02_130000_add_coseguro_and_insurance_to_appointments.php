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
      $table->foreignId('health_insurance_id')->nullable()->after('patient_id')->constrained('health_insurances')->onDelete('set null');
      $table->decimal('coseguro', 10, 2)->nullable()->after('health_insurance_id')->comment('Monto de coseguro a cobrar por la consulta');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('appointments', function (Blueprint $table) {
      $table->dropForeign(['health_insurance_id']);
      $table->dropColumn(['health_insurance_id', 'coseguro']);
    });
  }
};
