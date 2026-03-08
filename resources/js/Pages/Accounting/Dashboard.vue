<script setup lang="ts">
import { ref, computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface PatientAccount {
    id: number;
    patient_id: number;
    balance: number;
    total_charged: number;
    total_paid: number;
    total_credits: number;
    status: string;
    payment_status: string;
    last_payment_date: string | null;
    days_overdue: number;
    accrued_interest: number;
    interest_rate: number;
    patient: {
        id: number;
        first_name: string;
        last_name: string;
        dni: string;
    };
}

interface Props {
    stats: {
        total_patients: number;
        total_due: number;
        overdue_accounts: number;
        total_charged: number;
        total_paid: number;
    };
    top_debtors: PatientAccount[];
    collection_rate: number;
}

const props = defineProps<Props>();

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
    }).format(value);
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        active: "bg-green-100 text-green-800",
        suspended: "bg-yellow-100 text-yellow-800",
        blocked: "bg-red-100 text-red-800",
    };
    return colors[status] || "bg-gray-100 text-gray-800";
};

const getPaymentStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        current: "text-green-600",
        overdue: "text-orange-600",
        in_arrears: "text-red-600",
    };
    return colors[status] || "text-gray-600";
};

const getPaymentStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        current: "Al día",
        overdue: "Vencido",
        in_arrears: "En mora",
    };
    return labels[status] || status;
};
</script>

<template>
    <Head title="Dashboard de Cuentas Corrientes" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Dashboard de Cuentas Corrientes
                </h1>
                <p class="text-gray-600 mt-2">
                    Gestión de cuentas y deudas de pacientes
                </p>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-5 gap-4 mb-8">
                <Link
                    :href="route('accounting.index')"
                    class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition"
                >
                    <p class="text-gray-600 text-sm mb-1">Pacientes Activos</p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ stats.total_patients }}
                    </p>
                </Link>

                <Link
                    :href="route('accounting.index')"
                    class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition"
                >
                    <p class="text-gray-600 text-sm mb-1">Total a Cobrar</p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ formatCurrency(stats.total_due) }}
                    </p>
                </Link>

                <Link
                    :href="route('accounting.index')"
                    class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition"
                >
                    <p class="text-gray-600 text-sm mb-1">Cuentas Vencidas</p>
                    <p class="text-3xl font-bold text-orange-600">
                        {{ stats.overdue_accounts }}
                    </p>
                </Link>

                <Link
                    :href="route('accounting.index')"
                    class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition"
                >
                    <p class="text-gray-600 text-sm mb-1">Total Devengado</p>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ formatCurrency(stats.total_charged) }}
                    </p>
                </Link>

                <Link
                    :href="route('accounting.index')"
                    class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition"
                >
                    <p class="text-gray-600 text-sm mb-1">Tasa de Cobranza</p>
                    <p class="text-3xl font-bold text-green-600">
                        {{ (collection_rate * 100).toFixed(1) }}%
                    </p>
                </Link>
            </div>

            <!-- Top Debtors -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Top 10 Deudores
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
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Balance
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Total Cobrado
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Estado
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Pago
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Días Vencidos
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
                                v-for="account in top_debtors"
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
                                        class="text-sm font-semibold"
                                        :class="
                                            account.balance < 0
                                                ? 'text-red-600'
                                                : 'text-green-600'
                                        "
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
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium',
                                            getStatusColor(account.status),
                                        ]"
                                    >
                                        {{ account.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'text-sm font-medium',
                                            getPaymentStatusColor(
                                                account.payment_status,
                                            ),
                                        ]"
                                    >
                                        {{
                                            getPaymentStatusLabel(
                                                account.payment_status,
                                            )
                                        }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900">
                                        {{ account.days_overdue || 0 }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <Link
                                        :href="
                                            route('accounting.show', account.id)
                                        "
                                        class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                                    >
                                        Ver
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
