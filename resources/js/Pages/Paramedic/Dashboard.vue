<script setup lang="ts">
import { ref } from "vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Ambulance {
    id: number;
    internal_code: string;
    plate_number: string | null;
    brand: string | null;
    model: string | null;
    base_location: string | null;
    current_mileage: number | null;
    status: string;
}

interface Transfer {
    id: number;
    origin: string;
    destination: string;
    transfer_type: string;
    priority: string;
    status: string;
    requested_at: string;
    patient: {
        id: number;
        full_name: string;
        dni: string;
    } | null;
    ambulance: {
        id: number;
        internal_code: string;
        plate_number: string | null;
        status: string;
    } | null;
    requester: {
        id: number;
        name: string;
    } | null;
}

interface Props {
    ambulances: {
        data: Ambulance[];
        links: PaginationLink[];
    };
    transfers: {
        data: Transfer[];
        links: PaginationLink[];
    };
    patients: Array<{
        id: number;
        full_name: string;
        dni: string;
    }>;
    available_ambulances: Array<{
        id: number;
        internal_code: string;
        plate_number: string | null;
        status: string;
    }>;
    stats: {
        total_ambulances: number;
        available_ambulances: number;
        ambulances_in_transfer: number;
        ambulances_in_maintenance: number;
        active_transfers: number;
        requested_transfers: number;
        completed_today: number;
    };
    filters: {
        ambulance_status?: string;
        ambulance_search?: string;
        transfer_status?: string;
    };
}

const props = defineProps<Props>();

const filters = ref({
    ambulance_status: props.filters.ambulance_status || "",
    ambulance_search: props.filters.ambulance_search || "",
    transfer_status: props.filters.transfer_status || "",
});

const ambulanceForm = useForm({
    internal_code: "",
    plate_number: "",
    brand: "",
    model: "",
    year: "",
    current_mileage: "",
    base_location: "",
    status: "available",
    notes: "",
});

const transferForm = useForm({
    patient_id: "",
    ambulance_id: "",
    origin: "",
    destination: "",
    transfer_type: "emergency",
    priority: "high",
    clinical_summary: "",
});

const ambulanceStatus = ref<Record<number, string>>({});
const transferStatus = ref<Record<number, string>>({});
const transferAmbulance = ref<Record<number, string>>({});

props.ambulances.data.forEach((ambulance) => {
    ambulanceStatus.value[ambulance.id] = ambulance.status;
});

props.transfers.data.forEach((transfer) => {
    transferStatus.value[transfer.id] = transfer.status;
    transferAmbulance.value[transfer.id] =
        transfer.ambulance?.id?.toString() || "";
});

