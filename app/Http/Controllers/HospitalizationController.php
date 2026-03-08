<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\BedCleaningLog;
use App\Models\Hospitalization;
use App\Models\Patient;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class HospitalizationController extends Controller
{
  /**
   * Return authenticated user as App\Models\User.
   */
  private function authUser(): User
  {
    /** @var User|null $user */
    $user = Auth::user();

    if (!$user instanceof User) {
      abort(401, 'Usuario no autenticado.');
    }

    return $user;
  }

  /**
   * Display a listing of beds with hospitalization status.
   */
  public function index(Request $request)
  {
    $query = Bed::with([
      'room',
      'currentHospitalization.patient',
      'currentHospitalization.doctor',
      'lastCleaning.cleaner'
    ])->active();

    // Filtro por estado de cama
    if ($request->filled('status')) {
      switch ($request->status) {
        case 'available':
          $query->available();
          break;
        case 'occupied':
          $query->occupied();
          break;
        case 'pending_cleaning':
          $query->pendingCleaning();
          break;
        case 'cleaning':
          $query->where('status', Bed::STATUS_CLEANING);
          break;
        case 'maintenance':
          $query->where('status', Bed::STATUS_MAINTENANCE);
          break;
      }
    }

    // Filtro por tipo de cama
    if ($request->filled('bed_type')) {
      $query->ofType($request->bed_type);
    }

    // Filtro por habitación
    if ($request->filled('room_id')) {
      $query->where('room_id', $request->room_id);
    }

    // Filtro por piso
    if ($request->filled('floor')) {
      $query->whereHas('room', function ($q) use ($request) {
        $q->where('floor', $request->floor);
      });
    }

    // Búsqueda por paciente (si hay internación activa)
    if ($request->filled('search')) {
      $search = $request->search;
      $query->whereHas('currentHospitalization.patient', function ($q) use ($search) {
        $q->where('first_name', 'like', "%{$search}%")
          ->orWhere('last_name', 'like', "%{$search}%")
          ->orWhere('dni', 'like', "%{$search}%");
      });
    }

    $beds = $query->orderBy('room_id')
      ->orderBy('bed_number')
      ->paginate(20)
      ->withQueryString();

    // Datos adicionales para filtros
    $rooms = Room::active()->orderBy('name')->get(['id', 'name', 'floor', 'wing']);
    $floors = Room::active()->distinct()->pluck('floor')->filter()->sort()->values();

    // Estadísticas
    $stats = [
      'total_beds' => Bed::active()->count(),
      'available' => Bed::available()->count(),
      'occupied' => Bed::occupied()->count(),
      'pending_cleaning' => Bed::pendingCleaning()->count(),
      'ready_for_discharge' => Hospitalization::readyForDischarge()->count(),
    ];

    return Inertia::render('Hospitalization/Index', [
      'beds' => $beds,
      'rooms' => $rooms,
      'floors' => $floors,
      'filters' => $request->only(['status', 'bed_type', 'room_id', 'floor', 'search']),
      'stats' => $stats,
    ]);
  }

  /**
   * Display the specified bed with hospitalization details and history.
   */
  public function show(Bed $bed)
  {
    $bed->load([
      'room',
      'currentHospitalization.patient',
      'currentHospitalization.doctor',
      'currentHospitalization.dischargeAuthorizer',
      'currentHospitalization.operation',
      'hospitalizations' => function ($query) {
        $query->orderByDesc('admission_date')->limit(10);
      },
      'hospitalizations.patient',
      'hospitalizations.doctor',
      'cleaningLogs' => function ($query) {
        $query->orderByDesc('completed_at')->limit(10);
      },
      'cleaningLogs.cleaner'
    ]);

    $user = $this->authUser();
    $canManage = $user->isAdmin() || $user->isNurse();

    return Inertia::render('Hospitalization/Show', [
      'bed' => $bed,
      'canManage' => $canManage,
    ]);
  }

  /**
   * Show the form for creating a new hospitalization.
   */
  public function create()
  {
    // Camas disponibles
    $availableBeds = Bed::with('room')
      ->available()
      ->orderBy('room_id')
      ->orderBy('bed_number')
      ->get();

    // Pacientes (para selector)
    $patients = Patient::orderBy('last_name')
      ->orderBy('first_name')
      ->get(['id', 'first_name', 'last_name', 'dni']);

    // Médicos activos
    $doctors = User::where('role', 'doctor')
      ->orderBy('name')
      ->get(['id', 'name', 'specialty']);

    return Inertia::render('Hospitalization/Create', [
      'availableBeds' => $availableBeds,
      'patients' => $patients,
      'doctors' => $doctors,
    ]);
  }

  /**
   * Store a newly created hospitalization.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'patient_id' => ['required', 'exists:patients,id'],
      'bed_id' => ['required', 'exists:beds,id'],
      'doctor_id' => ['required', 'exists:users,id'],
      'operation_id' => ['nullable', 'exists:operations,id'],
      'admission_date' => ['required', 'date'],
      'expected_discharge_date' => ['nullable', 'date', 'after:admission_date'],
      'admission_reason' => ['required', 'string', 'max:1000'],
      'admission_type' => ['required', Rule::in([
        Hospitalization::TYPE_EMERGENCY,
        Hospitalization::TYPE_SCHEDULED,
        Hospitalization::TYPE_POST_SURGICAL,
        Hospitalization::TYPE_TRANSFER
      ])],
      'diagnosis' => ['nullable', 'string', 'max:1000'],
      'treatment' => ['nullable', 'string', 'max:1000'],
    ]);

    $bed = Bed::find($validated['bed_id']);

    if (!$bed->isAvailable()) {
      return back()->withErrors(['bed_id' => 'La cama seleccionada no está disponible.']);
    }

    DB::beginTransaction();
    try {
      $hospitalization = Hospitalization::create(array_merge($validated, [
        'status' => Hospitalization::STATUS_ACTIVE,
      ]));

      // Marcar cama como ocupada
      $bed->markAsOccupied();

      DB::commit();

      return redirect()->route('hospitalizations.show', $bed)
        ->with('success', 'Internación registrada exitosamente.');
    } catch (\Exception $e) {
      DB::rollBack();
      return back()->withErrors(['error' => 'Error al registrar internación: ' . $e->getMessage()]);
    }
  }

  /**
   * Update expected discharge date (doctors only).
   */
  public function updateDischargeDate(Request $request, Hospitalization $hospitalization)
  {
    $user = $this->authUser();

    // Solo médicos o admin
    if (!$user->isDoctor() && !$user->isAdmin()) {
      abort(403, 'No tiene permiso para actualizar la fecha de alta.');
    }

    // Solo el médico responsable o admin pueden actualizar
    if (!$user->isAdmin() && $hospitalization->doctor_id !== $user->id) {
      abort(403, 'Solo el médico responsable puede actualizar la fecha de alta.');
    }

    $validated = $request->validate([
      'expected_discharge_date' => ['required', 'date', 'after_or_equal:today'],
      'discharge_notes' => ['nullable', 'string', 'max:1000'],
    ]);

    $hospitalization->updateExpectedDischargeDate(
      $validated['expected_discharge_date'],
      $user->id
    );

    if (isset($validated['discharge_notes'])) {
      $hospitalization->update(['discharge_notes' => $validated['discharge_notes']]);
    }

    return back()->with('success', 'Fecha de alta actualizada exitosamente.');
  }

  /**
   * Discharge a patient.
   */
  public function discharge(Request $request, Hospitalization $hospitalization)
  {
    $user = $this->authUser();

    if (!$user->isAdmin() && !$user->isNurse()) {
      abort(403, 'No tiene permiso para dar altas.');
    }

    if (!$hospitalization->isActive()) {
      return back()->withErrors(['error' => 'La internación no está activa.']);
    }

    $validated = $request->validate([
      'discharge_notes' => ['nullable', 'string', 'max:1000'],
    ]);

    DB::beginTransaction();
    try {
      $hospitalization->discharge($user->id, $validated['discharge_notes'] ?? null);

      DB::commit();

      return redirect()->route('hospitalizations.show', $hospitalization->bed)
        ->with('success', 'Alta registrada exitosamente. La cama está marcada como pendiente de limpieza.');
    } catch (\Exception $e) {
      DB::rollBack();
      return back()->withErrors(['error' => $e->getMessage()]);
    }
  }

  /**
   * Transfer patient to another bed.
   */
  public function transfer(Request $request, Hospitalization $hospitalization)
  {
    $user = $this->authUser();

    if (!$user->isAdmin() && !$user->isNurse()) {
      abort(403, 'No tiene permiso para transferir pacientes.');
    }

    $validated = $request->validate([
      'new_bed_id' => ['required', 'exists:beds,id'],
      'transfer_reason' => ['nullable', 'string', 'max:500'],
    ]);

    DB::beginTransaction();
    try {
      $newHospitalization = $hospitalization->transferToBed(
        $validated['new_bed_id'],
        $validated['transfer_reason'] ?? null
      );

      DB::commit();

      return redirect()->route('hospitalizations.show', $newHospitalization->bed)
        ->with('success', 'Paciente transferido exitosamente.');
    } catch (\Exception $e) {
      DB::rollBack();
      return back()->withErrors(['error' => $e->getMessage()]);
    }
  }

  /**
   * Mark bed as cleaned and available.
   */
  public function markCleaned(Request $request, Bed $bed)
  {
    $user = $this->authUser();

    if (!$user->isAdmin() && !$user->isNurse()) {
      abort(403, 'No tiene permiso para marcar camas como limpias.');
    }

    $validated = $request->validate([
      'cleaning_type' => ['required', Rule::in([
        BedCleaningLog::TYPE_ROUTINE,
        BedCleaningLog::TYPE_DEEP,
        BedCleaningLog::TYPE_DISCHARGE,
        BedCleaningLog::TYPE_DISINFECTION
      ])],
      'notes' => ['nullable', 'string', 'max:500'],
    ]);

    DB::beginTransaction();
    try {
      $bed->completeCleaning(
        $user->id,
        $validated['cleaning_type'],
        $validated['notes'] ?? null
      );

      DB::commit();

      return back()->with('success', 'Cama marcada como limpia y disponible.');
    } catch (\Exception $e) {
      DB::rollBack();
      return back()->withErrors(['error' => $e->getMessage()]);
    }
  }

  /**
   * Start cleaning process.
   */
  public function startCleaning(Bed $bed)
  {
    $user = $this->authUser();

    if (!$user->isAdmin() && !$user->isNurse()) {
      abort(403, 'No tiene permiso para iniciar limpieza de camas.');
    }

    if (!$bed->needsCleaning() && $bed->status !== Bed::STATUS_AVAILABLE) {
      return back()->withErrors(['error' => 'La cama no necesita limpieza o no está en estado correcto.']);
    }

    $bed->startCleaning();

    return back()->with('success', 'Limpieza de cama iniciada.');
  }

  /**
   * Display hospitalization history.
   */
  public function history(Request $request)
  {
    $query = Hospitalization::with([
      'patient',
      'bed.room',
      'doctor',
      'discharger'
    ]);

    // Filtros
    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    if ($request->filled('patient_id')) {
      $query->where('patient_id', $request->patient_id);
    }

    if ($request->filled('doctor_id')) {
      $query->where('doctor_id', $request->doctor_id);
    }

    if ($request->filled('from_date')) {
      $query->whereDate('admission_date', '>=', $request->from_date);
    }

    if ($request->filled('to_date')) {
      $query->whereDate('admission_date', '<=', $request->to_date);
    }

    $hospitalizations = $query->orderByDesc('admission_date')
      ->paginate(20)
      ->withQueryString();

    // Datos para filtros
    $patients = Patient::orderBy('last_name')->get(['id', 'first_name', 'last_name']);
    $doctors = User::where('role', 'doctor')->orderBy('name')->get(['id', 'name']);

    return Inertia::render('Hospitalization/History', [
      'hospitalizations' => $hospitalizations,
      'patients' => $patients,
      'doctors' => $doctors,
      'filters' => $request->only(['status', 'patient_id', 'doctor_id', 'from_date', 'to_date']),
    ]);
  }

  /**
   * Update daily observations.
   */
  public function updateObservations(Request $request, Hospitalization $hospitalization)
  {
    $user = $this->authUser();

    if (!$user->isAdmin() && !$user->isNurse() && $hospitalization->doctor_id !== $user->id) {
      abort(403, 'No tiene permiso para actualizar observaciones.');
    }

    $validated = $request->validate([
      'daily_observations' => ['required', 'string', 'max:2000'],
    ]);

    $hospitalization->update($validated);

    return back()->with('success', 'Observaciones actualizadas exitosamente.');
  }
}
