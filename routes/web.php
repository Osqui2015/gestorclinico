<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\HealthInsuranceController;
use App\Http\Controllers\ReceptionController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ReportController;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureSecretary;
use App\Http\Middleware\EnsureDoctor;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Patients routes
    Route::resource('patients', PatientController::class);

    // Medical Records routes (nested under patients) - only for doctors/admins
    Route::middleware([EnsureRole::class])->group(function () {
        Route::resource('patients.medical-records', MedicalRecordController::class);
    });

    // Admin routes: audits, only for admins
    Route::middleware([EnsureRole::class, \App\Http\Middleware\EnsureAdmin::class])->prefix('admin')->name('admin.')->group(function () {
        Route::get('audits', [\App\Http\Controllers\AuditController::class, 'index'])->name('audits.index');
        Route::get('audits/{audit}', [\App\Http\Controllers\AuditController::class, 'show'])->name('audits.show');

        // Users (Doctors) management
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    });

    // Appointments routes
    Route::resource('appointments', AppointmentController::class);
    Route::get('/calendar', [AppointmentController::class, 'calendar'])->name('appointments.calendar');
    Route::get('/appointments/availability/{doctor_id}/{date}', [AppointmentController::class, 'getAvailability'])->name('appointments.availability');

    // Prescriptions routes
    Route::middleware([EnsureRole::class])->group(function () {
        Route::resource('prescriptions', PrescriptionController::class);
        Route::get('patients/{patientId}/prescriptions/create', [PrescriptionController::class, 'createForPatient'])->name('prescriptions.create-for-patient');
        Route::get('patients/{patientId}/medical-records/{medicalRecordId}/prescriptions/create', [PrescriptionController::class, 'createForMedicalRecord'])->name('prescriptions.create-for-medical-record');
        Route::get('appointments/{appointmentId}/prescriptions/create', [PrescriptionController::class, 'create'])->name('prescriptions.create-from-appointment');
        Route::get('prescriptions/{prescription}/generate-prescription-pdf', [PrescriptionController::class, 'generatePrescriptionPDF'])->name('prescriptions.generate-prescription-pdf');
        Route::get('prescriptions/{prescription}/generate-instructions-pdf', [PrescriptionController::class, 'generateInstructionsPDF'])->name('prescriptions.generate-instructions-pdf');
        Route::post('prescriptions/draft-pdf', [PrescriptionController::class, 'generateDraftPDF'])->name('prescriptions.draft-pdf');
        Route::get('prescriptions/temp/{token}', [PrescriptionController::class, 'serveTempPDF'])->name('prescriptions.temp');

        // ReNaPDiS - Rutas adicionales para receta digital
        Route::post('prescriptions/{prescription}/annul', [PrescriptionController::class, 'annul'])->name('prescriptions.annul');
    });

    // Public routes for prescription verification (for pharmacies)
    Route::post('prescriptions/verify-cuir', [PrescriptionController::class, 'verifyCUIR'])->name('prescriptions.verify-cuir');
    Route::post('prescriptions/{cuir}/dispense', [PrescriptionController::class, 'markAsDispensed'])->name('prescriptions.dispense');

    // Invoices routes
    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'addPayment'])->name('invoices.payments.store');
    Route::patch('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');

    // Health Insurances routes - admin and secretary
    Route::middleware([EnsureRole::class])->group(function () {
        Route::resource('health-insurances', HealthInsuranceController::class);
    });

    // Doctor Schedules routes
    Route::resource('doctor-schedules', DoctorScheduleController::class);
    Route::get('doctor-exceptions', [DoctorScheduleController::class, 'indexExceptions'])->name('doctor-exceptions.index');
    Route::post('doctor-exceptions', [DoctorScheduleController::class, 'storeException'])->name('doctor-exceptions.store');
    Route::delete('doctor-exceptions/{exception}', [DoctorScheduleController::class, 'destroyException'])->name('doctor-exceptions.destroy');

    // Reception/Secretary routes - only for secretaries and admins
    Route::middleware([EnsureRole::class])->prefix('reception')->name('reception.')->group(function () {
        Route::get('dashboard', [ReceptionController::class, 'dashboard'])->name('dashboard');
        Route::post('appointments/{appointment}/check-in', [ReceptionController::class, 'checkIn'])->name('appointments.check-in');
        Route::post('appointments/{appointment}/confirm', [ReceptionController::class, 'confirm'])->name('appointments.confirm');
        Route::get('search-patient', [ReceptionController::class, 'searchPatient'])->name('search-patient');
        Route::post('quick-register', [ReceptionController::class, 'quickRegister'])->name('quick-register');
        Route::get('waiting-room', [ReceptionController::class, 'waitingRoom'])->name('waiting-room');
        Route::get('doctor/{doctor}', [ReceptionController::class, 'byDoctor'])->name('by-doctor');
    });

    // Secretary routes - only for secretaries
    Route::middleware([EnsureSecretary::class])->prefix('secretary')->name('secretary.')->group(function () {
        // Turns management
        Route::get('turns', [\App\Http\Controllers\SecretaryController::class, 'indexTurns'])->name('turns.index');
        Route::get('turns/create', [\App\Http\Controllers\SecretaryController::class, 'createTurn'])->name('turns.create');
        Route::post('turns', [\App\Http\Controllers\SecretaryController::class, 'storeTurn'])->name('turns.store');
        Route::get('turns/{appointment}/edit', [\App\Http\Controllers\SecretaryController::class, 'editTurn'])->name('turns.edit');
        Route::patch('turns/{appointment}', [\App\Http\Controllers\SecretaryController::class, 'updateTurn'])->name('turns.update');
        Route::delete('turns/{appointment}', [\App\Http\Controllers\SecretaryController::class, 'destroyTurn'])->name('turns.destroy');

        // Patient creation from turns
        Route::get('patients/create', [\App\Http\Controllers\SecretaryController::class, 'createPatient'])->name('patients.create');
        Route::post('patients', [\App\Http\Controllers\SecretaryController::class, 'storePatient'])->name('patients.store');
    });

    // Doctor Queue/Board routes
    Route::middleware([EnsureDoctor::class])->prefix('doctor')->name('doctor.')->group(function () {
        Route::get('queue', [\App\Http\Controllers\QueueController::class, 'board'])->name('queue.board');
        Route::patch('appointments/{appointment}/call-next', [\App\Http\Controllers\QueueController::class, 'callNext'])->name('appointments.call-next');
        Route::patch('appointments/{appointment}/mark-called', [\App\Http\Controllers\QueueController::class, 'markCalled'])->name('appointments.mark-called');
        Route::patch('appointments/{appointment}/complete', [\App\Http\Controllers\QueueController::class, 'complete'])->name('appointments.complete');
        // Doctor reports and statistics
        Route::get('reports', [ReportController::class, 'doctorDashboard'])->name('reports');
    });

    // Admin - All queues/boards
    Route::middleware([\App\Http\Middleware\EnsureAdmin::class])->prefix('admin')->name('admin.')->group(function () {
        Route::get('queues', [\App\Http\Controllers\QueueController::class, 'allQueues'])->name('queues.index');
        // Admin reports and statistics
        Route::get('reports', [ReportController::class, 'adminDashboard'])->name('reports');
    });
});

require __DIR__ . '/auth.php';
