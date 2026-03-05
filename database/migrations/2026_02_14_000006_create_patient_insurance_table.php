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
    Schema::create('patient_insurance', function (Blueprint $table) {
      $table->id();
      $table->foreignId('patient_id')->constrained()->onDelete('cascade');
      $table->foreignId('health_insurance_id')->constrained()->onDelete('cascade');
      $table->string('member_number')->nullable();
      $table->date('valid_from')->nullable();
      $table->date('valid_until')->nullable();
      $table->boolean('is_primary')->default(true);
      $table->timestamps();

      $table->index(['patient_id', 'is_primary']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('patient_insurance');
  }
};
