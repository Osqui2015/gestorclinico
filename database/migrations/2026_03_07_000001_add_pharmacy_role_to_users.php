<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    // Modify the role enum to include 'pharmacy'
    DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'doctor', 'secretary', 'pharmacy') DEFAULT 'doctor'");
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    // Remove pharmacy role
    DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'doctor', 'secretary') DEFAULT 'doctor'");
  }
};
