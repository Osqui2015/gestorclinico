<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";

interface PharmacyItem {
    id: number;
    name: string;
    type: string;
    current_stock: number;
    minimum_stock: number;
    expiration_date?: string;
    next_sterilization_date?: string;
    unit_measurement: string;
}

interface PharmacyRequest {
    id: number;
    priority: string;
    status: string;
    requested_at: string;
    requested_by: {
        id: number;
        name: string;
    };
    patient?: {
        id: number;
        first_name: string;
        last_name: string;
    };
    items: Array<{
        id: number;
        quantity_requested: number;
        pharmacy_item: {
            name: string;
        };
    }>;
}

interface Alerts {
    lowStock: PharmacyItem[];
    expiringSoon: PharmacyItem[];
    expired: PharmacyItem[];
    sterilizationDue: PharmacyItem[];
}

interface Statistics {
    totalItems: number;
    totalMedications: number;
    totalInstruments: number;
    totalSupplies: number;
    totalPendingRequests: number;
    totalProcessingRequests: number;
}

const props = defineProps<{
    alerts: Alerts;
    pendingRequests: PharmacyRequest[];
    statistics: Statistics;
}>();

const totalAlerts = computed(() => {
    return (
        props.alerts.lowStock.length +
        props.alerts.expiringSoon.length +
        props.alerts.expired.length +
        props.alerts.sterilizationDue.length
    );
});

