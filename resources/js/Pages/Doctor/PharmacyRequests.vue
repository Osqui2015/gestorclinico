<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
}

interface User {
    id: number;
    name: string;
}

interface PharmacyItem {
    id: number;
    name: string;
    code: string;
    unit_measurement: string;
}

interface RequestItem {
    id: number;
    quantity_requested: number;
    quantity_delivered: number;
    pharmacy_item: PharmacyItem;
}

interface PharmacyRequest {
    id: number;
    patient?: Patient;
    processed_by?: User;
    priority: string;
    status: string;
    requested_at: string;
    processed_at?: string;
    completed_at?: string;
    notes?: string;
    pharmacy_notes?: string;
    items: RequestItem[];
}

const props = defineProps<{
    requests: {
        data: PharmacyRequest[];
        links: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        status?: string;
    };
}>();

const filters = ref({
    status: props.filters.status || "",
});

const applyFilters = () => {
    router.get(
        route("pharmacy-requests.my-requests"),
        {
            status: filters.value.status || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const getPriorityLabel = (priority: string) => {
    const labels: Record<string, string> = {
        low: "Baja",
        normal: "Normal",
        high: "Alta",
        urgent: "Urgente",
    };
    return labels[priority] || priority;
};

const getPriorityClass = (priority: string) => {
    const classes: Record<string, string> = {
        low: "bg-gray-100 text-gray-800",
        normal: "bg-blue-100 text-blue-800",
        high: "bg-orange-100 text-orange-800",
        urgent: "bg-red-100 text-red-800",
    };
    return classes[priority] || "bg-gray-100 text-gray-800";
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        pending: "Pendiente",
        processing: "En Proceso",
        completed: "Completada",
        cancelled: "Cancelada",
    };
    return labels[status] || status;
};

const getStatusClass = (status: string) => {
    const classes: Record<string, string> = {
        pending: "bg-yellow-100 text-yellow-800",
        processing: "bg-blue-100 text-blue-800",
        completed: "bg-green-100 text-green-800",
        cancelled: "bg-gray-100 text-gray-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const formatDateTime = (date: string) => {
    return new Date(date).toLocaleString("es-AR");
};

const getTotalRequested = (request: PharmacyRequest) => {
    return request.items.reduce(
        (sum, item) => sum + item.quantity_requested,
        0,
    );
};

const getTotalDelivered = (request: PharmacyRequest) => {
    return request.items.reduce(
        (sum, item) => sum + item.quantity_delivered,
        0,
    );
};

const getDeliveryProgress = (request: PharmacyRequest) => {
    const total = getTotalRequested(request);
    const delivered = getTotalDelivered(request);
    return total > 0 ? Math.round((delivered / total) * 100) : 0;
};
</script>

<template>
    <Head title="Mis Solicitudes de Farmacia" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        💊 Mis Solicitudes de Farmacia
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Gestiona tus solicitudes de medicamentos e insumos
                    </p>
                </div>
                <Link
                    :href="route('pharmacy-requests.create')"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                >
                    ➕ Nueva Solicitud
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 rounded-lg bg-white p-4 shadow">
                    <div class="flex items-end space-x-4">
                        <div class="flex-1">
                            <label
                                for="status"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Filtrar por estado
                            </label>
                            <select
                                id="status"
                                v-model="filters.status"
                                @change="applyFilters"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">Todos</option>
                                <option value="pending">Pendiente</option>
                                <option value="processing">En Proceso</option>
                                <option value="completed">Completada</option>
                                <option value="cancelled">Cancelada</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Requests List -->
                <div class="space-y-4">
                    <div
                        v-for="request in requests.data"
                        :key="request.id"
                        class="rounded-lg bg-white p-6 shadow hover:shadow-lg transition-shadow"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <!-- Header -->
                                <div class="flex items-center space-x-3">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Solicitud #{{ request.id }}
                                    </h3>
                                    <span
                                        :class="[
                                            getPriorityClass(request.priority),
                                            'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                        ]"
                                    >
                                        {{ getPriorityLabel(request.priority) }}
                                    </span>
                                    <span
                                        :class="[
                                            getStatusClass(request.status),
                                            'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                        ]"
                                    >
                                        {{ getStatusLabel(request.status) }}
                                    </span>
                                </div>

                                <!-- Request Info -->
                                <div
                                    class="mt-3 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2 lg:grid-cols-3"
                                >
                                    <div>
                                        <span class="font-medium text-gray-500"
                                            >Fecha:</span
                                        >
                                        <span class="ml-2 text-gray-900">{{
                                            formatDateTime(request.requested_at)
                                        }}</span>
                                    </div>
                                    <div v-if="request.patient">
                                        <span class="font-medium text-gray-500"
                                            >Paciente:</span
                                        >
                                        <span class="ml-2 text-gray-900"
                                            >{{ request.patient.first_name }}
                                            {{
                                                request.patient.last_name
                                            }}</span
                                        >
                                    </div>
                                    <div v-if="request.processed_by">
                                        <span class="font-medium text-gray-500"
                                            >Procesado por:</span
                                        >
                                        <span class="ml-2 text-gray-900">{{
                                            request.processed_by.name
                                        }}</span>
                                    </div>
                                </div>

                                <!-- Items Summary -->
                                <div class="mt-4">
                                    <h4
                                        class="text-sm font-medium text-gray-700"
                                    >
                                        Items solicitados ({{
                                            request.items.length
                                        }}):
                                    </h4>
                                    <div
                                        class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2"
                                    >
                                        <div
                                            v-for="item in request.items"
                                            :key="item.id"
                                            class="flex items-center justify-between rounded bg-gray-50 px-3 py-2 text-sm"
                                        >
                                            <div class="flex-1">
                                                <span
                                                    class="font-medium text-gray-900"
                                                    >{{
                                                        item.pharmacy_item.name
                                                    }}</span
                                                >
                                                <span class="ml-2 text-gray-500"
                                                    >({{
                                                        item.pharmacy_item.code
                                                    }})</span
                                                >
                                            </div>
                                            <div class="ml-4 text-right">
                                                <span
                                                    :class="[
                                                        item.quantity_delivered >=
                                                        item.quantity_requested
                                                            ? 'text-green-600'
                                                            : item.quantity_delivered >
                                                                0
                                                              ? 'text-yellow-600'
                                                              : 'text-gray-600',
                                                        'font-medium',
                                                    ]"
                                                >
                                                    {{
                                                        item.quantity_delivered
                                                    }}/{{
                                                        item.quantity_requested
                                                    }}
                                                    {{
                                                        item.pharmacy_item
                                                            .unit_measurement
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Progress Bar -->
                                <div
                                    v-if="
                                        request.status === 'processing' ||
                                        request.status === 'completed'
                                    "
                                    class="mt-4"
                                >
                                    <div
                                        class="flex items-center justify-between text-sm"
                                    >
                                        <span class="font-medium text-gray-700"
                                            >Progreso de entrega:</span
                                        >
                                        <span class="text-gray-900"
                                            >{{
                                                getDeliveryProgress(request)
                                            }}%</span
                                        >
                                    </div>
                                    <div
                                        class="mt-1 h-2 overflow-hidden rounded-full bg-gray-200"
                                    >
                                        <div
                                            class="h-full transition-all duration-300"
                                            :class="[
                                                getDeliveryProgress(request) ===
                                                100
                                                    ? 'bg-green-500'
                                                    : 'bg-blue-500',
                                            ]"
                                            :style="{
                                                width: `${getDeliveryProgress(request)}%`,
                                            }"
                                        ></div>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div
                                    v-if="
                                        request.notes || request.pharmacy_notes
                                    "
                                    class="mt-4 space-y-2"
                                >
                                    <div v-if="request.notes" class="text-sm">
                                        <span class="font-medium text-gray-500"
                                            >Mis notas:</span
                                        >
                                        <span class="ml-2 text-gray-700">{{
                                            request.notes
                                        }}</span>
                                    </div>
                                    <div
                                        v-if="request.pharmacy_notes"
                                        class="text-sm"
                                    >
                                        <span class="font-medium text-gray-500"
                                            >Notas de farmacia:</span
                                        >
                                        <span class="ml-2 text-gray-700">{{
                                            request.pharmacy_notes
                                        }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div class="ml-6">
                                <Link
                                    :href="
                                        route(
                                            'pharmacy-requests.show',
                                            request.id,
                                        )
                                    "
                                    class="rounded-lg bg-indigo-100 px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-200"
                                >
                                    Ver Detalles →
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="requests.data.length === 0"
                        class="rounded-lg bg-white px-6 py-12 text-center shadow"
                    >
                        <svg
                            class="mx-auto h-12 w-12 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                            No tienes solicitudes
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Crea una nueva solicitud para comenzar.
                        </p>
                        <div class="mt-6">
                            <Link
                                :href="route('pharmacy-requests.create')"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                            >
                                ➕ Nueva Solicitud
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="requests.data.length > 0"
                    class="mt-6 flex items-center justify-between rounded-lg bg-white px-4 py-3 shadow sm:px-6"
                >
                    <div class="flex flex-1 justify-between sm:hidden">
                        <Link
                            v-if="requests.current_page > 1"
                            :href="
                                requests.links[requests.current_page - 1].url
                            "
                            class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Anterior
                        </Link>
                        <Link
                            v-if="requests.current_page < requests.last_page"
                            :href="
                                requests.links[requests.current_page + 1].url
                            "
                            class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Siguiente
                        </Link>
                    </div>
                    <div
                        class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between"
                    >
                        <div>
                            <p class="text-sm text-gray-700">
                                Mostrando
                                <span class="font-medium">{{
                                    (requests.current_page - 1) *
                                        requests.per_page +
                                    1
                                }}</span>
                                a
                                <span class="font-medium">{{
                                    Math.min(
                                        requests.current_page *
                                            requests.per_page,
                                        requests.total,
                                    )
                                }}</span>
                                de
                                <span class="font-medium">{{
                                    requests.total
                                }}</span>
                                resultados
                            </p>
                        </div>
                        <div>
                            <nav
                                class="isolate inline-flex -space-x-px rounded-md shadow-sm"
                            >
                                <Link
                                    v-for="(link, index) in requests.links"
                                    :key="index"
                                    :href="link.url || '#'"
                                    v-html="link.label"
                                    :class="[
                                        link.active
                                            ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                        'relative inline-flex items-center border px-4 py-2 text-sm font-medium',
                                        !link.url
                                            ? 'cursor-not-allowed opacity-50'
                                            : '',
                                    ]"
                                />
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
