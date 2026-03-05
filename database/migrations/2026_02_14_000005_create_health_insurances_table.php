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
    Schema::create('health_insurances', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('code')->unique()->nullable();
      $table->string('phone')->nullable();
      $table->string('email')->nullable();
      $table->decimal('copay_amount', 10, 2)->default(0)->comment('Copago fijo');
      $table->integer('copay_percentage')->default(0)->comment('Porcentaje de copago');
      $table->boolean('requires_authorization')->default(false);
      $table->boolean('is_active')->default(true);
      $table->text('notes')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('health_insurances');
  }
};
