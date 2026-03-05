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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'license_number')) {
                $table->string('license_number')->nullable()->comment('Número de matrícula/licencia profesional');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->comment('Teléfono del consultorio');
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable()->comment('Dirección del consultorio');
            }
            if (!Schema::hasColumn('users', 'professional_id')) {
                $table->string('professional_id')->nullable()->comment('Número de colegio o afiliación profesional');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['license_number', 'phone', 'address', 'professional_id']);
        });
    }
};
