<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\HealthInsurance;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
  /**
   * Display invoices
   */
  public function index(Request $request)
  {
    $query = Invoice::with(['patient', 'appointment', 'creator'])
      ->when($request->search, function ($q, $search) {
        $q->where('invoice_number', 'like', "%{$search}%")
          ->orWhereHas('patient', function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('dni', 'like', "%{$search}%");
          });
      })
      ->when($request->status, fn($q, $status) => $q->where('status', $status))
      ->when($request->from_date, fn($q, $date) => $q->whereDate('invoice_date', '>=', $date))
      ->when($request->to_date, fn($q, $date) => $q->whereDate('invoice_date', '<=', $date))
      ->orderBy('invoice_date', 'desc')
      ->orderBy('id', 'desc');

    $invoices = $query->paginate($request->per_page ?? 15);

    return Inertia::render('Invoices/Index', [
      'invoices' => $invoices,
      'filters' => $request->only(['search', 'status', 'from_date', 'to_date']),
    ]);
  }

  /**
   * Show create invoice form
   */
  public function create(Request $request)
  {
    $patients = Patient::orderBy('last_name')->orderBy('first_name')->get();
    $healthInsurances = HealthInsurance::where('is_active', true)->orderBy('name')->get();

    $appointment = null;
    if ($request->appointment_id) {
      $appointment = Appointment::with(['patient', 'doctor'])->find($request->appointment_id);
    }

    return Inertia::render('Invoices/Create', [
      'patients' => $patients,
      'healthInsurances' => $healthInsurances,
      'appointment' => $appointment,
      'nextInvoiceNumber' => Invoice::generateInvoiceNumber(),
    ]);
  }

  /**
   * Store new invoice
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'patient_id' => 'required|exists:patients,id',
      'appointment_id' => 'nullable|exists:appointments,id',
      'health_insurance_id' => 'nullable|exists:health_insurances,id',
      'invoice_date' => 'required|date',
      'items' => 'required|array|min:1',
      'items.*.description' => 'required|string|max:255',
      'items.*.quantity' => 'required|integer|min:1',
      'items.*.unit_price' => 'required|numeric|min:0',
      'discount' => 'nullable|numeric|min:0',
      'insurance_coverage' => 'nullable|numeric|min:0',
      'notes' => 'nullable|string',
      'payment_method' => 'nullable|in:cash,card,transfer,insurance,other',
    ]);

    DB::beginTransaction();
    try {
      // Calculate totals
      $subtotal = collect($validated['items'])->sum(function ($item) {
        return $item['quantity'] * $item['unit_price'];
      });

      $discount = $validated['discount'] ?? 0;
      $insurance_coverage = $validated['insurance_coverage'] ?? 0;
      $total = $subtotal - $discount - $insurance_coverage;

      // Create invoice
      $invoice = Invoice::create([
        'invoice_number' => Invoice::generateInvoiceNumber(),
        'patient_id' => $validated['patient_id'],
        'appointment_id' => $validated['appointment_id'] ?? null,
        'health_insurance_id' => $validated['health_insurance_id'] ?? null,
        'invoice_date' => $validated['invoice_date'],
        'subtotal' => $subtotal,
        'discount' => $discount,
        'insurance_coverage' => $insurance_coverage,
        'total' => $total,
        'status' => 'pending',
        'notes' => $validated['notes'] ?? null,
        'created_by' => $request->user()->id,
      ]);

      // Create invoice items
      foreach ($validated['items'] as $item) {
        $invoice->items()->create([
          'description' => $item['description'],
          'quantity' => $item['quantity'],
          'unit_price' => $item['unit_price'],
          'total' => $item['quantity'] * $item['unit_price'],
        ]);
      }

      // If payment method is provided, create payment
      if (isset($validated['payment_method'])) {
        Payment::create([
          'invoice_id' => $invoice->id,
          'amount' => $total,
          'payment_method' => $validated['payment_method'],
          'payment_date' => $validated['invoice_date'],
          'received_by' => $request->user()->id,
        ]);

        $invoice->update([
          'status' => 'paid',
          'paid_at' => now(),
          'payment_method' => $validated['payment_method'],
        ]);
      }

      DB::commit();

      return redirect()->route('invoices.show', $invoice)
        ->with('success', 'Factura creada exitosamente');
    } catch (\Exception $e) {
      DB::rollBack();
      return back()->withErrors(['error' => 'Error al crear la factura: ' . $e->getMessage()]);
    }
  }

  /**
   * Display invoice
   */
  public function show(Invoice $invoice)
  {
    $invoice->load(['patient', 'appointment.doctor', 'healthInsurance', 'items', 'payments.receiver', 'creator']);

    return Inertia::render('Invoices/Show', [
      'invoice' => $invoice,
    ]);
  }

  /**
   * Add payment to invoice
   */
  public function addPayment(Request $request, Invoice $invoice)
  {
    $validated = $request->validate([
      'amount' => 'required|numeric|min:0.01|max:' . $invoice->balance,
      'payment_method' => 'required|in:cash,card,transfer,check,other',
      'reference_number' => 'nullable|string|max:255',
      'payment_date' => 'required|date',
      'notes' => 'nullable|string',
    ]);

    DB::beginTransaction();
    try {
      Payment::create([
        'invoice_id' => $invoice->id,
        'amount' => $validated['amount'],
        'payment_method' => $validated['payment_method'],
        'reference_number' => $validated['reference_number'] ?? null,
        'payment_date' => $validated['payment_date'],
        'notes' => $validated['notes'] ?? null,
        'received_by' => $request->user()->id,
      ]);

      // Update invoice status
      $totalPaid = $invoice->payments()->sum('amount') + $validated['amount'];

      if ($totalPaid >= $invoice->total) {
        $invoice->update([
          'status' => 'paid',
          'paid_at' => now(),
        ]);
      } else {
        $invoice->update([
          'status' => 'partially_paid',
        ]);
      }

      DB::commit();

      return back()->with('success', 'Pago registrado exitosamente');
    } catch (\Exception $e) {
      DB::rollBack();
      return back()->withErrors(['error' => 'Error al registrar el pago']);
    }
  }

  /**
   * Cancel invoice
   */
  public function cancel(Invoice $invoice)
  {
    if ($invoice->status === 'paid') {
      return back()->withErrors(['error' => 'No se puede cancelar una factura pagada']);
    }

    $invoice->update(['status' => 'cancelled']);

    return back()->with('success', 'Factura cancelada exitosamente');
  }
}
