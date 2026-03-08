<?php

namespace Database\Seeders;

use App\Models\PatientAccount;
use App\Models\AccountTransaction;
use App\Models\Patient;
use Illuminate\Database\Seeder;

class AccountingDemoSeeder extends Seeder
{
  public function run(): void
  {
    $this->command->info('Creando datos de demostración para cuentas corrientes...');

    $patients = Patient::take(10)->get();

    foreach ($patients as $index => $patient) {
      $account = $patient->account;

      if (!$account) {
        continue;
      }

      // Simular diferentes escenarios de pago
      if ($index % 3 === 0) {
        // Paciente con deuda
        $totalCharge = rand(5000, 15000);
        $payment = rand(1000, 5000);

        AccountTransaction::create([
          'patient_account_id' => $account->id,
          'type' => 'charge',
          'concept' => 'Consulta médica',
          'amount' => $totalCharge,
          'balance_after' => -$totalCharge,
          'transaction_date' => now()->subDays(rand(30, 90)),
        ]);

        AccountTransaction::create([
          'patient_account_id' => $account->id,
          'type' => 'payment',
          'concept' => 'Pago parcial',
          'amount' => $payment,
          'balance_after' => - ($totalCharge - $payment),
          'transaction_date' => now()->subDays(rand(20, 60)),
          'payment_method' => 'transfer',
          'voucher_number' => 'TRF-' . rand(100000, 999999),
        ]);

        $account->update([
          'balance' => - ($totalCharge - $payment),
          'total_charged' => $totalCharge,
          'total_paid' => $payment,
          'payment_status' => 'overdue',
          'last_payment_date' => now()->subDays(rand(20, 60)),
          'days_overdue' => rand(10, 45),
        ]);
      } else if ($index % 3 === 1) {
        // Paciente al día
        $totalCharge = rand(3000, 10000);

        AccountTransaction::create([
          'patient_account_id' => $account->id,
          'type' => 'charge',
          'concept' => 'Consulta + estudios',
          'amount' => $totalCharge,
          'balance_after' => -$totalCharge,
          'transaction_date' => now()->subDays(rand(5, 15)),
        ]);

        AccountTransaction::create([
          'patient_account_id' => $account->id,
          'type' => 'payment',
          'concept' => 'Pago completo',
          'amount' => $totalCharge,
          'balance_after' => 0,
          'transaction_date' => now()->subDays(rand(2, 10)),
          'payment_method' => 'cash',
        ]);

        $account->update([
          'balance' => 0,
          'total_charged' => $totalCharge,
          'total_paid' => $totalCharge,
          'payment_status' => 'current',
          'last_payment_date' => now()->subDays(rand(2, 10)),
          'days_overdue' => 0,
        ]);
      } else {
        // Paciente con crédito
        $totalCharge = rand(2000, 8000);
        $credit = rand(500, 2000);

        AccountTransaction::create([
          'patient_account_id' => $account->id,
          'type' => 'charge',
          'concept' => 'Internación',
          'amount' => $totalCharge,
          'balance_after' => -$totalCharge,
          'transaction_date' => now()->subDays(rand(10, 30)),
        ]);

        AccountTransaction::create([
          'patient_account_id' => $account->id,
          'type' => 'credit',
          'concept' => 'Crédito para tratamiento',
          'amount' => $credit,
          'balance_after' => - ($totalCharge - $credit),
          'transaction_date' => now()->subDays(rand(5, 20)),
        ]);

        $account->update([
          'balance' => - ($totalCharge - $credit),
          'total_charged' => $totalCharge,
          'total_credits' => $credit,
          'payment_status' => 'overdue',
          'days_overdue' => rand(5, 30),
        ]);
      }
    }

    $this->command->info('✅ Datos de demostración de cuentas corrientes creados exitosamente.');
    $this->command->info('Resumen:');
    $this->command->info('  - Cuentas al día: ' . PatientAccount::where('payment_status', 'current')->count());
    $this->command->info('  - Cuentas vencidas: ' . PatientAccount::where('payment_status', 'overdue')->count());
    $this->command->info('  - Cuentas en mora: ' . PatientAccount::where('payment_status', 'in_arrears')->count());
    $this->command->info('  - Total deuda: $' . $this->formatCurrency(PatientAccount::where('balance', '<', 0)->sum('balance')));
  }

  private function formatCurrency($amount)
  {
    return number_format(abs($amount), 2, ',', '.');
  }
}
