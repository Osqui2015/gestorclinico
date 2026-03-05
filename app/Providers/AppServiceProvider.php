<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Models\MedicalRecord;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Prescription;
use App\Observers\AuditableObserver;
use App\Observers\PrescriptionObserver;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Register audit observer for critical models
        MedicalRecord::observe(AuditableObserver::class);
        Appointment::observe(AuditableObserver::class);
        Patient::observe(AuditableObserver::class);
        User::observe(AuditableObserver::class);

        // Register prescription observer for ReNaPDiS compliance and audit trail
        Prescription::observe(PrescriptionObserver::class);
    }
}
