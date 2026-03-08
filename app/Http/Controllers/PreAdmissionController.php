<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\PreAdmission;
use App\Models\RequiredDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PreAdmissionController extends Controller
{
  /**
   * Display listing of pre-admissions
   */
  public function index(Request $request)
  {
    $user = $request->user();
    $query = PreAdmission::with('operation', 'operation.room', 'patient', 'secretary', 'documents.requiredDocument');

    // Filter by secretary
    if ($user->isSecretary()) {
      $query->where(function ($secretaryQuery) use ($user) {
        $secretaryQuery
          ->where('secretary_id', $user->id)
          ->orWhereNull('secretary_id');
      });
    } elseif (!$user->isAdmin()) {
      abort(403, 'No autorizado');
    }

    // Filter by status
    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    // Filter by urgency
    if ($request->filled('urgent')) {
      $query->whereHas('operation', fn($q) => $q->where('urgency', $request->urgent));
    }

    // Search by patient or urgent number
    if ($request->filled('search')) {
      $search = '%' . $request->search . '%';
      $query->where(function ($q) use ($search) {
        $q->where('urgent_number', 'like', $search)
          ->orWhereHas(
            'patient',
            fn($pq) => $pq
              ->where('first_name', 'like', $search)
              ->orWhere('last_name', 'like', $search)
          );
      });
    }

    $preAdmissions = $query
      ->orderByDesc('created_at')
      ->paginate(15);

    $secretaries = $user->isAdmin()
      ? User::query()
      ->where('role', 'secretary')
      ->orderBy('name')
      ->get(['id', 'name'])
      : [];

    return Inertia::render('PreAdmission/Index', [
      'preAdmissions' => $preAdmissions,
      'secretaries' => $secretaries,
      'filters' => [
        'status' => $request->status,
        'urgent' => $request->urgent,
        'search' => $request->search,
      ],
      'permissions' => [
        'canAssign' => $user->isAdmin(),
        'canVerify' => $user->isSecretary() || $user->isAdmin(),
      ],
    ]);
  }

  /**
   * Display pre-admission details and verification form
   */
  public function show(Request $request, PreAdmission $preAdmission)
  {
    $user = $request->user();

    // Authorization: only secretary or admin
    if ($user->isSecretary() && $preAdmission->secretary_id !== $user->id && !is_null($preAdmission->secretary_id)) {
      abort(403, 'No autorizado');
    }
    if (!($user->isSecretary() || $user->isAdmin())) {
      abort(403, 'No autorizado');
    }

    $preAdmission->load([
      'operation.room',
      'operation.doctor',
      'patient',
      'secretary',
      'documents.requiredDocument',
    ]);

    $requiredDocuments = RequiredDocument::where('status', 'active')
      ->get()
      ->map(fn($doc) => [
        'id' => $doc->id,
        'name' => $doc->name,
        'code' => $doc->code,
        'description' => $doc->description,
        'is_mandatory' => $doc->is_mandatory,
        'requires_upload' => $doc->requires_upload,
        'preAdmissionDocument' => $preAdmission->documents
          ->firstWhere('required_document_id', $doc->id),
      ]);

    return Inertia::render('PreAdmission/Show', [
      'preAdmission' => [
        'id' => $preAdmission->id,
        'operation_id' => $preAdmission->operation_id,
        'patient_id' => $preAdmission->patient_id,
        'secretary_id' => $preAdmission->secretary_id,
        'status' => $preAdmission->status,
        'urgent_number' => $preAdmission->urgent_number,
        'contact_phone' => $preAdmission->contact_phone,
        'emergency_contact_name' => $preAdmission->emergency_contact_name,
        'emergency_contact_phone' => $preAdmission->emergency_contact_phone,
        'medical_history_verified' => $preAdmission->medical_history_verified,
        'patient_observations' => $preAdmission->patient_observations,
        'data_verified_at' => $preAdmission->data_verified_at,
        'documentation_verified_at' => $preAdmission->documentation_verified_at,
        'ready_for_surgery_at' => $preAdmission->ready_for_surgery_at,
        'operation' => [
          'id' => $preAdmission->operation->id,
          'operation_type' => $preAdmission->operation->operation_type,
          'scheduled_start' => $preAdmission->operation->scheduled_start,
          'urgency' => $preAdmission->operation->urgency,
          'status' => $preAdmission->operation->status,
          'room' => [
            'id' => $preAdmission->operation->room->id,
            'name' => $preAdmission->operation->room->name,
            'code' => $preAdmission->operation->room->code,
          ],
          'doctor' => [
            'id' => $preAdmission->operation->doctor->id,
            'name' => $preAdmission->operation->doctor->name,
          ],
        ],
        'patient' => [
          'id' => $preAdmission->patient->id,
          'first_name' => $preAdmission->patient->first_name,
          'last_name' => $preAdmission->patient->last_name,
          'dni' => $preAdmission->patient->dni,
          'phone' => $preAdmission->patient->phone,
          'email' => $preAdmission->patient->email,
          'emergency_contact_name' => $preAdmission->patient->emergency_contact_name,
          'emergency_contact_phone' => $preAdmission->patient->emergency_contact_phone,
          'allergies' => $preAdmission->patient->allergies,
        ],
        'secretary' => $preAdmission->secretary ? [
          'id' => $preAdmission->secretary->id,
          'name' => $preAdmission->secretary->name,
        ] : null,
      ],
      'requiredDocuments' => $requiredDocuments,
      'canConfirm' => $preAdmission->canConfirmForSurgery(),
      'permissions' => [
        'canVerify' => $preAdmission->secretary_id === $user->id || $user->isAdmin(),
      ],
    ]);
  }

  /**
   * Assign secretary to pre-admission
   */
  public function assign(Request $request, PreAdmission $preAdmission)
  {
    if (!$request->user()->isAdmin()) {
      abort(403, 'No autorizado');
    }

    $request->validate([
      'secretary_id' => [
        'required',
        Rule::exists('users', 'id')->where(fn($query) => $query->where('role', 'secretary')),
      ],
    ]);

    if ($preAdmission->secretary_id !== null) {
      return back()->withErrors(['error' => 'Ya está asignada a una secretaria']);
    }

    $preAdmission->update([
      'secretary_id' => $request->secretary_id,
      'status' => PreAdmission::STATUS_DATA_PENDING,
    ]);

    return back()->with('success', 'Secretaria asignada exitosamente');
  }

  /**
   * Verify patient data and update pre-admission info
   */
  public function verifyData(Request $request, PreAdmission $preAdmission)
  {
    $request->validate([
      'urgent_number' => 'required|string|max:50',
      'contact_phone' => 'required|string|max:20',
      'emergency_contact_name' => 'required|string|max:100',
      'emergency_contact_phone' => 'required|string|max:20',
      'medical_history_verified' => 'required|in:yes,no,pending',
      'patient_observations' => 'nullable|string',
    ]);

    $this->authorizeSecretary($preAdmission, $request->user());

    $preAdmission->update([
      'urgent_number' => $request->urgent_number,
      'contact_phone' => $request->contact_phone,
      'emergency_contact_name' => $request->emergency_contact_name,
      'emergency_contact_phone' => $request->emergency_contact_phone,
      'medical_history_verified' => $request->medical_history_verified,
      'patient_observations' => $request->patient_observations,
      'data_verified_at' => now(),
      'status' => PreAdmission::STATUS_DOCUMENTS_PENDING,
    ]);

    return back()->with('success', 'Datos del paciente verificados');
  }

  /**
   * Upload a document
   */
  public function uploadDocument(Request $request, PreAdmission $preAdmission)
  {
    $request->validate([
      'required_document_id' => 'required|exists:required_documents,id',
      'file' => 'required|file|max:10240', // 10MB max
    ]);

    $this->authorizeSecretary($preAdmission, $request->user());

    $requiredDoc = RequiredDocument::findOrFail($request->required_document_id);
    $file = $request->file('file');

    // Store file
    $path = $file->store("pre-admissions/{$preAdmission->id}", 'public');

    $preAdmissionDoc = $preAdmission->documents()
      ->firstOrCreate(
        ['required_document_id' => $requiredDoc->id],
        ['status' => 'pending']
      );

    $preAdmissionDoc->update([
      'status' => 'uploaded',
      'file_path' => $path,
      'original_filename' => $file->getClientOriginalName(),
      'file_size' => $file->getSize(),
      'uploaded_at' => now(),
    ]);

    return back()->with('success', 'Documento subido exitosamente');
  }

  /**
   * Verify document
   */
  public function verifyDocument(Request $request, PreAdmission $preAdmission)
  {
    $request->validate([
      'pre_admission_document_id' => 'required|exists:pre_admission_documents,id',
      'verification_notes' => 'nullable|string',
    ]);

    $this->authorizeSecretary($preAdmission, $request->user());

    $padDoc = $preAdmission->documents()
      ->findOrFail($request->pre_admission_document_id);

    $padDoc->verify($request->verification_notes);

    return back()->with('success', 'Documento verificado');
  }

  /**
   * Reject document
   */
  public function rejectDocument(Request $request, PreAdmission $preAdmission)
  {
    $request->validate([
      'pre_admission_document_id' => 'required|exists:pre_admission_documents,id',
      'rejection_reason' => 'required|string',
    ]);

    $this->authorizeSecretary($preAdmission, $request->user());

    $padDoc = $preAdmission->documents()
      ->findOrFail($request->pre_admission_document_id);

    $padDoc->reject($request->rejection_reason);
    $padDoc->update(['status' => 'rejected']);

    return back()->with('success', 'Documento rechazado');
  }

  /**
   * Confirm pre-admission ready for surgery (turns green)
   */
  public function confirm(Request $request, PreAdmission $preAdmission)
  {
    $this->authorizeSecretary($preAdmission, $request->user());

    if (!$preAdmission->canConfirmForSurgery()) {
      return back()->withErrors([
        'error' => 'No se puede confirmar: datos o documentos pendientes de verificación',
      ]);
    }

    if ($preAdmission->confirmForSurgery()) {
      return back()->with('success', '✅ Pre-internación confirmada. Operación lista para quirófano.');
    }

    return back()->withErrors(['error' => 'Error al confirmar']);
  }

  /**
   * Cancel pre-admission
   */
  public function cancel(Request $request, PreAdmission $preAdmission)
  {
    $request->validate([
      'cancellation_reason' => 'required|string',
    ]);

    $this->authorizeSecretary($preAdmission, $request->user());

    $preAdmission->cancel($request->cancellation_reason);

    return back()->with('success', 'Pre-internación cancelada');
  }

  /**
   * Helper: Check if user can manage this pre-admission
   */
  private function authorizeSecretary(PreAdmission $preAdmission, $user): void
  {
    if ($user->isSecretary() && $preAdmission->secretary_id !== $user->id) {
      abort(403, 'No autorizado');
    }
    if (!($user->isSecretary() || $user->isAdmin())) {
      abort(403, 'No autorizado');
    }
  }
}
