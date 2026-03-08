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
    Schema::create('ambulances', function (Blueprint $table) {
      $table->id();
      $table->string('internal_code')->unique();
      $table->string('plate_number')->nullable()->unique();
      $table->string('brand')->nullable();
      $table->string('model')->nullable();
      $table->unsignedInteger('year')->nullable();
      $table->unsignedBigInteger('current_mileage')->nullable();
      $table->string('base_location')->nullable();
      $table->enum('status', ['available', 'in_transfer', 'maintenance', 'out_of_service'])->default('available');
      $table->date('last_service_at')->nullable();
      $table->date('next_service_at')->nullable();
      $table->text('notes')->nullable();
      $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
      $table->timestamps();
      $table->softDeletes();

      $table->index(['status', 'base_location']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('ambulances');
  }
};
