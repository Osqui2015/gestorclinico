<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps<{
    item: {
        id: number;
        name: string;
        type: string;
        description?: string;
        code: string;
        laboratory?: string;
        unit_price: number;
        current_stock: number;
        minimum_stock: number;
        reorder_point: number;
        unit_measurement: string;
        expiration_date?: string;
        batch_number?: string;
        requires_sterilization: boolean;
        last_sterilization_date?: string;
        next_sterilization_date?: string;
        status: string;
        notes?: string;
        created_at: string;
        updated_at: string;
        stock_movements?: Array<{
            id: number;
            movement_type: string;
            quantity: number;
            stock_before: number;
            stock_after: number;
            reference?: string;
            notes?: string;
            created_at: string;
            user: {
                name: string;
            };
        }>;
        request_items?: Array<{
            id: number;
            quantity_requested: number;
            quantity_delivered: number;
            pharmacy_request: {
                id: number;
                status: string;
                requested_at: string;
                requested_by: {
                    name: string;
                };
            };
        }>;
    };
}>();

const getTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        medication: "Medicamento",
        instrument: "Instrumento",
        supply: "Insumo",
    };
    return labels[type] || type;
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        active: "Activo",
        inactive: "Inactivo",
        discontinued: "Discontinuado",
    };
    return labels[status] || status;
};

const getStatusClass = (status: string) => {
    const classes: Record<string, string> = {
        active: "bg-green-100 text-green-800",
        inactive: "bg-gray-100 text-gray-800",
        discontinued: "bg-red-100 text-red-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const getMovementTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        entry: "Entrada",
        exit: "Salida",
        adjustment: "Ajuste",
        return: "Devolución",
        expired: "Vencido",
        damaged: "Dañado",
    };
    return labels[type] || type;
};

const getMovementTypeClass = (type: string) => {
    const classes: Record<string, string> = {
        entry: "text-green-600",
        return: "text-green-600",
        exit: "text-red-600",
        expired: "text-red-600",
        damaged: "text-red-600",
        adjustment: "text-blue-600",
    };
    return classes[type] || "text-gray-600";
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString("es-AR");
};

const formatDateTime = (date: string) => {
    return new Date(date).toLocaleString("es-AR");
};

const isLowStock = () => {
    return props.item.current_stock <= props.item.minimum_stock;
};

const isExpiringSoon = () => {
    if (!props.item.expiration_date) return false;
    const expirationDate = new Date(props.item.expiration_date);
    const today = new Date();
    const daysUntilExpiration = Math.floor(
        (expirationDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24),
    );
    return daysUntilExpiration <= 30 && daysUntilExpiration > 0;
};

const isExpired = () => {
    if (!props.item.expiration_date) return false;
    const expirationDate = new Date(props.item.expiration_date);
    const today = new Date();
    return expirationDate < today;
};

const isSterilizationDue = () => {
    if (
        !props.item.requires_sterilization ||
        !props.item.next_sterilization_date
    )
        return false;
    const nextDate = new Date(props.item.next_sterilization_date);
    const today = new Date();
    const daysUntilSterilization = Math.floor(
        (nextDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24),
    );
    return daysUntilSterilization <= 7 && daysUntilSterilization >= 0;
};

const activeTab = ref<"details" | "movements" | "requests">("details");
</script>

