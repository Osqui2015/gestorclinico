<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";

interface Room {
    id: number;
    name: string;
    code: string;
}

interface Doctor {
    id: number;
    name: string;
}

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni?: string;
}

interface Secretary {
    id: number;
    name: string;
}

interface Operation {
    id: number;
    operation_type: string;
    urgency: string;
    scheduled_start: string;
    room: Room;
    doctor: Doctor;
}

interface PreAdmission {
    id: number;
    operation_id: number;
    patient_id: number;
    secretary_id?: number;
    status: string;
    urgent_number?: string;
    data_verified_at?: string;
    documentation_verified_at?: string;
    ready_for_surgery_at?: string;
    operation: Operation;
    patient: Patient;
    secretary?: Secretary;
}

const props = defineProps<{
    preAdmissions: {
        data: PreAdmission[];
        links: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        status?: string;
        urgent?: string;
        search?: string;
    };
    secretaries: Secretary[];
    permissions: {
        canAssign: boolean;
        canVerify: boolean;
    };
}>();

const assignmentSelection = ref<Record<number, string>>(
    Object.fromEntries(
        props.preAdmissions.data.map((pa) => [
            pa.id,
            pa.secretary_id ? String(pa.secretary_id) : "",
        ]),
    ),
);

const filters = ref({
    status: props.filters.status || "",
    urgent: props.filters.urgent || "",
    search: props.filters.search || "",
});