const getPriorityColor = (priority: string) => {
    switch (priority) {
        case "urgent":
            return "bg-red-100 text-red-800";
        case "high":
            return "bg-orange-100 text-orange-800";
        case "normal":
            return "bg-blue-100 text-blue-800";
        case "low":
            return "bg-gray-100 text-gray-800";
        default:
            return "bg-gray-100 text-gray-800";
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("es-AR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
};
</script>

<template>
    <Head title="Farmacia - Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        💊 Farmacia - Dashboard
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Gestión de medicamentos, instrumentos e insumos
                    </p>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Statistics Cards -->
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <!-- Total Items -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 rounded-md bg-indigo-500 p-3"
                                >
                                    <svg
                                        class="h-6 w-6 text-white"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="truncate text-sm font-medium text-gray-500"
                                        >
                                            Total de Items
                                        </dt>
                                        <dd
                                            class="text-3xl font-semibold text-gray-900"
                                        >
                                            {{ statistics.totalItems }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            <div class="mt-4 text-sm text-gray-500">
                                <span
                                    >{{
                                        statistics.totalMedications
                                    }}
                                    medicamentos</span
                                >
                                <span class="mx-1">•</span>
                                <span
                                    >{{
                                        statistics.totalInstruments
                                    }}
                                    instrumentos</span
                                >
                                <span class="mx-1">•</span>
                                <span
                                    >{{
                                        statistics.totalSupplies
                                    }}
                                    insumos</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Pending Requests -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 rounded-md bg-yellow-500 p-3"
                                >
                                    <svg
                                        class="h-6 w-6 text-white"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="truncate text-sm font-medium text-gray-500"
                                        >
                                            Solicitudes Pendientes
                                        </dt>
                                        <dd
                                            class="text-3xl font-semibold text-gray-900"
                                        >
                                            {{
                                                statistics.totalPendingRequests
                                            }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            <div class="mt-4">
                                <Link
                                    :href="route('pharmacy.requests.index')"
                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                                >
                                    Ver todas →
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Alerts -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 rounded-md bg-red-500 p-3"
                                >
                                    <svg
                                        class="h-6 w-6 text-white"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="truncate text-sm font-medium text-gray-500"
                                        >
                                            Alertas Activas
                                        </dt>
                                        <dd
                                            class="text-3xl font-semibold text-gray-900"
                                        >
                                            {{ totalAlerts }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            <div class="mt-4 text-sm text-gray-500">
                                <span
                                    >{{ alerts.lowStock.length }} stock
                                    bajo</span
                                >
                                <span class="mx-1">•</span>
                                <span
                                    >{{
                                        alerts.expiringSoon.length +
                                        alerts.expired.length
                                    }}
                                    vencimientos</span
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Two column layout -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Alerts Section -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">
                                🚨 Alertas
                            </h3>
                            <Link
                                :href="route('pharmacy.items.index')"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                            >
                                Ver todos los items
                            </Link>
                        </div>

                        <!-- Low Stock Alert -->
                        <div
                            v-if="alerts.lowStock.length > 0"
                            class="rounded-lg border border-orange-200 bg-orange-50 p-4"
                        >
                            <h4
                                class="mb-2 flex items-center text-sm font-semibold text-orange-900"
                            >
                                <svg
                                    class="mr-2 h-5 w-5"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                Stock Bajo ({{ alerts.lowStock.length }})
                            </h4>
                            <div class="space-y-2">
                                <div
                                    v-for="item in alerts.lowStock.slice(0, 5)"
                                    :key="item.id"
                                    class="flex items-center justify-between rounded bg-white p-2 text-sm"
                                >
                                    <span class="font-medium text-gray-900">{{
                                        item.name
                                    }}</span>
                                    <span class="text-orange-700"
                                        >{{ item.current_stock }}
                                        {{ item.unit_measurement }}</span
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Expiring Soon Alert -->
                        <div
                            v-if="alerts.expiringSoon.length > 0"
                            class="rounded-lg border border-yellow-200 bg-yellow-50 p-4"
                        >
                            <h4
                                class="mb-2 flex items-center text-sm font-semibold text-yellow-900"
                            >
                                <svg
                                    class="mr-2 h-5 w-5"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                Próximos a Vencer ({{
                                    alerts.expiringSoon.length
                                }})
                            </h4>
                            <div class="space-y-2">
                                <div
                                    v-for="item in alerts.expiringSoon.slice(
                                        0,
                                        5,
                                    )"
                                    :key="item.id"
                                    class="flex items-center justify-between rounded bg-white p-2 text-sm"
                                >
                                    <span class="font-medium text-gray-900">{{
                                        item.name
                                    }}</span>
                                    <span class="text-yellow-700">{{
                                        formatDate(item.expiration_date!)
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Expired Alert -->
                        <div
                            v-if="alerts.expired.length > 0"
                            class="rounded-lg border border-red-200 bg-red-50 p-4"
                        >
                            <h4
                                class="mb-2 flex items-center text-sm font-semibold text-red-900"
                            >
                                <svg
                                    class="mr-2 h-5 w-5"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                Vencidos ({{ alerts.expired.length }})
                            </h4>
                            <div class="space-y-2">
                                <div
                                    v-for="item in alerts.expired.slice(0, 5)"
                                    :key="item.id"
                                    class="flex items-center justify-between rounded bg-white p-2 text-sm"
                                >
                                    <span class="font-medium text-gray-900">{{
                                        item.name
                                    }}</span>
                                    <span class="text-red-700">{{
                                        formatDate(item.expiration_date!)
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Sterilization Due Alert -->
                        <div
                            v-if="alerts.sterilizationDue.length > 0"
                            class="rounded-lg border border-blue-200 bg-blue-50 p-4"
                        >
                            <h4
                                class="mb-2 flex items-center text-sm font-semibold text-blue-900"
                            >
                                <svg
                                    class="mr-2 h-5 w-5"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                Esterilización Pendiente ({{
                                    alerts.sterilizationDue.length
                                }})
                            </h4>
                            <div class="space-y-2">
                                <div
                                    v-for="item in alerts.sterilizationDue.slice(
                                        0,
                                        5,
                                    )"
                                    :key="item.id"
                                    class="flex items-center justify-between rounded bg-white p-2 text-sm"
                                >
                                    <span class="font-medium text-gray-900">{{
                                        item.name
                                    }}</span>
                                    <span class="text-blue-700">{{
                                        formatDate(
                                            item.next_sterilization_date!,
                                        )
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="totalAlerts === 0"
                            class="rounded-lg border border-green-200 bg-green-50 p-4 text-center"
                        >
                            <p class="text-sm font-medium text-green-800">
                                ✅ No hay alertas pendientes
                            </p>
                        </div>
                    </div>

                    <!-- Pending Requests Section -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">
                                📋 Solicitudes Pendientes
                            </h3>
                            <Link
                                :href="route('pharmacy.requests.index')"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                            >
                                Ver todas
                            </Link>
                        </div>

                        <div
                            v-if="pendingRequests.length > 0"
                            class="space-y-3"
                        >
                            <Link
                                v-for="request in pendingRequests.slice(0, 10)"
                                :key="request.id"
                                :href="
                                    route('pharmacy.requests.show', request.id)
                                "
                                class="block rounded-lg border border-gray-200 bg-white p-4 transition hover:border-indigo-500 hover:shadow-md"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span
                                                :class="[
                                                    'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                                    getPriorityColor(
                                                        request.priority,
                                                    ),
                                                ]"
                                            >
                                                {{
                                                    request.priority ===
                                                    "urgent"
                                                        ? "Urgente"
                                                        : request.priority ===
                                                            "high"
                                                          ? "Alta"
                                                          : request.priority ===
                                                              "normal"
                                                            ? "Normal"
                                                            : "Baja"
                                                }}
                                            </span>
                                            <span class="text-xs text-gray-500"
                                                >Solicitud #{{
                                                    request.id
                                                }}</span
                                            >
                                        </div>
                                        <div class="mt-2">
                                            <p
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                Dr.
                                                {{ request.requested_by.name }}
                                            </p>
                                            <p
                                                v-if="request.patient"
                                                class="mt-1 text-sm text-gray-600"
                                            >
                                                Paciente:
                                                {{ request.patient.first_name }}
                                                {{ request.patient.last_name }}
                                            </p>
                                            <p
                                                class="mt-1 text-xs text-gray-500"
                                            >
                                                {{
                                                    request.items.length
                                                }}
                                                item(s) solicitado(s)
                                            </p>
                                        </div>
                                    </div>
                                    <div class="ml-4 text-right">
                                        <p class="text-xs text-gray-500">
                                            {{
                                                formatDate(request.requested_at)
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <div
                            v-else
                            class="rounded-lg border border-gray-200 bg-white p-6 text-center"
                        >
                            <p class="text-sm text-gray-500">
                                No hay solicitudes pendientes
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">
                        Acciones Rápidas
                    </h3>
                    <div
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <Link
                            :href="route('pharmacy.items.create')"
                            class="flex items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-4 text-center transition hover:border-indigo-500 hover:bg-indigo-50"
                        >
                            <div>
                                <svg
                                    class="mx-auto h-8 w-8 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4v16m8-8H4"
                                    />
                                </svg>
                                <p
                                    class="mt-2 text-sm font-medium text-gray-900"
                                >
                                    Nuevo Item
                                </p>
                            </div>
                        </Link>

                        <Link
                            :href="route('pharmacy.items.index')"
                            class="flex items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-4 text-center transition hover:border-indigo-500 hover:bg-indigo-50"
                        >
                            <div>
                                <svg
                                    class="mx-auto h-8 w-8 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                    />
                                </svg>
                                <p
                                    class="mt-2 text-sm font-medium text-gray-900"
                                >
                                    Inventario
                                </p>
                            </div>
                        </Link>

                        <Link
                            :href="route('pharmacy.requests.index')"
                            class="flex items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-4 text-center transition hover:border-indigo-500 hover:bg-indigo-50"
                        >
                            <div>
                                <svg
                                    class="mx-auto h-8 w-8 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                    />
                                </svg>
                                <p
                                    class="mt-2 text-sm font-medium text-gray-900"
                                >
                                    Solicitudes
                                </p>
                            </div>
                        </Link>

                        <Link
                            :href="
                                route('pharmacy.items.index', {
                                    alert: 'low_stock',
                                })
                            "
                            class="flex items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-4 text-center transition hover:border-indigo-500 hover:bg-indigo-50"
                        >
                            <div>
                                <svg
                                    class="mx-auto h-8 w-8 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                    />
                                </svg>
                                <p
                                    class="mt-2 text-sm font-medium text-gray-900"
                                >
                                    Ver Alertas
                                </p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
