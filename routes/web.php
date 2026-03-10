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
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\PharmacyItemController;
use App\Http\Controllers\PharmacyRequestController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\OperationRoomController;
use App\Http\Controllers\PreAdmissionController;
use App\Http\Controllers\HospitalizationController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ParamedicController;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureSecretary;
use App\Http\Middleware\EnsureDoctor;
use App\Http\Middleware\EnsurePharmacy;
use App\Http\Middleware\EnsureOperationAccess;
use App\Http\Middleware\EnsureOperatingRoomManager;
use App\Http\Middleware\EnsurePreAdmissionAccess;
use App\Http\Middleware\EnsureHospitalizationAccess;
use App\Http\Middleware\EnsureEmergency;
use App\Http\Middleware\EnsureAccountant;
use App\Http\Middleware\EnsureMaintenance;
use App\Http\Middleware\EnsureParamedic;
use App\Http\Middleware\EnsureSecretaryOrAdmin;
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
    Route::middleware([EnsureSecretaryOrAdmin::class])->group(function () {
        Route::resource('health-insurances', HealthInsuranceController::class);
    });

    // Doctor Schedules routes
    Route::resource('doctor-schedules', DoctorScheduleController::class);
    Route::get('doctor-exceptions', [DoctorScheduleController::class, 'indexExceptions'])->name('doctor-exceptions.index');
    Route::post('doctor-exceptions', [DoctorScheduleController::class, 'storeException'])->name('doctor-exceptions.store');
    Route::delete('doctor-exceptions/{exception}', [DoctorScheduleController::class, 'destroyException'])->name('doctor-exceptions.destroy');

    // Reception/Secretary routes - only for secretaries and admins
    Route::middleware([EnsureSecretaryOrAdmin::class])->prefix('reception')->name('reception.')->group(function () {
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

    // Pharmacy routes - only for pharmacy staff and admins
    Route::middleware([EnsurePharmacy::class])->prefix('pharmacy')->name('pharmacy.')->group(function () {
        Route::get('dashboard', [PharmacyController::class, 'index'])->name('dashboard');

        // Pharmacy Items management
        Route::resource('items', PharmacyItemController::class);
        Route::post('items/{item}/adjust-stock', [PharmacyItemController::class, 'adjustStock'])->name('items.adjust-stock');
        Route::post('items/{item}/update-sterilization', [PharmacyItemController::class, 'updateSterilization'])->name('items.update-sterilization');

        // Pharmacy Requests management
        Route::resource('requests', PharmacyRequestController::class)->except(['create', 'store']);
        Route::post('requests/{request}/process', [PharmacyRequestController::class, 'process'])->name('requests.process');
        Route::post('requests/{request}/deliver', [PharmacyRequestController::class, 'deliver'])->name('requests.deliver');
        Route::post('requests/{request}/cancel', [PharmacyRequestController::class, 'cancel'])->name('requests.cancel');
    });

    // Doctor/Admin pharmacy requests routes
    Route::middleware([EnsureRole::class])->prefix('pharmacy-requests')->name('pharmacy-requests.')->group(function () {
        Route::get('/', [PharmacyRequestController::class, 'index'])->name('index');
        Route::get('/my-requests', [PharmacyRequestController::class, 'myRequests'])->name('my-requests');
        Route::get('/create', [PharmacyRequestController::class, 'create'])->name('create');
        Route::post('/', [PharmacyRequestController::class, 'store'])->name('store');
        Route::get('/{request}', [PharmacyRequestController::class, 'show'])->name('show');
    });

    // Operating rooms and operations module (doctor + admin + operating room manager)
    Route::middleware([EnsureOperationAccess::class])->prefix('operations')->name('operations.')->group(function () {
        Route::get('/', [OperationController::class, 'index'])->name('index');
        Route::get('/create', [OperationController::class, 'create'])->name('create');
        Route::post('/', [OperationController::class, 'store'])->name('store');
        Route::get('/{operation}/edit', [OperationController::class, 'edit'])->name('edit');
        Route::patch('/{operation}', [OperationController::class, 'update'])->name('update');
        Route::delete('/{operation}', [OperationController::class, 'destroy'])->name('destroy');
        Route::post('/{operation}/cancel', [OperationController::class, 'cancel'])->name('cancel');

        // Room management (operating room manager + admin)
        Route::middleware([EnsureOperatingRoomManager::class])->group(function () {
            Route::post('/{operation}/quick-status', [OperationController::class, 'quickStatus'])->name('quick-status');
            Route::get('/rooms/settings', [OperationRoomController::class, 'settings'])->name('rooms.settings');
            Route::patch('/rooms/{room}', [OperationRoomController::class, 'update'])->name('rooms.update');
        });

        // Capacity setup only by admin
        Route::middleware([\App\Http\Middleware\EnsureAdmin::class])->post('/rooms/capacity', [OperationRoomController::class, 'updateCapacity'])->name('rooms.capacity');
    });

    // Pre-admission module (secretary + admin)
    Route::middleware([EnsurePreAdmissionAccess::class])->prefix('pre-admissions')->name('pre-admissions.')->group(function () {
        Route::get('/', [PreAdmissionController::class, 'index'])->name('index');
        Route::get('/{preAdmission}', [PreAdmissionController::class, 'show'])->name('show');
        Route::post('/{preAdmission}/assign', [PreAdmissionController::class, 'assign'])->name('assign');
        Route::post('/{preAdmission}/verify-data', [PreAdmissionController::class, 'verifyData'])->name('verify-data');
        Route::post('/{preAdmission}/upload-document', [PreAdmissionController::class, 'uploadDocument'])->name('upload-document');
        Route::post('/{preAdmission}/verify-document', [PreAdmissionController::class, 'verifyDocument'])->name('verify-document');
        Route::post('/{preAdmission}/reject-document', [PreAdmissionController::class, 'rejectDocument'])->name('reject-document');
        Route::post('/{preAdmission}/confirm', [PreAdmissionController::class, 'confirm'])->name('confirm');
        Route::post('/{preAdmission}/cancel', [PreAdmissionController::class, 'cancel'])->name('cancel');
    });

    // Hospitalization module (nurse + doctor + admin)
    Route::middleware([EnsureHospitalizationAccess::class])->prefix('hospitalizations')->name('hospitalizations.')->group(function () {
        Route::get('/', [HospitalizationController::class, 'index'])->name('index');
        Route::get('/create', [HospitalizationController::class, 'create'])->name('create');
        Route::post('/', [HospitalizationController::class, 'store'])->name('store');
        Route::get('/history', [HospitalizationController::class, 'history'])->name('history');
        Route::get('/{bed}', [HospitalizationController::class, 'show'])->name('show');

        // Hospitalization management
        Route::post('/{hospitalization}/update-discharge-date', [HospitalizationController::class, 'updateDischargeDate'])->name('update-discharge-date');
        Route::post('/{hospitalization}/discharge', [HospitalizationController::class, 'discharge'])->name('discharge');
        Route::post('/{hospitalization}/transfer', [HospitalizationController::class, 'transfer'])->name('transfer');
        Route::post('/{hospitalization}/update-observations', [HospitalizationController::class, 'updateObservations'])->name('update-observations');

        // Bed cleaning
        Route::post('/beds/{bed}/start-cleaning', [HospitalizationController::class, 'startCleaning'])->name('beds.start-cleaning');
        Route::post('/beds/{bed}/mark-cleaned', [HospitalizationController::class, 'markCleaned'])->name('beds.mark-cleaned');
    });

    // Room & Beds configuration (admin)
    Route::middleware([\App\Http\Middleware\EnsureAdmin::class])->prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/settings', [\App\Http\Controllers\RoomController::class, 'settings'])->name('settings');
        Route::post('/', [\App\Http\Controllers\RoomController::class, 'store'])->name('store');
        Route::patch('/{room}', [\App\Http\Controllers\RoomController::class, 'update'])->name('update');
        Route::delete('/{room}', [\App\Http\Controllers\RoomController::class, 'destroy'])->name('destroy');
    });

    // Emergency/Guardia module (emergency + doctor + nurse + admin)
    Route::middleware([EnsureEmergency::class])->prefix('emergency')->name('emergency.')->group(function () {
        Route::get('/', [\App\Http\Controllers\EmergencyController::class, 'board'])->name('board');
        Route::get('/create', [\App\Http\Controllers\EmergencyController::class, 'create'])->name('create');
        Route::get('/history', [\App\Http\Controllers\EmergencyController::class, 'history'])->name('history');
        Route::post('/', [\App\Http\Controllers\EmergencyController::class, 'store'])->name('store');
        Route::get('/{admission}', [\App\Http\Controllers\EmergencyController::class, 'show'])->name('show');
        Route::get('/{admission}/edit', [\App\Http\Controllers\EmergencyController::class, 'edit'])->name('edit');
        Route::get('/{admission}/evolution', [\App\Http\Controllers\EmergencyController::class, 'evolution'])->name('evolution');
        Route::patch('/{admission}', [\App\Http\Controllers\EmergencyController::class, 'update'])->name('update');
        Route::post('/{admission}/evolution', [\App\Http\Controllers\EmergencyController::class, 'recordEvolution'])->name('record-evolution');
        Route::patch('/{admission}/status', [\App\Http\Controllers\EmergencyController::class, 'changeStatus'])->name('change-status');
        Route::post('/{admission}/prescription', [\App\Http\Controllers\EmergencyController::class, 'createPrescription'])->name('create-prescription');
        Route::post('/{admission}/pharmacy', [\App\Http\Controllers\EmergencyController::class, 'requestPharmacy'])->name('request-pharmacy');
        Route::post('/{admission}/admit', [\App\Http\Controllers\EmergencyController::class, 'admitToHospital'])->name('admit-hospital');
    });

    // Accounting/Cuentas Corrientes module (accountant + secretary + admin)
    Route::middleware([EnsureAccountant::class])->prefix('accounting')->name('accounting.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AccountingController::class, 'dashboard'])->name('dashboard');
        Route::get('/accounts', [\App\Http\Controllers\AccountingController::class, 'index'])->name('index');
        Route::get('/accounts/{account}', [\App\Http\Controllers\AccountingController::class, 'show'])->name('show');
        Route::get('/accounts/{account}/charge', [\App\Http\Controllers\AccountingController::class, 'chargeForm'])->name('charge-form');
        Route::post('/accounts/{account}/charge', [\App\Http\Controllers\AccountingController::class, 'recordCharge'])->name('record-charge');
        Route::get('/accounts/{account}/payment', [\App\Http\Controllers\AccountingController::class, 'paymentForm'])->name('payment-form');
        Route::post('/accounts/{account}/payment', [\App\Http\Controllers\AccountingController::class, 'recordPayment'])->name('record-payment');
        Route::post('/accounts/{account}/credit', [\App\Http\Controllers\AccountingController::class, 'grantCredit'])->name('grant-credit');
        Route::post('/accounts/{account}/write-off', [\App\Http\Controllers\AccountingController::class, 'writeOff'])->name('write-off');
        Route::get('/debtors', [\App\Http\Controllers\AccountingController::class, 'debtors'])->name('debtors');
        Route::get('/accounts/{account}/export', [\App\Http\Controllers\AccountingController::class, 'exportTransactions'])->name('export');
    });

    // Maintenance module (maintenance + admin)
    Route::middleware([EnsureMaintenance::class])->prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/', [MaintenanceController::class, 'index'])->name('index');
        Route::post('/equipments', [MaintenanceController::class, 'storeEquipment'])->name('equipments.store');
        Route::patch('/equipments/{equipment}/status', [MaintenanceController::class, 'updateEquipmentStatus'])->name('equipments.status');
        Route::post('/orders', [MaintenanceController::class, 'storeOrder'])->name('orders.store');
        Route::patch('/orders/{order}/status', [MaintenanceController::class, 'updateOrderStatus'])->name('orders.status');
    });

    // Paramedic module (paramedic + emergency + admin)
    Route::middleware([EnsureParamedic::class])->prefix('paramedic')->name('paramedic.')->group(function () {
        Route::get('/', [ParamedicController::class, 'dashboard'])->name('dashboard');
        Route::post('/ambulances', [ParamedicController::class, 'storeAmbulance'])->name('ambulances.store');
        Route::patch('/ambulances/{ambulance}/status', [ParamedicController::class, 'updateAmbulanceStatus'])->name('ambulances.status');
        Route::post('/transfers', [ParamedicController::class, 'storeTransfer'])->name('transfers.store');
        Route::patch('/transfers/{transfer}/status', [ParamedicController::class, 'updateTransferStatus'])->name('transfers.status');
    });

    // Advanced Reports module (admin + accountant + secretary)
    Route::middleware([\App\Http\Middleware\EnsureAdmin::class])->prefix('advanced-reports')->name('advanced-reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AdvancedReportController::class, 'index'])->name('index');
        Route::get('/c2-report', [\App\Http\Controllers\AdvancedReportController::class, 'c2Report'])->name('c2');
        Route::get('/epidemiology', [\App\Http\Controllers\AdvancedReportController::class, 'epidemiologyReport'])->name('epidemiology');
        Route::get('/quality-indicators', [\App\Http\Controllers\AdvancedReportController::class, 'qualityIndicators'])->name('quality-indicators');
        Route::get('/insurance-report', [\App\Http\Controllers\AdvancedReportController::class, 'insuranceReport'])->name('insurance');
        Route::get('/billing-analysis', [\App\Http\Controllers\AdvancedReportController::class, 'billingAnalysis'])->name('billing');
        Route::get('/bed-occupancy', [\App\Http\Controllers\AdvancedReportController::class, 'bedOccupancyReport'])->name('bed-occupancy');
    });
});

require __DIR__ . '/auth.php';
