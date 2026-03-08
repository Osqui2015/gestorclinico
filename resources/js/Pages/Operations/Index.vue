<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { reactive } from "vue";

interface Room {
    id: number;
    name: string;
    code: string;
    status: "active" | "maintenance" | "inactive";
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

interface PharmacyRequirement {
    id: number;
    requested_item_name?: string;
    quantity_required: number;
    unit_measurement?: string;
    pharmacy_item?: {
        id: number;
        name: string;
        code: string;
    };
}

interface Operation {
    id: number;
    operation_room_id: number;
    operation_type: string;
    scheduled_start: string;
    scheduled_end: string;
    estimated_duration_minutes: number;
    urgency: "scheduled" | "urgent" | "emergency";
    status: "scheduled" | "in_progress" | "completed" | "cancelled";
    room: Room;
    doctor: Doctor;
    patient: Patient;
    pharmacy_items: PharmacyRequirement[];
}

interface AvailabilitySlot {
    start: string;
    end: string;
    minutes: number;
}

interface AvailabilityByRoom {
    room_id: number;
    room_name: string;
    room_code: string;
    room_status: string;
    total_free_minutes: number;
    slots: AvailabilitySlot[];
}

const props = defineProps<{
    rooms: Room[];
    operations: Operation[];
    availability: AvailabilityByRoom[];
    filters: {
        date: string;
        room_id?: string | number | null;
        status?: string | null;
    };
    permissions: {
        canManage: boolean;
        canCreate: boolean;
    };
}>();

const filters = reactive({
    date: props.filters.date || new Date().toISOString().slice(0, 10),
    room_id: props.filters.room_id ? String(props.filters.room_id) : "",
    status: props.filters.status || "",
});

const applyFilters = () => {
    router.get(
        route("operations.index"),
        {
            date: filters.date,
            room_id: filters.room_id || undefined,
            status: filters.status || undefined,
        },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};

const clearFilters = () => {
    filters.room_id = "";
    filters.status = "";
    applyFilters();
};

const getUrgencyLabel = (urgency: string) => {
    const map: Record<string, string> = {
        scheduled: "Programada",
        urgent: "Urgente",
        emergency: "Emergencia",
    };
    return map[urgency] || urgency;
};

const getUrgencyClass = (urgency: string) => {
    const map: Record<string, string> = {
        scheduled: "bg-gray-100 text-gray-800",
        urgent: "bg-orange-100 text-orange-800",
        emergency: "bg-red-100 text-red-800",
    };
    return map[urgency] || "bg-gray-100 text-gray-800";
};

const getStatusLabel = (status: string) => {
    const map: Record<string, string> = {
        scheduled: "Programada",
        in_progress: "En curso",
        completed: "Completada",
        cancelled: "Cancelada",
    };
    return map[status] || status;
};

const getStatusClass = (status: string) => {
    const map: Record<string, string> = {
        scheduled: "bg-blue-100 text-blue-800",
        in_progress: "bg-yellow-100 text-yellow-800",
        completed: "bg-green-100 text-green-800",
        cancelled: "bg-red-100 text-red-800",
    };
    return map[status] || "bg-gray-100 text-gray-800";
};

const formatDateTime = (value: string) => {
    return new Date(value).toLocaleString("es-AR", {
        day: "2-digit",
        month: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const formatTimeRange = (start: string, end: string) => {
    const startTime = new Date(start).toLocaleTimeString("es-AR", {
        hour: "2-digit",
        minute: "2-digit",
    });
    const endTime = new Date(end).toLocaleTimeString("es-AR", {
        hour: "2-digit",
        minute: "2-digit",
    });
    return `${startTime} - ${endTime}`;
};

const cancelOperation = (operationId: number) => {
    const reason = prompt("Motivo de cancelación:");

    if (!reason || !reason.trim()) {
        return;
    }

    router.post(
        route("operations.cancel", operationId),
        {
            cancellation_reason: reason.trim(),
        },
        {
            preserveScroll: true,
        },
    );
};

const setQuickStatus = (operationId: number, status: string) => {
    router.post(
        route("operations.quick-status", operationId),
        { status },
        {
            preserveScroll: true,
        },
    );
};

const getRequirementLabel = (requirement: PharmacyRequirement) => {
    const name =
        requirement.pharmacy_item?.name ||
        requirement.requested_item_name ||
        "Insumo";

    const unit = requirement.unit_measurement
        ? ` ${requirement.unit_measurement}`
        : "";

    return `${name} (${requirement.quantity_required}${unit})`;
};
</script>

<template>
    <Head title="Operaciones - Agenda de Quirófanos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        🏥 Agenda de Quirófanos
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Disponibilidad de salas, ocupación por médico y
                        requerimientos de farmacia
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link
                        v-if="permissions.canCreate"
                        :href="route('operations.create')"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    >
                        ➕ Nueva operación
                    </Link>
                    <Link
                        v-if="permissions.canManage"
                        :href="route('operations.rooms.settings')"
                        class="rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100"
                    >
                        ⚙️ Salas
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div>
                            <label
                                for="date"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Fecha
                            </label>
                            <input
                                id="date"
                                v-model="filters.date"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @change="applyFilters"
                            />
                        </div>
                        <div>
                            <label
                                for="room"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Sala
                            </label>
                            <select
                                id="room"
                                v-model="filters.room_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @change="applyFilters"
                            >
                                <option value="">Todas</option>
                                <option
                                    v-for="room in rooms"
                                    :key="room.id"
                                    :value="String(room.id)"
                                >
                                    {{ room.name }} ({{ room.code }})
                                </option>
                            </select>
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
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @change="applyFilters"
                            >
                                <option value="">Todos</option>
                                <option value="scheduled">Programada</option>
                                <option value="in_progress">En curso</option>
                                <option value="completed">Completada</option>
                                <option value="cancelled">Cancelada</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button
                                type="button"
                                class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                                @click="clearFilters"
                            >
                                Limpiar filtros
                            </button>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-5 shadow">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">
                        Salas libres y ventanas disponibles
                    </h3>
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div
                            v-for="roomAvailability in availability"
                            :key="roomAvailability.room_id"
                            class="rounded-lg border border-gray-200 p-4"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-900">
                                        {{ roomAvailability.room_name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ roomAvailability.room_code }}
                                    </p>
                                </div>
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-semibold"
                                    :class="
                                        roomAvailability.total_free_minutes > 0
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-red-100 text-red-800'
                                    "
                                >
                                    {{
                                        roomAvailability.total_free_minutes
                                    }}
                                    min libres
                                </span>
                            </div>
                            <div class="mt-3 space-y-2">
                                <p
                                    v-if="roomAvailability.slots.length === 0"
                                    class="text-sm text-gray-500"
                                >
                                    Sin ventanas libres en el rango 07:00 -
                                    22:00.
                                </p>
                                <div
                                    v-for="(
                                        slot, idx
                                    ) in roomAvailability.slots"
                                    :key="`${roomAvailability.room_id}-${idx}`"
                                    class="rounded bg-green-50 px-3 py-2 text-sm text-green-800"
                                >
                                    {{ slot.start }} - {{ slot.end }}
                                    <span class="text-green-600"
                                        >({{ slot.minutes }} min)</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Operaciones programadas ({{ operations.length }})
                        </h3>
                    </div>

                    <div
                        v-if="operations.length === 0"
                        class="px-6 py-10 text-center"
                    >
                        <p class="text-sm text-gray-500">
                            No hay operaciones registradas para los filtros
                            seleccionados.
                        </p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                    >
                                        Horario
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                    >
                                        Sala
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                    >
                                        Operación
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                    >
                                        Médico / Paciente
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                    >
                                        Farmacia
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                    >
                                        Urgencia
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                    >
                                        Estado
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500"
                                    >
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="operation in operations"
                                    :key="operation.id"
                                    class="align-top hover:bg-gray-50"
                                >
                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        <p class="font-medium">
                                            {{
                                                formatTimeRange(
                                                    operation.scheduled_start,
                                                    operation.scheduled_end,
                                                )
                                            }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{
                                                formatDateTime(
                                                    operation.scheduled_start,
                                                )
                                            }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        <p class="font-medium">
                                            {{ operation.room.name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ operation.room.code }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        <p class="font-medium">
                                            {{ operation.operation_type }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Duración:
                                            {{
                                                operation.estimated_duration_minutes
                                            }}
                                            min
                                        </p>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        <p>
                                            <span class="font-medium"
                                                >Dr/a.</span
                                            >
                                            {{ operation.doctor.name }}
                                        </p>
                                        <p class="text-xs text-gray-600">
                                            {{ operation.patient.first_name }}
                                            {{ operation.patient.last_name }}
                                            <span v-if="operation.patient.dni"
                                                >(DNI
                                                {{
                                                    operation.patient.dni
                                                }})</span
                                            >
                                        </p>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        <p
                                            v-if="
                                                operation.pharmacy_items
                                                    .length === 0
                                            "
                                            class="text-xs text-gray-500"
                                        >
                                            Sin insumos solicitados
                                        </p>
                                        <ul v-else class="space-y-1">
                                            <li
                                                v-for="requirement in operation.pharmacy_items"
                                                :key="requirement.id"
                                                class="text-xs"
                                            >
                                                •
                                                {{
                                                    getRequirementLabel(
                                                        requirement,
                                                    )
                                                }}
                                            </li>
                                        </ul>
                                    </td>
                                    <td class="px-4 py-4 text-sm">
                                        <span
                                            class="rounded-full px-2 py-1 text-xs font-semibold"
                                            :class="
                                                getUrgencyClass(
                                                    operation.urgency,
                                                )
                                            "
                                        >
                                            {{
                                                getUrgencyLabel(
                                                    operation.urgency,
                                                )
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-sm">
                                        <span
                                            class="rounded-full px-2 py-1 text-xs font-semibold"
                                            :class="
                                                getStatusClass(operation.status)
                                            "
                                        >
                                            {{
                                                getStatusLabel(operation.status)
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm">
                                        <div
                                            class="flex flex-col items-end gap-1"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'operations.edit',
                                                        operation.id,
                                                    )
                                                "
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Editar
                                            </Link>
                                            <button
                                                v-if="
                                                    operation.status !==
                                                        'completed' &&
                                                    operation.status !==
                                                        'cancelled'
                                                "
                                                type="button"
                                                class="text-red-600 hover:text-red-800"
                                                @click="
                                                    cancelOperation(
                                                        operation.id,
                                                    )
                                                "
                                            >
                                                Cancelar
                                            </button>
                                            <button
                                                v-if="
                                                    permissions.canManage &&
                                                    operation.status ===
                                                        'scheduled'
                                                "
                                                type="button"
                                                class="text-yellow-700 hover:text-yellow-900"
                                                @click="
                                                    setQuickStatus(
                                                        operation.id,
                                                        'in_progress',
                                                    )
                                                "
                                            >
                                                Marcar en curso
                                            </button>
                                            <button
                                                v-if="
                                                    permissions.canManage &&
                                                    operation.status ===
                                                        'in_progress'
                                                "
                                                type="button"
                                                class="text-green-700 hover:text-green-900"
                                                @click="
                                                    setQuickStatus(
                                                        operation.id,
                                                        'completed',
                                                    )
                                                "
                                            >
                                                Marcar completada
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
