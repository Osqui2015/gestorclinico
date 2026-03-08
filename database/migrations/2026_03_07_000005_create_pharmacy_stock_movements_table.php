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
    Schema::create('pharmacy_stock_movements', function (Blueprint $table) {
      $table->id();
      $table->foreignId('pharmacy_item_id')->constrained()->onDelete('cascade');
      $table->enum('movement_type', ['entry', 'exit', 'adjustment', 'return', 'expired', 'damaged']);
      $table->integer('quantity'); // Positivo para entradas, negativo para salidas
      $table->integer('stock_before');
      $table->integer('stock_after');
      $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Usuario que realizó el movimiento
      $table->foreignId('pharmacy_request_id')->nullable()->constrained()->onDelete('set null');
      $table->string('reference')->nullable(); // Número de factura, orden, etc.
      $table->text('notes')->nullable();
      $table->timestamps();

      // Indices
      $table->index('movement_type');
      $table->index('created_at');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('pharmacy_stock_movements');
  }
};
