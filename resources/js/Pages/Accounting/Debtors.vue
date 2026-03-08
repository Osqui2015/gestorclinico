<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface PatientAccount {
    id: number;
    patient: {
        id: number;
        first_name: string;
        last_name: string;
        dni: string;
    };
    balance: number;
    days_overdue: number;
    total_charged: number;
    accrued_interest: number;
}

interface Props {
    accounts: PatientAccount[];
    total_debt: number;
    total_accounts: number;
    average_debt: number;
}

const props = defineProps<Props>();

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
    }).format(value);
};
</script>

<template>
    <Head title="Reporte de Deudores" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Reporte de Deudores
                </h1>
                <p class="text-gray-600 mt-2">
                    Análisis de cuentas con saldo deudor
                </p>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Total de Deudores</p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ props.total_accounts }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Deuda Total</p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ formatCurrency(props.total_debt) }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Deuda Promedio</p>
                    <p class="text-2xl font-bold text-orange-600">
                        {{ formatCurrency(props.average_debt) }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Interés Acumulado</p>
                    <p class="text-2xl font-bold text-purple-600">
                        {{
                            formatCurrency(
                                props.accounts.reduce(
                                    (sum, acc) => sum + acc.accrued_interest,
                                    0,
                                ),
                            )
                        }}
                    </p>
                </div>
            </div>

            <!-- Debtors Table -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Deudores ({{ props.accounts.length }})
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Paciente
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    DNI
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                >
                                    Deuda
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                >
                                    Facturado
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                >
                                    Días Vencidos
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                >
                                    Interés
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="account in props.accounts"
                                :key="account.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4">
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ account.patient.first_name }}
                                        {{ account.patient.last_name }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600">
                                        {{ account.patient.dni }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p
                                        class="text-sm font-semibold text-red-600"
                                    >
                                        {{ formatCurrency(account.balance) }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900">
                                        {{
                                            formatCurrency(
                                                account.total_charged,
                                            )
                                        }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p
                                        class="text-sm font-semibold text-right"
                                        :class="
                                            account.days_overdue > 60
                                                ? 'text-red-600'
                                                : 'text-orange-600'
                                        "
                                    >
                                        {{ account.days_overdue }} días
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p
                                        class="text-sm font-semibold text-purple-600 text-right"
                                    >
                                        {{
                                            formatCurrency(
                                                account.accrued_interest,
                                            )
                                        }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <Link
                                        :href="
                                            route('accounting.show', account.id)
                                        "
                                        class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                                    >
                                        Ver Cuenta
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
