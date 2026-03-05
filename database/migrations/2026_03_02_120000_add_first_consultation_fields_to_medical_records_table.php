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
    Schema::table('medical_records', function (Blueprint $table) {
      $table->boolean('is_first_consultation')->default(false)->after('private_notes');
      $table->text('health_background')->nullable()->after('is_first_consultation');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('medical_records', function (Blueprint $table) {
      $table->dropColumn(['is_first_consultation', 'health_background']);
    });
  }
};
