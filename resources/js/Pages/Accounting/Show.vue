<script setup lang="ts">
import { ref } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
    phone: string;
    email: string;
}

interface User {
    id: number;
    name: string;
}

interface AccountTransaction {
    id: number;
    type: string;
    concept: string;
    amount: number;
    balance_after: number;
    transaction_date: string;
    payment_method: string;
    created_by: User | null;
    notes: string | null;
}

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
    patient: Patient;
    transactions: {
        data: AccountTransaction[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
}

interface Props {
    account: PatientAccount;
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
        current: "bg-green-100 text-green-800",
        overdue: "bg-orange-100 text-orange-800",
        in_arrears: "bg-red-100 text-red-800",
    };
    return colors[status] || "bg-gray-100 text-gray-800";
};

const getTransactionTypeColor = (type: string) => {
    const colors: Record<string, string> = {
        charge: "text-red-600",
        payment: "text-green-600",
        credit: "text-blue-600",
        write_off: "text-purple-600",
        interest: "text-orange-600",
        adjustment: "text-gray-600",
        refund: "text-green-500",
    };
    return colors[type] || "text-gray-600";
};

const getTransactionTypeIcon = (type: string) => {
    const icons: Record<string, string> = {
        charge: "📤",
        payment: "📥",
        credit: "💳",
        write_off: "❌",
        interest: "📈",
        adjustment: "🔧",
        refund: "↩️",
    };
    return icons[type] || "•";
};

const getTransactionTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        charge: "Cargo",
        payment: "Pago",
        credit: "Crédito",
        write_off: "Condonación",
        interest: "Interés",
        adjustment: "Ajuste",
        refund: "Reembolso",
    };
    return labels[type] || type;
};

const getPaymentMethodIcon = (method: string) => {
    const icons: Record<string, string> = {
        cash: "💵",
        check: "🏦",
        transfer: "💸",
        credit_card: "💳",
        debit_card: "🏧",
        promissory_note: "📋",
        credit: "📊",
        insurance: "🏥",
        other: "❓",
    };
    return icons[method] || "•";
};

const goBack = () => {
    router.get(route("accounting.index"));
};

const recordPayment = () => {
    router.get(route("accounting.payment-form", props.account.id));
};

const recordCharge = () => {
    router.get(route("accounting.charge-form", props.account.id));
};
</script>

<template>
    <Head :title="`Cuenta ${account.patient.first_name}`" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ account.patient.first_name }}
                        {{ account.patient.last_name }}
                    </h1>
                    <p class="text-gray-600 mt-1">
                        DNI: {{ account.patient.dni }}
                    </p>
                </div>
                <div class="flex gap-3">
                    <button
                        @click="recordCharge"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                    >
                        ➕ Registrar Cargo
                    </button>
                    <button
                        @click="recordPayment"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                    >
                        ✓ Registrar Pago
                    </button>
                    <button
                        @click="goBack"
                        class="px-4 py-2 bg-gray-300 text-gray-900 rounded-lg hover:bg-gray-400 transition"
                    >
                        ← Volver
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-6 mb-8">
                <!-- Account Status -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        Estado de Cuenta
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Estado</p>
                            <span
                                :class="[
                                    'px-3 py-1 rounded text-sm font-medium inline-block',
                                    getStatusColor(account.status),
                                ]"
                            >
                                {{ account.status }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">
                                Condición de Pago
                            </p>
                            <span
                                :class="[
                                    'px-3 py-1 rounded text-sm font-medium inline-block',
                                    getPaymentStatusColor(
                                        account.payment_status,
                                    ),
                                ]"
                            >
                                {{ account.payment_status }}
                            </span>
                        </div>
                        <div v-if="account.days_overdue > 0">
                            <p class="text-sm text-gray-600">Días Vencidos</p>
                            <p class="text-xl font-bold text-red-600">
                                {{ account.days_overdue }} días
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Balance -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        Balance
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Balance Actual</p>
                            <p
                                class="text-2xl font-bold"
                                :class="
                                    account.balance < 0
                                        ? 'text-red-600'
                                        : 'text-green-600'
                                "
                            >
                                {{ formatCurrency(account.balance) }}
                            </p>
                        </div>
                        <div v-if="account.accrued_interest > 0">
                            <p class="text-sm text-gray-600">
                                Interés Devengado
                            </p>
                            <p class="text-lg font-semibold text-orange-600">
                                {{ formatCurrency(account.accrued_interest) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        Resumen
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Total Devengado</p>
                            <p class="font-semibold text-gray-900">
                                {{ formatCurrency(account.total_charged) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Cobrado</p>
                            <p class="font-semibold text-gray-900">
                                {{ formatCurrency(account.total_paid) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">
                                Créditos Otorgados
                            </p>
                            <p class="font-semibold text-gray-900">
                                {{ formatCurrency(account.total_credits) }}
                            </p>
                        </div>
                        <div v-if="account.last_payment_date">
                            <p class="text-sm text-gray-600">Último Pago</p>
                            <p class="font-semibold text-gray-900">
                                {{
                                    new Date(
                                        account.last_payment_date,
                                    ).toLocaleDateString("es-AR")
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Historial de Transacciones ({{
                            account.transactions.total
                        }})
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Fecha
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Tipo
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Concepto
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                >
                                    Monto
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                >
                                    Balance
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Método
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Usuario
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="transaction in account.transactions.data"
                                :key="transaction.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900">
                                        {{
                                            new Date(
                                                transaction.transaction_date,
                                            ).toLocaleDateString("es-AR")
                                        }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span>{{
                                            getTransactionTypeIcon(
                                                transaction.type,
                                            )
                                        }}</span>
                                        <span
                                            :class="[
                                                'text-sm font-medium',
                                                getTransactionTypeColor(
                                                    transaction.type,
                                                ),
                                            ]"
                                        >
                                            {{
                                                getTransactionTypeLabel(
                                                    transaction.type,
                                                )
                                            }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900">
                                        {{ transaction.concept }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p
                                        class="text-sm font-medium text-right"
                                        :class="
                                            transaction.type === 'charge' ||
                                            transaction.type === 'interest'
                                                ? 'text-red-600'
                                                : 'text-green-600'
                                        "
                                    >
                                        {{
                                            (transaction.type === "charge" ||
                                            transaction.type === "interest"
                                                ? "-"
                                                : "+") +
                                            formatCurrency(
                                                Math.abs(transaction.amount),
                                            )
                                        }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p
                                        class="text-sm font-medium text-right"
                                        :class="
                                            transaction.balance_after < 0
                                                ? 'text-red-600'
                                                : 'text-green-600'
                                        "
                                    >
                                        {{
                                            formatCurrency(
                                                transaction.balance_after,
                                            )
                                        }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1">
                                        <span>{{
                                            getPaymentMethodIcon(
                                                transaction.payment_method,
                                            )
                                        }}</span>
                                        <span class="text-xs text-gray-600">{{
                                            transaction.payment_method
                                        }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900">
                                        {{
                                            transaction.created_by?.name || "-"
                                        }}
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