const applyFilters = () => {
    router.get(
        route("pre-admissions.index"),
        {
            status: filters.value.status || undefined,
            urgent: filters.value.urgent || undefined,
            search: filters.value.search || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const clearFilters = () => {
    filters.value = { status: "", urgent: "", search: "" };
    applyFilters();
};

const assignSecretary = (preAdmissionId: number) => {
    const secretaryId = Number(assignmentSelection.value[preAdmissionId]);
    if (!secretaryId) {
        return;
    }

    router.post(
        route("pre-admissions.assign", preAdmissionId),
        {
            secretary_id: secretaryId,
        },
        {
            preserveScroll: true,
        },
    );
};

const getStatusLabel = (status: string) => {
    const map: Record<string, string> = {
        pending_assignment: "Sin asignar",
        data_pending: "Datos pendientes",
        documents_pending: "Documentos pendientes",
        ready_for_surgery: "✅ Lista para cirugía",
        cancelled: "Cancelada",
    };
    return map[status] || status;
};

const getStatusClass = (status: string) => {
    const map: Record<string, string> = {
        pending_assignment: "bg-gray-100 text-gray-800",
        data_pending: "bg-yellow-100 text-yellow-800",
        documents_pending: "bg-orange-100 text-orange-800",
        ready_for_surgery: "bg-green-100 text-green-800",
        cancelled: "bg-red-100 text-red-800",
    };
    return map[status] || "bg-gray-100 text-gray-800";
};

const getUrgencyClass = (urgency: string) => {
    const map: Record<string, string> = {
        scheduled: "bg-blue-50 text-blue-700",
        urgent: "bg-orange-50 text-orange-700",
        emergency: "bg-red-50 text-red-700",
    };
    return map[urgency] || "bg-gray-50 text-gray-700";
};

const formatDateTime = (value: string) => {
    return new Date(value).toLocaleString("es-AR", {
        day: "2-digit",
        month: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>

<template>
    <Head title="Pre-Internación - Validación" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        📋 Pre-Internación
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Validación de datos y documentación de pacientes
                    </p>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="rounded-lg bg-white p-4 shadow">
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
                                placeholder="Nombre o número de urgencia..."
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
                                <option value="pending_assignment">
                                    Sin asignar
                                </option>
                                <option value="data_pending">
                                    Datos pendientes
                                </option>
                                <option value="documents_pending">
                                    Documentos pendientes
                                </option>
                                <option value="ready_for_surgery">
                                    Lista para cirugía
                                </option>
                                <option value="cancelled">Cancelada</option>
                            </select>
                        </div>

                        <div>
                            <label
                                for="urgent"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Urgencia
                            </label>
                            <select
                                id="urgent"
                                v-model="filters.urgent"
                                @change="applyFilters"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">Todas</option>
                                <option value="scheduled">Programada</option>
                                <option value="urgent">Urgente</option>
                                <option value="emergency">Emergencia</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button
                                @click="clearFilters"
                                type="button"
                                class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500"
                                >
                                    Paciente
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500"
                                >
                                    Operación
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500"
                                >
                                    Sala
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500"
                                >
                                    Secretaria
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500"
                                >
                                    Estado
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr
                                v-for="pa in preAdmissions.data"
                                :key="pa.id"
                                class="align-top hover:bg-gray-50"
                            >
                                <td class="px-4 py-4 text-sm">
                                    <p class="font-medium text-gray-900">
                                        {{ pa.patient.first_name }}
                                        {{ pa.patient.last_name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        DNI:
                                        {{ pa.patient.dni || "N/A" }}
                                    </p>
                                    <p
                                        v-if="pa.urgent_number"
                                        class="text-xs font-semibold text-blue-600"
                                    >
                                        #{{ pa.urgent_number }}
                                    </p>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <p class="font-medium text-gray-900">
                                        {{ pa.operation.operation_type }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{
                                            formatDateTime(
                                                pa.operation.scheduled_start,
                                            )
                                        }}
                                    </p>
                                    <span
                                        :class="[
                                            getUrgencyClass(
                                                pa.operation.urgency,
                                            ),
                                            'inline-block mt-1 rounded px-2 py-1 text-xs font-semibold',
                                        ]"
                                    >
                                        {{
                                            pa.operation.urgency
                                                .charAt(0)
                                                .toUpperCase() +
                                            pa.operation.urgency.slice(1)
                                        }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <p class="font-medium text-gray-900">
                                        {{ pa.operation.room.name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ pa.operation.room.code }}
                                    </p>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <p
                                        v-if="pa.secretary"
                                        class="text-gray-900"
                                    >
                                        {{ pa.secretary.name }}
                                    </p>
                                    <p
                                        v-else
                                        class="text-orange-600 font-semibold"
                                    >
                                        Sin asignar
                                    </p>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <span
                                        :class="[
                                            getStatusClass(pa.status),
                                            'inline-flex rounded-full px-3 py-1 text-xs font-semibold leading-5',
                                        ]"
                                    >
                                        {{ getStatusLabel(pa.status) }}
                                    </span>
                                </td>
                                <td
                                    class="px-4 py-4 text-right text-sm font-medium"
                                >
                                    <Link
                                        :href="
                                            route('pre-admissions.show', pa.id)
                                        "
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        Ver detalles
                                    </Link>

                                    <div
                                        v-if="
                                            permissions.canAssign &&
                                            !pa.secretary_id
                                        "
                                        class="mt-2 flex items-center justify-end gap-2"
                                    >
                                        <select
                                            v-model="assignmentSelection[pa.id]"
                                            class="rounded-md border-gray-300 py-1 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option value="">
                                                Asignar secretaria...
                                            </option>
                                            <option
                                                v-for="secretary in secretaries"
                                                :key="secretary.id"
                                                :value="String(secretary.id)"
                                            >
                                                {{ secretary.name }}
                                            </option>
                                        </select>
                                        <button
                                            type="button"
                                            :disabled="
                                                !assignmentSelection[pa.id]
                                            "
                                            @click="assignSecretary(pa.id)"
                                            class="rounded-md bg-indigo-600 px-2 py-1 text-xs font-semibold text-white disabled:opacity-50"
                                        >
                                            Asignar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div
                        v-if="preAdmissions.data.length === 0"
                        class="px-6 py-12 text-center"
                    >
                        <p class="text-sm text-gray-500">
                            No hay registros de pre-internación
                        </p>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="preAdmissions.last_page > 1"
                        class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6"
                    >
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                v-if="preAdmissions.current_page > 1"
                                :href="
                                    preAdmissions.links[
                                        preAdmissions.current_page - 1
                                    ].url
                                "
                                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Anterior
                            </Link>
                            <Link
                                v-if="
                                    preAdmissions.current_page <
                                    preAdmissions.last_page
                                "
                                :href="
                                    preAdmissions.links[
                                        preAdmissions.current_page + 1
                                    ].url
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
                                        (preAdmissions.current_page - 1) *
                                            preAdmissions.per_page +
                                        1
                                    }}</span>
                                    a
                                    <span class="font-medium">{{
                                        Math.min(
                                            preAdmissions.current_page *
                                                preAdmissions.per_page,
                                            preAdmissions.total,
                                        )
                                    }}</span>
                                    de
                                    <span class="font-medium">{{
                                        preAdmissions.total
                                    }}</span>
                                    resultados
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
