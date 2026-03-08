<script setup lang="ts">
import { ref } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

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

interface Bed {
    id: number;
    bed_number: string;
    full_name: string;
    room: {
        id: number;
        name: string;
    };
}

interface Hospitalization {
    id: number;
    admission_date: string;
    expected_discharge_date: string | null;
    actual_discharge_date: string | null;
    admission_type: string;
    status: string;
    days_hospitalized: number;
    patient: Patient;
    doctor: Doctor;
    bed: Bed;
    discharger?: {
        id: number;
        name: string;
    };
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    hospitalizations: {
        data: Hospitalization[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
        links: PaginationLink[];
    };
    patients: Patient[];
    doctors: Doctor[];
    filters: {
        status?: string;
        patient_id?: number;
        doctor_id?: number;
        from_date?: string;
        to_date?: string;
    };
}

const props = defineProps<Props>();

const filters = ref({
    status: props.filters.status || "",
    patient_id: props.filters.patient_id || "",
    doctor_id: props.filters.doctor_id || "",
    from_date: props.filters.from_date || "",
    to_date: props.filters.to_date || "",
});

const applyFilters = () => {
    router.get(route("hospitalizations.history"), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = {
        status: "",
        patient_id: "",
        doctor_id: "",
        from_date: "",
        to_date: "",
    };
    applyFilters();
};

const getStatusLabel = (status: string): string => {
    const labels: Record<string, string> = {
        active: "Activo",
        discharged: "Alta",
        transferred: "Transferido",
        deceased: "Fallecido",
    };
    return labels[status] || status;
};

const getStatusClass = (status: string): string => {
    const classes: Record<string, string> = {
        active: "bg-green-100 text-green-800",
        discharged: "bg-blue-100 text-blue-800",
        transferred: "bg-yellow-100 text-yellow-800",
        deceased: "bg-gray-100 text-gray-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const getAdmissionTypeLabel = (type: string): string => {
    const labels: Record<string, string> = {
        emergency: "🚨 Emergencia",
        scheduled: "📅 Programada",
        post_surgical: "🏥 Post-quirúrgica",
        transfer: "🔄 Transferencia",
    };
    return labels[type] || type;
};

const formatDateTime = (value: string): string => {
    return new Date(value).toLocaleString("es-AR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Historial de Internaciones" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <a
                            :href="route('hospitalizations.index')"
                            class="text-sm text-indigo-600 hover:text-indigo-900 mb-2 inline-block"
                        >
                            ← Volver al listado
                        </a>
                        <h2 class="text-2xl font-bold text-gray-900">
                            📋 Historial de Internaciones
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Registro completo de todas las internaciones
                        </p>
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
                                <option value="active">Activo</option>
                                <option value="discharged">Alta</option>
                                <option value="transferred">Transferido</option>
                                <option value="deceased">Fallecido</option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Paciente</label
                            >
                            <select
                                v-model="filters.patient_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option
                                    v-for="patient in patients"
                                    :key="patient.id"
                                    :value="patient.id"
                                >
                                    {{ patient.last_name }},
                                    {{ patient.first_name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Médico</label
                            >
                            <select
                                v-model="filters.doctor_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option
                                    v-for="doctor in doctors"
                                    :key="doctor.id"
                                    :value="doctor.id"
                                >
                                    {{ doctor.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Desde</label
                            >
                            <input
                                v-model="filters.from_date"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @change="applyFilters"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Hasta</label
                            >
                            <input
                                v-model="filters.to_date"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @change="applyFilters"
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

                <!-- Table -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Paciente
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Cama
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Médico
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Tipo
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Ingreso
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Alta
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Días
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Estado
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="hosp in hospitalizations.data"
                                :key="hosp.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ hosp.patient.first_name }}
                                        {{ hosp.patient.last_name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ hosp.patient.dni }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ hosp.bed.full_name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ hosp.bed.room.name }}
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{ hosp.doctor.name }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{
                                        getAdmissionTypeLabel(
                                            hosp.admission_type,
                                        )
                                    }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{ formatDateTime(hosp.admission_date) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    <div v-if="hosp.actual_discharge_date">
                                        {{
                                            formatDateTime(
                                                hosp.actual_discharge_date,
                                            )
                                        }}
                                        <div
                                            v-if="hosp.discharger"
                                            class="text-xs text-gray-400"
                                        >
                                            por {{ hosp.discharger.name }}
                                        </div>
                                    </div>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{ hosp.days_hospitalized }} días
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="getStatusClass(hosp.status)"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    >
                                        {{ getStatusLabel(hosp.status) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div
                        v-if="hospitalizations.data.length > 0"
                        class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <a
                                    v-if="hospitalizations.links[0].url"
                                    :href="hospitalizations.links[0].url!"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Anterior
                                </a>
                                <a
                                    v-if="
                                        hospitalizations.links[
                                            hospitalizations.links.length - 1
                                        ].url
                                    "
                                    :href="
                                        hospitalizations.links[
                                            hospitalizations.links.length - 1
                                        ].url!
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
                                            hospitalizations.from
                                        }}</span>
                                        a
                                        <span class="font-medium">{{
                                            hospitalizations.to
                                        }}</span>
                                        de
                                        <span class="font-medium">{{
                                            hospitalizations.total
                                        }}</span>
                                        resultados
                                    </p>
                                </div>
                                <div>
                                    <nav
                                        class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                    >
                                        <template
                                            v-for="(
                                                link, index
                                            ) in hospitalizations.links"
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
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                            No hay registros
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            No se encontraron internaciones con los filtros
                            aplicados.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
