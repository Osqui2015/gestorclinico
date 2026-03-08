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
    Schema::create('maintenance_orders', function (Blueprint $table) {
      $table->id();
      $table->foreignId('medical_equipment_id')->constrained('medical_equipments')->cascadeOnDelete();
      $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
      $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
      $table->string('title');
      $table->text('description')->nullable();
      $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
      $table->enum('status', ['open', 'in_progress', 'on_hold', 'completed', 'cancelled'])->default('open');
      $table->timestamp('reported_at');
      $table->timestamp('started_at')->nullable();
      $table->timestamp('completed_at')->nullable();
      $table->text('resolution_notes')->nullable();
      $table->decimal('cost', 12, 2)->nullable();
      $table->timestamps();
      $table->softDeletes();

      $table->index(['status', 'priority']);
      $table->index('reported_at');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('maintenance_orders');
  }
};
