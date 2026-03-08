<script setup lang="ts">
import { ref, computed } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface Room {
    id: number;
    name: string;
    floor: number | null;
    wing: string | null;
}

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
}

interface Doctor {
    id: number;
    name: string;
}

interface Hospitalization {
    id: number;
    patient: Patient;
    doctor: Doctor;
    admission_date: string;
    expected_discharge_date: string | null;
    days_hospitalized: number;
}

interface Bed {
    id: number;
    bed_number: string;
    status: string;
    bed_type: string;
    full_name: string;
    room: Room;
    current_hospitalization: Hospitalization | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    beds: {
        data: Bed[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
        links: PaginationLink[];
    };
    rooms: Room[];
    floors: number[];
    filters: {
        status?: string;
        bed_type?: string;
        room_id?: number;
        floor?: number;
        search?: string;
    };
    stats: {
        total_beds: number;
        available: number;
        occupied: number;
        pending_cleaning: number;
        ready_for_discharge: number;
    };
}

const props = defineProps<Props>();

const filters = ref({
    status: props.filters.status || "",
    bed_type: props.filters.bed_type || "",
    room_id: props.filters.room_id || "",
    floor: props.filters.floor || "",
    search: props.filters.search || "",
});

const applyFilters = () => {
    router.get(route("hospitalizations.index"), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = {
        status: "",
        bed_type: "",
        room_id: "",
        floor: "",
        search: "",
    };
    applyFilters();
};

const getStatusLabel = (status: string): string => {
    const labels: Record<string, string> = {
        available: "Disponible",
        occupied: "Ocupada",
        pending_cleaning: "Pendiente limpieza",
        cleaning: "En limpieza",
        maintenance: "Mantenimiento",
    };
    return labels[status] || status;
};

const getStatusClass = (status: string): string => {
    const classes: Record<string, string> = {
        available: "bg-green-100 text-green-800",
        occupied: "bg-red-100 text-red-800",
        pending_cleaning: "bg-yellow-100 text-yellow-800",
        cleaning: "bg-blue-100 text-blue-800",
        maintenance: "bg-gray-100 text-gray-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const getBedTypeLabel = (type: string): string => {
    const labels: Record<string, string> = {
        standard: "Estándar",
        intensive_care: "Cuidados Intensivos",
        isolation: "Aislamiento",
        pediatric: "Pediátrica",
        psychiatric: "Psiquiátrica",
    };
    return labels[type] || type;
};

const formatDateTime = (value: string | null): string => {
    if (!value) return "-";
    return new Date(value).toLocaleString("es-AR", {
        day: "2-digit",
        month: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const formatDate = (value: string | null): string => {
    if (!value) return "-";
    return new Date(value).toLocaleDateString("es-AR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
};

const canDischarge = (bed: Bed): boolean => {
    if (!bed.current_hospitalization) return false;
    if (!bed.current_hospitalization.expected_discharge_date) return false;
    const expectedDate = new Date(
        bed.current_hospitalization.expected_discharge_date,
    );
    return expectedDate <= new Date();
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Gestión de Camas e Internación" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">
                            🛏️ Gestión de Camas e Internación
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Administre camas, internaciones y altas médicas
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <a
                            :href="route('hospitalizations.history')"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                        >
                            📋 Historial
                        </a>
                        <a
                            :href="route('hospitalizations.create')"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                        >
                            ➕ Nueva Internación
                        </a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 bg-indigo-500 rounded-md p-3"
                                >
                                    <span class="text-white text-xl">🛏️</span>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Total Camas
                                        </dt>
                                        <dd
                                            class="text-2xl font-semibold text-gray-900"
                                        >
                                            {{ stats.total_beds }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 bg-green-500 rounded-md p-3"
                                >
                                    <span class="text-white text-xl">✅</span>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Disponibles
                                        </dt>
                                        <dd
                                            class="text-2xl font-semibold text-gray-900"
                                        >
                                            {{ stats.available }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 bg-red-500 rounded-md p-3"
                                >
                                    <span class="text-white text-xl">🚫</span>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Ocupadas
                                        </dt>
                                        <dd
                                            class="text-2xl font-semibold text-gray-900"
                                        >
                                            {{ stats.occupied }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 bg-yellow-500 rounded-md p-3"
                                >
                                    <span class="text-white text-xl">🧹</span>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Pend. Limpieza
                                        </dt>
                                        <dd
                                            class="text-2xl font-semibold text-gray-900"
                                        >
                                            {{ stats.pending_cleaning }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 bg-blue-500 rounded-md p-3"
                                >
                                    <span class="text-white text-xl">🏥</span>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Listos p/ Alta
                                        </dt>
                                        <dd
                                            class="text-2xl font-semibold text-gray-900"
                                        >
                                            {{ stats.ready_for_discharge }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Estado</label
                            >
                            <select
                                v-model="filters.status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option value="available">Disponible</option>
                                <option value="occupied">Ocupada</option>
                                <option value="pending_cleaning">
                                    Pendiente limpieza
                                </option>
                                <option value="cleaning">En limpieza</option>
                                <option value="maintenance">
                                    Mantenimiento
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Tipo de Cama</label
                            >
                            <select
                                v-model="filters.bed_type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option value="standard">Estándar</option>
                                <option value="intensive_care">
                                    Cuidados Intensivos
                                </option>
                                <option value="isolation">Aislamiento</option>
                                <option value="pediatric">Pediátrica</option>
                                <option value="psychiatric">
                                    Psiquiátrica
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Habitación</label
                            >
                            <select
                                v-model="filters.room_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @change="applyFilters"
                            >
                                <option value="">Todas</option>
                                <option
                                    v-for="room in rooms"
                                    :key="room.id"
                                    :value="room.id"
                                >
                                    {{ room.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Piso</label
                            >
                            <select
                                v-model="filters.floor"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option
                                    v-for="floor in floors"
                                    :key="floor"
                                    :value="floor"
                                >
                                    Piso {{ floor }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Buscar Paciente</label
                            >
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Nombre o DNI..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button
                            @click="clearFilters"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Limpiar Filtros
                        </button>
                        <button
                            @click="applyFilters"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                        >
                            Aplicar Filtros
                        </button>
                    </div>
                </div>

                <!-- Beds Table -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Cama
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Estado
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Tipo
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Paciente
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Médico Responsable
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Días Internado
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Fecha Est. Alta
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="bed in beds.data"
                                :key="bed.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ bed.full_name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ bed.room.name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="getStatusClass(bed.status)"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    >
                                        {{ getStatusLabel(bed.status) }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{ getBedTypeLabel(bed.bed_type) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        v-if="bed.current_hospitalization"
                                        class="text-sm"
                                    >
                                        <div class="text-gray-900 font-medium">
                                            {{
                                                bed.current_hospitalization
                                                    .patient.first_name
                                            }}
                                            {{
                                                bed.current_hospitalization
                                                    .patient.last_name
                                            }}
                                        </div>
                                        <div class="text-gray-500">
                                            {{
                                                bed.current_hospitalization
                                                    .patient.dni
                                            }}
                                        </div>
                                    </div>
                                    <span v-else class="text-sm text-gray-400"
                                        >-</span
                                    >
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{
                                        bed.current_hospitalization?.doctor
                                            .name || "-"
                                    }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    <span v-if="bed.current_hospitalization">
                                        {{
                                            bed.current_hospitalization
                                                .days_hospitalized
                                        }}
                                        días
                                    </span>
                                    <span v-else>-</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        v-if="
                                            bed.current_hospitalization
                                                ?.expected_discharge_date
                                        "
                                    >
                                        <div class="text-sm text-gray-900">
                                            {{
                                                formatDate(
                                                    bed.current_hospitalization
                                                        .expected_discharge_date,
                                                )
                                            }}
                                        </div>
                                        <span
                                            v-if="canDischarge(bed)"
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800"
                                        >
                                            ✓ Lista para alta
                                        </span>
                                    </div>
                                    <span v-else class="text-sm text-gray-400"
                                        >-</span
                                    >
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                >
                                    <a
                                        :href="
                                            route(
                                                'hospitalizations.show',
                                                bed.id,
                                            )
                                        "
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        Ver Detalles
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div
                        v-if="beds.data.length > 0"
                        class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <a
                                    v-if="beds.links[0].url"
                                    :href="beds.links[0].url!"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Anterior
                                </a>
                                <a
                                    v-if="beds.links[beds.links.length - 1].url"
                                    :href="
                                        beds.links[beds.links.length - 1].url!
                                    "
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Siguiente
                                </a>
                            </div>
                            <div
                                class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between"
                            >
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Mostrando
                                        <span class="font-medium">{{
                                            beds.from
                                        }}</span>
                                        a
                                        <span class="font-medium">{{
                                            beds.to
                                        }}</span>
                                        de
                                        <span class="font-medium">{{
                                            beds.total
                                        }}</span>
                                        resultados
                                    </p>
                                </div>
                                <div>
                                    <nav
                                        class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                    >
                                        <template
                                            v-for="(link, index) in beds.links"
                                            :key="index"
                                        >
                                            <a
                                                v-if="link.url"
                                                :href="link.url"
                                                :class="[
                                                    link.active
                                                        ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                                ]"
                                                v-html="link.label"
                                            ></a>
                                            <span
                                                v-else
                                                :class="[
                                                    'relative inline-flex items-center px-4 py-2 border border-gray-300 bg-gray-100 text-gray-400 text-sm font-medium cursor-not-allowed',
                                                ]"
                                                v-html="link.label"
                                            ></span>
                                        </template>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-12">
                        <svg
                            class="mx-auto h-12 w-12 text-gray-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                            />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                            No hay camas
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            No se encontraron camas con los filtros aplicados.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
