<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";

interface User {
    id: number;
    name: string;
}

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
}

interface PharmacyRequest {
    id: number;
    requested_by: User;
    patient?: Patient;
    priority: string;
    status: string;
    requested_at: string;
    processed_at?: string;
    completed_at?: string;
    items: Array<{
        id: number;
        quantity_requested: number;
        quantity_delivered: number;
        pharmacy_item: {
            name: string;
        };
    }>;
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
        priority?: string;
        search?: string;
    };
}>();

const filters = ref({
    status: props.filters.status || "",
    priority: props.filters.priority || "",
    search: props.filters.search || "",
});

const applyFilters = () => {
    router.get(
        route("pharmacy.requests.index"),
        {
            status: filters.value.status || undefined,
            priority: filters.value.priority || undefined,
            search: filters.value.search || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const clearFilters = () => {
    filters.value = {
        status: "",
        priority: "",
        search: "",
    };
    applyFilters();
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

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString("es-AR");
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
</script>

<template>
    <Head title="Solicitudes de Farmacia" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        📋 Solicitudes de Farmacia
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Gestiona las solicitudes de materiales y medicamentos
                    </p>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 rounded-lg bg-white p-4 shadow">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div>
                            <label
                                for="search"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Buscar
                            </label>
                            <input
                                id="search"
                                v-model="filters.search"
                                type="text"
                                placeholder="Doctor o paciente..."
                                @keyup.enter="applyFilters"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                        </div>

                        <div>
                            <label
                                for="status"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Estado
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

                        <div>
                            <label
                                for="priority"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Prioridad
                            </label>
                            <select
                                id="priority"
                                v-model="filters.priority"
                                @change="applyFilters"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">Todas</option>
                                <option value="urgent">Urgente</option>
                                <option value="high">Alta</option>
                                <option value="normal">Normal</option>
                                <option value="low">Baja</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button
                                @click="clearFilters"
                                type="button"
                                class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Limpiar Filtros
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Requests Table -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
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
                                    Solicitado por
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Paciente
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Items
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Prioridad
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Estado
                                </th>
                                <th class="relative px-6 py-3">
                                    <span class="sr-only">Acciones</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr
                                v-for="request in requests.data"
                                :key="request.id"
                                class="hover:bg-gray-50"
                            >
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900"
                                >
                                    #{{ request.id }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm text-gray-500"
                                >
                                    {{ formatDateTime(request.requested_at) }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm text-gray-900"
                                >
                                    {{ request.requested_by.name }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm text-gray-900"
                                >
                                    {{
                                        request.patient
                                            ? `${request.patient.first_name} ${request.patient.last_name}`
                                            : "N/A"
                                    }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm text-gray-500"
                                >
                                    {{ request.items.length }} items ({{
                                        getTotalRequested(request)
                                    }}
                                    solicitados,
                                    {{ getTotalDelivered(request) }} entregados)
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span
                                        :class="[
                                            getPriorityClass(request.priority),
                                            'inline-flex rounded-full px-2 text-xs font-semibold leading-5',
                                        ]"
                                    >
                                        {{ getPriorityLabel(request.priority) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span
                                        :class="[
                                            getStatusClass(request.status),
                                            'inline-flex rounded-full px-2 text-xs font-semibold leading-5',
                                        ]"
                                    >
                                        {{ getStatusLabel(request.status) }}
                                    </span>
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium"
                                >
                                    <Link
                                        :href="
                                            route(
                                                'pharmacy.requests.show',
                                                request.id,
                                            )
                                        "
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        Ver detalles
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <div
                        v-if="requests.data.length === 0"
                        class="px-6 py-12 text-center"
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
                            No hay solicitudes
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            No se encontraron solicitudes con los filtros
                            aplicados.
                        </p>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="requests.data.length > 0"
                        class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6"
                    >
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                v-if="requests.current_page > 1"
                                :href="
                                    requests.links[requests.current_page - 1]
                                        .url
                                "
                                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Anterior
                            </Link>
                            <Link
                                v-if="
                                    requests.current_page < requests.last_page
                                "
                                :href="
                                    requests.links[requests.current_page + 1]
                                        .url
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
        </div>
    </AuthenticatedLayout>
</template>
