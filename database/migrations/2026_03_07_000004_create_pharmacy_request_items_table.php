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
    Schema::create('pharmacy_request_items', function (Blueprint $table) {
      $table->id();
      $table->foreignId('pharmacy_request_id')->constrained()->onDelete('cascade');
      $table->foreignId('pharmacy_item_id')->constrained()->onDelete('cascade');
      $table->integer('quantity_requested');
      $table->integer('quantity_delivered')->default(0);
      $table->text('notes')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('pharmacy_request_items');
  }
};