const applyFilters = () => {
    router.get(route("paramedic.dashboard"), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = {
        ambulance_status: "",
        ambulance_search: "",
        transfer_status: "",
    };
    applyFilters();
};

const submitAmbulance = () => {
    ambulanceForm.post(route("paramedic.ambulances.store"), {
        preserveScroll: true,
        onSuccess: () => ambulanceForm.reset(),
    });
};

const submitTransfer = () => {
    transferForm.post(route("paramedic.transfers.store"), {
        preserveScroll: true,
        onSuccess: () =>
            transferForm.reset("origin", "destination", "clinical_summary"),
    });
};

const updateAmbulanceStatus = (ambulanceId: number) => {
    router.patch(
        route("paramedic.ambulances.status", ambulanceId),
        {
            status: ambulanceStatus.value[ambulanceId],
        },
        {
            preserveScroll: true,
        },
    );
};

const updateTransferStatus = (transferId: number) => {
    router.patch(
        route("paramedic.transfers.status", transferId),
        {
            status: transferStatus.value[transferId],
            ambulance_id: transferAmbulance.value[transferId] || null,
        },
        {
            preserveScroll: true,
        },
    );
};

const statusBadgeClass = (status: string) => {
    const map: Record<string, string> = {
        available: "bg-green-100 text-green-800",
        in_transfer: "bg-blue-100 text-blue-800",
        maintenance: "bg-amber-100 text-amber-800",
        out_of_service: "bg-red-100 text-red-800",
        requested: "bg-amber-100 text-amber-800",
        assigned: "bg-blue-100 text-blue-800",
        in_progress: "bg-indigo-100 text-indigo-800",
        completed: "bg-green-100 text-green-800",
        cancelled: "bg-rose-100 text-rose-800",
    };

    return map[status] || "bg-gray-100 text-gray-800";
};

const statusLabel = (status: string) => {
    const map: Record<string, string> = {
        available: "Disponible",
        in_transfer: "En traslado",
        maintenance: "Mantenimiento",
        out_of_service: "Fuera de servicio",
        requested: "Solicitado",
        assigned: "Asignado",
        in_progress: "En curso",
        completed: "Completado",
        cancelled: "Cancelado",
    };

    return map[status] || status;
};

const priorityClass = (priority: string) => {
    const map: Record<string, string> = {
        low: "text-slate-700",
        medium: "text-blue-700",
        high: "text-orange-700",
        critical: "text-red-700",
    };

    return map[priority] || "text-slate-700";
};
</script>

<template>
    <Head title="Paramedico y Ambulancias" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">
                    Paramedico / Ambulancias
                </h1>
                <button
                    class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700"
                    @click="applyFilters"
                >
                    Actualizar
                </button>
            </div>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-7">
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-xs text-gray-500">Moviles totales</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">
                        {{ stats.total_ambulances }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-xs text-gray-500">Disponibles</p>
                    <p class="mt-1 text-2xl font-bold text-green-700">
                        {{ stats.available_ambulances }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-xs text-gray-500">En traslado</p>
                    <p class="mt-1 text-2xl font-bold text-blue-700">
                        {{ stats.ambulances_in_transfer }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-xs text-gray-500">En mantenimiento</p>
                    <p class="mt-1 text-2xl font-bold text-amber-700">
                        {{ stats.ambulances_in_maintenance }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-xs text-gray-500">Traslados activos</p>
                    <p class="mt-1 text-2xl font-bold text-indigo-700">
                        {{ stats.active_transfers }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-xs text-gray-500">Solicitudes pendientes</p>
                    <p class="mt-1 text-2xl font-bold text-orange-700">
                        {{ stats.requested_transfers }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-xs text-gray-500">Completados hoy</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-700">
                        {{ stats.completed_today }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">
                        Registrar movil
                    </h2>
                    <form
                        class="grid grid-cols-1 gap-3 md:grid-cols-2"
                        @submit.prevent="submitAmbulance"
                    >
                        <input
                            v-model="ambulanceForm.internal_code"
                            class="rounded-md border-gray-300 text-sm"
                            placeholder="Codigo interno"
                            required
                        />
                        <input
                            v-model="ambulanceForm.plate_number"
                            class="rounded-md border-gray-300 text-sm"
                            placeholder="Patente"
                        />
                        <input
                            v-model="ambulanceForm.brand"
                            class="rounded-md border-gray-300 text-sm"
                            placeholder="Marca"
                        />
                        <input
                            v-model="ambulanceForm.model"
                            class="rounded-md border-gray-300 text-sm"
                            placeholder="Modelo"
                        />
                        <input
                            v-model="ambulanceForm.year"
                            type="number"
                            class="rounded-md border-gray-300 text-sm"
                            placeholder="Año"
                        />
                        <input
                            v-model="ambulanceForm.current_mileage"
                            type="number"
                            class="rounded-md border-gray-300 text-sm"
                            placeholder="Kilometraje"
                        />
                        <input
                            v-model="ambulanceForm.base_location"
                            class="rounded-md border-gray-300 text-sm"
                            placeholder="Base operativa"
                        />
                        <select
                            v-model="ambulanceForm.status"
                            class="rounded-md border-gray-300 text-sm"
                        >
                            <option value="available">Disponible</option>
                            <option value="in_transfer">En traslado</option>
                            <option value="maintenance">Mantenimiento</option>
                            <option value="out_of_service">
                                Fuera de servicio
                            </option>
                        </select>
                        <input
                            v-model="ambulanceForm.notes"
                            class="rounded-md border-gray-300 text-sm md:col-span-2"
                            placeholder="Notas"
                        />
                        <div class="md:col-span-2">
                            <button
                                class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700"
                                :disabled="ambulanceForm.processing"
                            >
                                Guardar movil
                            </button>
                        </div>
                    </form>
                </div>

                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">
                        Solicitar traslado
                    </h2>
                    <form
                        class="grid grid-cols-1 gap-3"
                        @submit.prevent="submitTransfer"
                    >
                        <select
                            v-model="transferForm.patient_id"
                            class="rounded-md border-gray-300 text-sm"
                        >
                            <option value="">
                                Paciente externo / no registrado
                            </option>
                            <option
                                v-for="patient in patients"
                                :key="patient.id"
                                :value="patient.id"
                            >
                                {{ patient.full_name }} ({{ patient.dni }})
                            </option>
                        </select>
                        <div class="grid grid-cols-2 gap-3">
                            <input
                                v-model="transferForm.origin"
                                class="rounded-md border-gray-300 text-sm"
                                placeholder="Origen"
                                required
                            />
                            <input
                                v-model="transferForm.destination"
                                class="rounded-md border-gray-300 text-sm"
                                placeholder="Destino"
                                required
                            />
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <select
                                v-model="transferForm.transfer_type"
                                class="rounded-md border-gray-300 text-sm"
                            >
                                <option value="emergency">Emergencia</option>
                                <option value="scheduled">Programado</option>
                                <option value="interhospital">
                                    Interhospitalario
                                </option>
                                <option value="discharge">Alta</option>
                            </select>
                            <select
                                v-model="transferForm.priority"
                                class="rounded-md border-gray-300 text-sm"
                            >
                                <option value="low">Baja</option>
                                <option value="medium">Media</option>
                                <option value="high">Alta</option>
                                <option value="critical">Critica</option>
                            </select>
                            <select
                                v-model="transferForm.ambulance_id"
                                class="rounded-md border-gray-300 text-sm"
                            >
                                <option value="">Sin asignar</option>
                                <option
                                    v-for="ambulance in available_ambulances"
                                    :key="ambulance.id"
                                    :value="ambulance.id"
                                >
                                    {{ ambulance.internal_code }}
                                    {{
                                        ambulance.plate_number
                                            ? `(${ambulance.plate_number})`
                                            : ""
                                    }}
                                </option>
                            </select>
                        </div>
                        <textarea
                            v-model="transferForm.clinical_summary"
                            rows="2"
                            class="rounded-md border-gray-300 text-sm"
                            placeholder="Resumen clinico"
                        ></textarea>
                        <button
                            class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-600"
                            :disabled="transferForm.processing"
                        >
                            Crear traslado
                        </button>
                    </form>
                </div>
            </div>

            <div class="rounded-lg bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">
                    Filtros
                </h2>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                    <input
                        v-model="filters.ambulance_search"
                        class="rounded-md border-gray-300 text-sm"
                        placeholder="Buscar movil"
                    />
                    <select
                        v-model="filters.ambulance_status"
                        class="rounded-md border-gray-300 text-sm"
                    >
                        <option value="">Estado movil: todos</option>
                        <option value="available">Disponible</option>
                        <option value="in_transfer">En traslado</option>
                        <option value="maintenance">Mantenimiento</option>
                        <option value="out_of_service">
                            Fuera de servicio
                        </option>
                    </select>
                    <select
                        v-model="filters.transfer_status"
                        class="rounded-md border-gray-300 text-sm"
                    >
                        <option value="">Estado traslado: todos</option>
                        <option value="requested">Solicitado</option>
                        <option value="assigned">Asignado</option>
                        <option value="in_progress">En curso</option>
                        <option value="completed">Completado</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                    <div class="flex gap-2">
                        <button
                            class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-600"
                            @click="applyFilters"
                        >
                            Aplicar
                        </button>
                        <button
                            class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-300"
                            @click="clearFilters"
                        >
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 px-5 py-3">
                    <h2 class="text-lg font-semibold text-gray-900">Moviles</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">Codigo</th>
                                <th class="px-4 py-3 text-left">Patente</th>
                                <th class="px-4 py-3 text-left">Unidad</th>
                                <th class="px-4 py-3 text-left">Base</th>
                                <th class="px-4 py-3 text-left">Estado</th>
                                <th class="px-4 py-3 text-left">Accion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="ambulance in ambulances.data"
                                :key="ambulance.id"
                            >
                                <td class="px-4 py-3 font-semibold">
                                    {{ ambulance.internal_code }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ ambulance.plate_number || "-" }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ ambulance.brand || "" }}
                                    {{ ambulance.model || "" }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ ambulance.base_location || "-" }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'rounded-full px-2 py-1 text-xs font-semibold',
                                            statusBadgeClass(ambulance.status),
                                        ]"
                                    >
                                        {{ statusLabel(ambulance.status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <select
                                            v-model="
                                                ambulanceStatus[ambulance.id]
                                            "
                                            class="rounded-md border-gray-300 text-xs"
                                        >
                                            <option value="available">
                                                Disponible
                                            </option>
                                            <option value="in_transfer">
                                                En traslado
                                            </option>
                                            <option value="maintenance">
                                                Mantenimiento
                                            </option>
                                            <option value="out_of_service">
                                                Fuera de servicio
                                            </option>
                                        </select>
                                        <button
                                            class="rounded bg-slate-800 px-2 py-1 text-xs font-semibold text-white"
                                            @click="
                                                updateAmbulanceStatus(
                                                    ambulance.id,
                                                )
                                            "
                                        >
                                            Guardar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="ambulances.data.length === 0">
                                <td
                                    class="px-4 py-6 text-center text-gray-500"
                                    colspan="6"
                                >
                                    No hay moviles registrados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div
                    class="flex flex-wrap gap-2 border-t border-gray-200 px-4 py-3"
                >
                    <a
                        v-for="link in ambulances.links"
                        :key="`amb-${link.label}`"
                        :href="link.url || '#'"
                        :class="[
                            'rounded px-3 py-1 text-sm',
                            link.active
                                ? 'bg-slate-800 text-white'
                                : 'bg-gray-200 text-gray-800',
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>

            <div class="rounded-lg bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 px-5 py-3">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Traslados
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">Paciente</th>
                                <th class="px-4 py-3 text-left">Ruta</th>
                                <th class="px-4 py-3 text-left">
                                    Tipo / Prioridad
                                </th>
                                <th class="px-4 py-3 text-left">Movil</th>
                                <th class="px-4 py-3 text-left">Estado</th>
                                <th class="px-4 py-3 text-left">Accion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="transfer in transfers.data"
                                :key="transfer.id"
                            >
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-900">
                                        {{
                                            transfer.patient?.full_name ||
                                            "Paciente externo"
                                        }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ transfer.patient?.dni || "Sin DNI" }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <p>{{ transfer.origin }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ transfer.destination }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="capitalize">
                                        {{ transfer.transfer_type }}
                                    </p>
                                    <p
                                        :class="[
                                            'text-xs font-semibold uppercase',
                                            priorityClass(transfer.priority),
                                        ]"
                                    >
                                        {{ transfer.priority }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <select
                                        v-model="transferAmbulance[transfer.id]"
                                        class="rounded-md border-gray-300 text-xs"
                                    >
                                        <option value="">Sin movil</option>
                                        <option
                                            v-if="transfer.ambulance"
                                            :value="transfer.ambulance.id"
                                        >
                                            {{
                                                transfer.ambulance.internal_code
                                            }}
                                        </option>
                                        <option
                                            v-for="ambulance in available_ambulances"
                                            :key="ambulance.id"
                                            :value="ambulance.id"
                                        >
                                            {{ ambulance.internal_code }}
                                        </option>
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'rounded-full px-2 py-1 text-xs font-semibold',
                                            statusBadgeClass(transfer.status),
                                        ]"
                                    >
                                        {{ statusLabel(transfer.status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <select
                                            v-model="
                                                transferStatus[transfer.id]
                                            "
                                            class="rounded-md border-gray-300 text-xs"
                                        >
                                            <option value="requested">
                                                Solicitado
                                            </option>
                                            <option value="assigned">
                                                Asignado
                                            </option>
                                            <option value="in_progress">
                                                En curso
                                            </option>
                                            <option value="completed">
                                                Completado
                                            </option>
                                            <option value="cancelled">
                                                Cancelado
                                            </option>
                                        </select>
                                        <button
                                            class="rounded bg-blue-700 px-2 py-1 text-xs font-semibold text-white"
                                            @click="
                                                updateTransferStatus(
                                                    transfer.id,
                                                )
                                            "
                                        >
                                            Guardar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="transfers.data.length === 0">
                                <td
                                    class="px-4 py-6 text-center text-gray-500"
                                    colspan="6"
                                >
                                    No hay traslados registrados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div
                    class="flex flex-wrap gap-2 border-t border-gray-200 px-4 py-3"
                >
                    <a
                        v-for="link in transfers.links"
                        :key="`tr-${link.label}`"
                        :href="link.url || '#'"
                        :class="[
                            'rounded px-3 py-1 text-sm',
                            link.active
                                ? 'bg-slate-800 text-white'
                                : 'bg-gray-200 text-gray-800',
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
