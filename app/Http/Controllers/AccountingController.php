<?php

namespace App\Http\Controllers;

use App\Models\PatientAccount;
use App\Models\AccountTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AccountingController extends Controller
{
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
   * Dashboard de cuentas corrientes
   */
  public function dashboard()
  {
    $totalCharged = (float) PatientAccount::sum('total_charged');
    $totalPaid = (float) PatientAccount::sum('total_paid');

    // Estadísticas
    $stats = [
      'total_patients' => PatientAccount::count(),
      'total_due' => PatientAccount::where('balance', '<', 0)->sum(DB::raw('ABS(balance)')),
      'overdue_accounts' => PatientAccount::where('payment_status', 'overdue')->count(),
      'total_charged' => $totalCharged,
      'total_paid' => $totalPaid,
    ];

    $collectionRate = $totalCharged > 0
      ? min(1, $totalPaid / $totalCharged)
      : 0;

    // Cuentas en mora
    $topDebtors = PatientAccount::with('patient')
      ->where('balance', '<', 0)
      ->orderByRaw('ABS(balance) DESC')
      ->limit(10)
      ->get();

    return Inertia::render('Accounting/Dashboard', [
      'stats' => $stats,
      'top_debtors' => $topDebtors,
      'collection_rate' => $collectionRate,
    ]);
  }

  /**
   * Listar cuentas corrientes
   */
  public function index(Request $request)
  {
    $query = PatientAccount::with('patient');

    // Filtros
    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    if ($request->filled('payment_status')) {
      $query->where('payment_status', $request->payment_status);
    }

    if ($request->filled('search')) {
      $search = $request->search;
      $query->whereHas('patient', function ($q) use ($search) {
        $q->where('first_name', 'like', "%{$search}%")
          ->orWhere('last_name', 'like', "%{$search}%")
          ->orWhere('dni', 'like', "%{$search}%");
      });
    }

    $accounts = $query->orderByDesc('updated_at')->paginate(20);

    return Inertia::render('Accounting/Index', [
      'accounts' => $accounts,
      'filters' => $request->only(['status', 'payment_status', 'search']),
    ]);
  }

  /**
   * Ver detalle de cuenta
   */
  public function show(PatientAccount $account)
  {
    $account->load('patient');

    $transactions = $account->transactions()
      ->with('createdBy:id,name')
      ->orderByDesc('transaction_date')
      ->paginate(30)
      ->through(function (AccountTransaction $transaction): array {
        return [
          'id' => $transaction->id,
          'type' => $transaction->type,
          'concept' => $transaction->concept,
          'amount' => (float) $transaction->amount,
          'balance_after' => (float) $transaction->balance_after,
          'transaction_date' => optional($transaction->transaction_date)->toDateString(),
          'payment_method' => $transaction->payment_method ?? 'other',
          'created_by' => $transaction->createdBy
            ? [
              'id' => $transaction->createdBy->id,
              'name' => $transaction->createdBy->name,
            ]
            : null,
          'notes' => $transaction->notes,
        ];
      });

    $accountData = [
      'id' => $account->id,
      'patient_id' => $account->patient_id,
      'balance' => (float) $account->balance,
      'total_charged' => (float) $account->total_charged,
      'total_paid' => (float) $account->total_paid,
      'total_credits' => (float) $account->total_credits,
      'status' => $account->status,
      'payment_status' => $account->payment_status,
      'last_payment_date' => optional($account->last_payment_date)->toDateString(),
      'days_overdue' => (int) $account->days_overdue,
      'accrued_interest' => (float) $account->accrued_interest,
      'interest_rate' => (float) $account->interest_rate,
      'patient' => [
        'id' => $account->patient->id,
        'first_name' => $account->patient->first_name,
        'last_name' => $account->patient->last_name,
        'dni' => $account->patient->dni,
        'phone' => $account->patient->phone,
        'email' => $account->patient->email,
      ],
      'transactions' => $transactions,
    ];

    return Inertia::render('Accounting/Show', [
      'account' => $accountData,
    ]);
  }

  /**
   * Formulario para registrar pago
   */
  public function paymentForm(PatientAccount $account)
  {
    $account->load('patient');

    return Inertia::render('Accounting/PaymentForm', [
      'account' => $account,
    ]);
  }

  /**
   * Formulario para registrar cargo
   */
  public function chargeForm(PatientAccount $account)
  {
    $account->load('patient');

    return Inertia::render('Accounting/ChargeForm', [
      'account' => $account,
    ]);
  }

  /**
   * Registrar cobro
   */
  public function recordCharge(Request $request, PatientAccount $account)
  {
    $validated = $request->validate([
      'concept' => 'required|string',
      'amount' => 'required|numeric|min:0.01',
      'description' => 'nullable|string',
      'reference_type' => 'nullable|string',
      'reference_id' => 'nullable|integer',
    ]);

    return DB::transaction(function () use ($account, $validated, $request) {
      $user = $this->authUser();

      $validated['type'] = 'charge';
      $validated['created_by'] = $user->id;
      $validated['transaction_date'] = today();
      $validated['balance_after'] = $account->balance - $validated['amount'];

      AccountTransaction::create([
        'patient_account_id' => $account->id,
        ...$validated,
      ]);

      $account->update([
        'balance' => $validated['balance_after'],
        'total_charged' => $account->total_charged + $validated['amount'],
      ]);

      $this->updatePaymentStatus($account);

      return back()->with('success', 'Cobro registrado');
    });
  }

  /**
   * Registrar pago
   */
  public function recordPayment(Request $request, PatientAccount $account)
  {
    $validated = $request->validate([
      'amount' => 'required|numeric|min:0.01',
      'payment_method' => 'required|in:cash,check,transfer,credit_card,debit_card,promissory_note,credit,insurance,other',
      'voucher_number' => 'nullable|string',
      'concept' => 'nullable|string',
      'notes' => 'nullable|string',
    ]);

    return DB::transaction(function () use ($account, $validated) {
      $user = $this->authUser();

      $validated['type'] = 'payment';
      $validated['created_by'] = $user->id;
      $validated['transaction_date'] = today();
      $validated['concept'] = $validated['concept'] ?? 'Pago de cuenta corriente';
      $validated['balance_after'] = $account->balance + $validated['amount'];

      AccountTransaction::create([
        'patient_account_id' => $account->id,
        ...$validated,
      ]);

      $account->update([
        'balance' => $validated['balance_after'],
        'total_paid' => $account->total_paid + $validated['amount'],
        'last_payment_date' => today(),
      ]);

      $this->updatePaymentStatus($account);

      return back()->with('success', 'Pago registrado');
    });
  }

  /**
   * Registrar crédito
   */
  public function grantCredit(Request $request, PatientAccount $account)
  {
    $validated = $request->validate([
      'amount' => 'required|numeric|min:0.01',
      'concept' => 'required|string',
      'description' => 'nullable|string',
    ]);

    return DB::transaction(function () use ($account, $validated) {
      $user = $this->authUser();

      $validated['type'] = 'credit';
      $validated['created_by'] = $user->id;
      $validated['transaction_date'] = today();
      $validated['balance_after'] = $account->balance + $validated['amount'];

      AccountTransaction::create([
        'patient_account_id' => $account->id,
        ...$validated,
      ]);

      $account->update([
        'balance' => $validated['balance_after'],
        'total_credits' => $account->total_credits + $validated['amount'],
      ]);

      return back()->with('success', 'Crédito registrado');
    });
  }

  /**
   * Condonar deuda
   */
  public function writeOff(Request $request, PatientAccount $account)
  {
    $validated = $request->validate([
      'amount' => 'required|numeric|min:0.01',
      'reason' => 'required|string',
    ]);

    return DB::transaction(function () use ($account, $validated) {
      $user = $this->authUser();

      $this->createTransaction($account, [
        'type' => 'write_off',
        'amount' => $validated['amount'],
        'concept' => 'Condonación: ' . $validated['reason'],
        'created_by' => $user->id,
        'balance_after' => $account->balance + $validated['amount'],
      ]);

      $account->update([
        'balance' => $account->balance + $validated['amount'],
      ]);

      $this->updatePaymentStatus($account);

      return back()->with('success', 'Deuda condonada');
    });
  }

  /**
   * Actualizar estado de pago
   */
  private function updatePaymentStatus(PatientAccount $account): void
  {
    if ($account->balance >= 0) {
      $account->update([
        'payment_status' => 'current',
        'days_overdue' => 0,
      ]);
    } else {
      $lastPayment = $account->last_payment_date ?? $account->created_at;
      $daysOverdue = today()->diffInDays($lastPayment);

      $newStatus = $daysOverdue > 30 ? 'in_arrears' : 'overdue';

      $account->update([
        'payment_status' => $newStatus,
        'days_overdue' => $daysOverdue,
      ]);
    }
  }

  /**
   * Helper para crear transacción
   */
  private function createTransaction(PatientAccount $account, array $data): AccountTransaction
  {
    $data['patient_account_id'] = $account->id;
    $data['transaction_date'] = $data['transaction_date'] ?? today();

    return AccountTransaction::create($data);
  }

  /**
   * Reporte de deudores
   */
  public function debtors()
  {
    $accounts = PatientAccount::with('patient')
      ->where('balance', '<', 0)
      ->orderByRaw('ABS(balance) DESC')
      ->get();

    $totalDebt = (float) PatientAccount::where('balance', '<', 0)
      ->sum(DB::raw('ABS(balance)'));

    $totalAccounts = $accounts->count();
    $averageDebt = $totalAccounts > 0 ? $totalDebt / $totalAccounts : 0;

    return Inertia::render('Accounting/Debtors', [
      'accounts' => $accounts,
      'total_debt' => $totalDebt,
      'total_accounts' => $totalAccounts,
      'average_debt' => $averageDebt,
    ]);
  }

  /**
   * Exportar movimientos
   */
  public function exportTransactions(Request $request, PatientAccount $account)
  {
    $transactions = $account->transactions()
      ->orderByDesc('transaction_date')
      ->get();

    $csv = "Fecha,Tipo,Concepto,Monto,Saldo,Método,Usuario\n";

    foreach ($transactions as $t) {
      $csv .= "{$t->transaction_date},{$t->getTypeLabel()},\"{$t->concept}\",{$t->amount},{$t->balance_after},";
      $csv .= "{$t->getPaymentMethodLabel()},{$t->createdBy?->name}\n";
    }

    return response($csv, 200, [
      'Content-Type' => 'text/csv; charset=utf-8',
      'Content-Disposition' => 'attachment; filename="account_' . $account->patient->dni . '.csv"',
    ]);
  }
}
