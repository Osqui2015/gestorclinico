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
    Schema::create('medical_equipments', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('code')->nullable()->unique();
      $table->enum('category', ['monitoring', 'imaging', 'life_support', 'laboratory', 'surgical', 'other'])->default('other');
      $table->string('brand')->nullable();
      $table->string('model')->nullable();
      $table->string('serial_number')->nullable();
      $table->string('location')->nullable();
      $table->enum('status', ['operational', 'maintenance_required', 'in_maintenance', 'out_of_service'])->default('operational');
      $table->timestamp('last_maintenance_at')->nullable();
      $table->timestamp('next_maintenance_at')->nullable();
      $table->text('notes')->nullable();
      $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
      $table->timestamps();
      $table->softDeletes();

      $table->index(['status', 'category']);
      $table->index('location');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('medical_equipments');
  }
};
