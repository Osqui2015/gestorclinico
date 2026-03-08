<script setup lang="ts">
import { ref, computed } from "vue";
import { Head, router } from "@inertiajs/vue3";
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
    patient: {
        id: number;
        first_name: string;
        last_name: string;
        dni: string;
    };
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    accounts: {
        data: PatientAccount[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
        links: PaginationLink[];
    };
    filters: {
        status?: string;
        payment_status?: string;
        search?: string;
    };
}

const props = defineProps<Props>();

const filters = ref({
    status: props.filters.status || "",
    payment_status: props.filters.payment_status || "",
    search: props.filters.search || "",
});

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

const getPaymentStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        current: "Al día",
        overdue: "Vencido",
        in_arrears: "En mora",
    };
    return labels[status] || status;
};

const applyFilters = () => {
    router.get(route("accounting.index"), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = {
        status: "",
        payment_status: "",
        search: "",
    };
    applyFilters();
};

const viewAccount = (id: number) => {
    router.get(route("accounting.show", id));
};
</script>

<template>
    <Head title="Cuentas Corrientes" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Cuentas Corrientes de Pacientes
                </h1>
                <p class="text-gray-600 mt-2">
                    Gestión de estados de cuenta y cobranzas
                </p>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Filtros
                </h3>
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <!-- Search -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Buscar (Nombre/DNI)</label
                        >
                        <input
                            v-model="filters.search"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Nombre o DNI"
                        />
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Estado de Cuenta</label
                        >
                        <select
                            v-model="filters.status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Todos</option>
                            <option value="active">Activa</option>
                            <option value="suspended">Suspendida</option>
                            <option value="blocked">Bloqueada</option>
                        </select>
                    </div>

                    <!-- Payment Status Filter -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Estado de Pago</label
                        >
                        <select
                            v-model="filters.payment_status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Todos</option>
                            <option value="current">Al día</option>
                            <option value="overdue">Vencido</option>
                            <option value="in_arrears">En mora</option>
                        </select>
                    </div>
                </div>

                <!-- Filter Buttons -->
                <div class="flex gap-3">
                    <button
                        @click="applyFilters"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                    >
                        🔍 Buscar
                    </button>
                    <button
                        @click="clearFilters"
                        class="px-4 py-2 bg-gray-300 text-gray-900 rounded-lg hover:bg-gray-400 transition"
                    >
                        ✕ Limpiar
                    </button>
                </div>
            </div>

            <!-- Accounts Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Cuentas ({{ accounts.total }} total)
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
                                    Devengado
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Cobrado
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
                                    Días Venc.
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
                                v-for="account in accounts.data"
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
                                    <p class="text-sm text-gray-900">
                                        {{ formatCurrency(account.total_paid) }}
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
                                            'px-2 py-1 rounded text-xs font-medium',
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
                                    <p
                                        class="text-sm font-medium"
                                        :class="
                                            account.days_overdue > 0
                                                ? 'text-red-600'
                                                : 'text-gray-900'
                                        "
                                    >
                                        {{ account.days_overdue || 0 }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <button
                                        @click="viewAccount(account.id)"
                                        class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                                    >
                                        Ver
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="accounts.last_page > 1"
                    class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-between items-center"
                >
                    <p class="text-sm text-gray-600">
                        Mostrando {{ accounts.from }} a {{ accounts.to }} de
                        {{ accounts.total }} cuentas
                    </p>
                    <div class="flex gap-1">
                        <a
                            v-for="link in accounts.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1 rounded text-sm',
                                link.active
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-300 text-gray-900 hover:bg-gray-400',
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
