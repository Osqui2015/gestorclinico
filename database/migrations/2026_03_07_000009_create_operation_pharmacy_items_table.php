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
    Schema::create('operation_pharmacy_items', function (Blueprint $table) {
      $table->id();
      $table->foreignId('operation_id')->constrained('operations')->cascadeOnDelete();
      $table->foreignId('pharmacy_item_id')->nullable()->constrained('pharmacy_items')->nullOnDelete();
      $table->string('requested_item_name')->nullable();
      $table->unsignedInteger('quantity_required')->default(1);
      $table->string('unit_measurement')->nullable();
      $table->boolean('picked_up')->default(false);
      $table->timestamp('picked_up_at')->nullable();
      $table->text('notes')->nullable();
      $table->timestamps();

      $table->index(['operation_id', 'picked_up']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('operation_pharmacy_items');
  }
};