<template>
    <Head title="Detalle de Item" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ item.name }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ getTypeLabel(item.type) }} - Código: {{ item.code }}
                    </p>
                </div>
                <Link
                    :href="route('pharmacy.items.edit', item.id)"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                >
                    ✏️ Editar
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Alerts -->
                <div
                    v-if="
                        isLowStock() ||
                        isExpiringSoon() ||
                        isExpired() ||
                        isSterilizationDue()
                    "
                    class="mb-6 space-y-3"
                >
                    <div
                        v-if="isLowStock()"
                        class="rounded-lg border-l-4 border-yellow-400 bg-yellow-50 p-4"
                    >
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg
                                    class="h-5 w-5 text-yellow-400"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-yellow-800">
                                    ⚠️ Stock bajo: {{ item.current_stock }}
                                    {{ item.unit_measurement }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="isExpiringSoon()"
                        class="rounded-lg border-l-4 border-orange-400 bg-orange-50 p-4"
                    >
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg
                                    class="h-5 w-5 text-orange-400"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-orange-800">
                                    ⏰ Próximo a vencer:
                                    {{ formatDate(item.expiration_date!) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="isExpired()"
                        class="rounded-lg border-l-4 border-red-400 bg-red-50 p-4"
                    >
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg
                                    class="h-5 w-5 text-red-400"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">
                                    ❌ Item vencido:
                                    {{ formatDate(item.expiration_date!) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="isSterilizationDue()"
                        class="rounded-lg border-l-4 border-blue-400 bg-blue-50 p-4"
                    >
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg
                                    class="h-5 w-5 text-blue-400"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-800">
                                    🧼 Requiere esterilización pronto:
                                    {{
                                        formatDate(
                                            item.next_sterilization_date!,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8">
                            <button
                                @click="activeTab = 'details'"
                                :class="[
                                    activeTab === 'details'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                    'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
                                ]"
                            >
                                Detalles
                            </button>
                            <button
                                @click="activeTab = 'movements'"
                                :class="[
                                    activeTab === 'movements'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                    'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
                                ]"
                            >
                                Movimientos de Stock
                            </button>
                            <button
                                @click="activeTab = 'requests'"
                                :class="[
                                    activeTab === 'requests'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                    'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
                                ]"
                            >
                                Solicitudes
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Tab Content: Details -->
                <div v-if="activeTab === 'details'" class="space-y-6">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">
                            Información General
                        </h3>
                        <dl
                            class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2"
                        >
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Nombre
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ item.name }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Tipo
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ getTypeLabel(item.type) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Código
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ item.code }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Laboratorio
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ item.laboratory || "N/A" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Estado
                                </dt>
                                <dd class="mt-1">
                                    <span
                                        :class="[
                                            getStatusClass(item.status),
                                            'inline-flex rounded-full px-2 text-xs font-semibold leading-5',
                                        ]"
                                    >
                                        {{ getStatusLabel(item.status) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Precio Unitario
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    ${{ item.unit_price.toLocaleString() }}
                                </dd>
                            </div>
                            <div v-if="item.description" class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">
                                    Descripción
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ item.description }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">
                            Stock
                        </h3>
                        <dl
                            class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2"
                        >
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Stock Actual
                                </dt>
                                <dd
                                    class="mt-1 text-sm font-semibold text-gray-900"
                                >
                                    {{ item.current_stock }}
                                    {{ item.unit_measurement }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Stock Mínimo
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ item.minimum_stock }}
                                    {{ item.unit_measurement }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Punto de Reorden
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ item.reorder_point }}
                                    {{ item.unit_measurement }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Unidad de Medida
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ item.unit_measurement }}
                                </dd>
                            </div>
                            <div v-if="item.batch_number">
                                <dt class="text-sm font-medium text-gray-500">
                                    Número de Lote
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ item.batch_number }}
                                </dd>
                            </div>
                            <div v-if="item.expiration_date">
                                <dt class="text-sm font-medium text-gray-500">
                                    Fecha de Vencimiento
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ formatDate(item.expiration_date) }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div
                        v-if="item.requires_sterilization"
                        class="rounded-lg bg-white p-6 shadow"
                    >
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">
                            Esterilización
                        </h3>
                        <dl
                            class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2"
                        >
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Última Esterilización
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{
                                        item.last_sterilization_date
                                            ? formatDate(
                                                  item.last_sterilization_date,
                                              )
                                            : "N/A"
                                    }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Próxima Esterilización
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{
                                        item.next_sterilization_date
                                            ? formatDate(
                                                  item.next_sterilization_date,
                                              )
                                            : "N/A"
                                    }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div
                        v-if="item.notes"
                        class="rounded-lg bg-white p-6 shadow"
                    >
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">
                            Notas
                        </h3>
                        <p class="text-sm text-gray-700">{{ item.notes }}</p>
                    </div>
                </div>

                <!-- Tab Content: Movements -->
                <div
                    v-if="activeTab === 'movements'"
                    class="rounded-lg bg-white shadow"
                >
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Historial de Movimientos
                        </h3>
                    </div>
                    <div
                        v-if="
                            item.stock_movements &&
                            item.stock_movements.length > 0
                        "
                        class="overflow-hidden"
                    >
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Fecha
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Tipo
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Cantidad
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Stock Anterior
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Stock Final
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Usuario
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Referencia
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="movement in item.stock_movements"
                                    :key="movement.id"
                                >
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-500"
                                    >
                                        {{
                                            formatDateTime(movement.created_at)
                                        }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm"
                                    >
                                        <span
                                            :class="[
                                                getMovementTypeClass(
                                                    movement.movement_type,
                                                ),
                                                'font-medium',
                                            ]"
                                        >
                                            {{
                                                getMovementTypeLabel(
                                                    movement.movement_type,
                                                )
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm font-medium"
                                        :class="[
                                            movement.quantity > 0
                                                ? 'text-green-600'
                                                : 'text-red-600',
                                        ]"
                                    >
                                        {{ movement.quantity > 0 ? "+" : ""
                                        }}{{ movement.quantity }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-900"
                                    >
                                        {{ movement.stock_before }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900"
                                    >
                                        {{ movement.stock_after }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-500"
                                    >
                                        {{ movement.user.name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ movement.reference || "-" }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="px-6 py-12 text-center">
                        <p class="text-sm text-gray-500">
                            No hay movimientos registrados
                        </p>
                    </div>
                </div>

                <!-- Tab Content: Requests -->
                <div
                    v-if="activeTab === 'requests'"
                    class="rounded-lg bg-white shadow"
                >
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Solicitudes Relacionadas
                        </h3>
                    </div>
                    <div
                        v-if="
                            item.request_items && item.request_items.length > 0
                        "
                        class="overflow-hidden"
                    >
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        #
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Fecha
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Solicitado Por
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Cantidad Solicitada
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Cantidad Entregada
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Estado
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="requestItem in item.request_items"
                                    :key="requestItem.id"
                                >
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm font-medium text-indigo-600"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'pharmacy.requests.show',
                                                    requestItem.pharmacy_request
                                                        .id,
                                                )
                                            "
                                            class="hover:text-indigo-900"
                                        >
                                            #{{
                                                requestItem.pharmacy_request.id
                                            }}
                                        </Link>
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-500"
                                    >
                                        {{
                                            formatDateTime(
                                                requestItem.pharmacy_request
                                                    .requested_at,
                                            )
                                        }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-900"
                                    >
                                        {{
                                            requestItem.pharmacy_request
                                                .requested_by.name
                                        }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-900"
                                    >
                                        {{ requestItem.quantity_requested }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-900"
                                    >
                                        {{ requestItem.quantity_delivered }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm"
                                    >
                                        <span
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                            :class="{
                                                'bg-yellow-100 text-yellow-800':
                                                    requestItem.pharmacy_request
                                                        .status === 'pending',
                                                'bg-blue-100 text-blue-800':
                                                    requestItem.pharmacy_request
                                                        .status ===
                                                    'processing',
                                                'bg-green-100 text-green-800':
                                                    requestItem.pharmacy_request
                                                        .status === 'completed',
                                                'bg-gray-100 text-gray-800':
                                                    requestItem.pharmacy_request
                                                        .status === 'cancelled',
                                            }"
                                        >
                                            {{
                                                requestItem.pharmacy_request
                                                    .status
                                            }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="px-6 py-12 text-center">
                        <p class="text-sm text-gray-500">
                            No hay solicitudes relacionadas
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
