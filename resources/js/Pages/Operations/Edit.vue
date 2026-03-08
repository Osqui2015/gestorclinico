<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";

interface Room {
    id: number;
    name: string;
    code: string;
    status: string;
}

interface Doctor {
    id: number;
    name: string;
    specialty?: string;
}

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni?: string;
}

interface PharmacyItem {
    id: number;
    name: string;
    code: string;
    unit_measurement?: string;
    current_stock: number;
}

interface ExistingRequirement {
    id: number;
    pharmacy_item_id?: number | null;
    requested_item_name?: string;
    quantity_required: number;
    unit_measurement?: string;
    notes?: string;
    pharmacy_item?: {
        id: number;
        name: string;
        unit_measurement?: string;
    };
}

interface OperationData {
    id: number;
    operation_room_id: number;
    doctor_id: number;
    patient_id: number;
    operation_type: string;
    scheduled_start: string;
    estimated_duration_minutes: number;
    urgency: "scheduled" | "urgent" | "emergency";
    status: "scheduled" | "in_progress" | "completed" | "cancelled";
    clinical_notes?: string;
    pharmacy_notes?: string;
    pharmacy_items: ExistingRequirement[];
}

interface RequirementFormItem {
    pharmacy_item_id: number | null;
    requested_item_name: string;
    quantity_required: number;
    unit_measurement: string;
    notes: string;
}

const props = defineProps<{
    operation: OperationData;
    rooms: Room[];
    doctors: Doctor[];
    patients: Patient[];
    pharmacyItems: PharmacyItem[];
    permissions: {
        canManage: boolean;
    };
}>();

const page = usePage();
const currentUser = (page.props as any).auth?.user;
const isDoctor = currentUser?.role === "doctor";

const startDate = props.operation.scheduled_start.slice(0, 10);
const startTime = props.operation.scheduled_start.slice(11, 16);

const existingRequirements: RequirementFormItem[] =
    props.operation.pharmacy_items.length > 0
        ? props.operation.pharmacy_items.map((item) => ({
              pharmacy_item_id:
                  item.pharmacy_item_id ?? item.pharmacy_item?.id ?? null,
              requested_item_name:
                  item.requested_item_name || item.pharmacy_item?.name || "",
              quantity_required: item.quantity_required || 1,
              unit_measurement:
                  item.unit_measurement ||
                  item.pharmacy_item?.unit_measurement ||
                  "",
              notes: item.notes || "",
          }))
        : [];

const form = useForm({
    operation_room_id: props.operation.operation_room_id,
    doctor_id: props.operation.doctor_id,
    patient_id: props.operation.patient_id,
    operation_type: props.operation.operation_type,
    scheduled_date: startDate,
    scheduled_time: startTime,
    estimated_duration_minutes: props.operation.estimated_duration_minutes,
    urgency: props.operation.urgency,
    reschedule_strategy: "none",
    clinical_notes: props.operation.clinical_notes || "",
    pharmacy_notes: props.operation.pharmacy_notes || "",
    pharmacy_items: existingRequirements,
});

const addRequirement = () => {
    form.pharmacy_items.push({
        pharmacy_item_id: null,
        requested_item_name: "",
        quantity_required: 1,
        unit_measurement: "",
        notes: "",
    });
};

const removeRequirement = (index: number) => {
    form.pharmacy_items.splice(index, 1);
};

const onSelectPharmacyItem = (index: number) => {
    const requirement = form.pharmacy_items[index];
    const selectedItem = props.pharmacyItems.find(
        (item) => item.id === requirement.pharmacy_item_id,
    );

    if (!selectedItem) {
        return;
    }

    if (!requirement.requested_item_name) {
        requirement.requested_item_name = selectedItem.name;
    }

    if (!requirement.unit_measurement && selectedItem.unit_measurement) {
        requirement.unit_measurement = selectedItem.unit_measurement;
    }
};

const submit = () => {
    form.transform((data) => ({
        operation_room_id: data.operation_room_id,
        doctor_id: data.doctor_id,
        patient_id: data.patient_id,
        operation_type: data.operation_type,
        scheduled_start: `${data.scheduled_date} ${data.scheduled_time}:00`,
        estimated_duration_minutes: data.estimated_duration_minutes,
        urgency: data.urgency,
        reschedule_strategy:
            data.urgency === "emergency" ? data.reschedule_strategy : "none",
        clinical_notes: data.clinical_notes,
        pharmacy_notes: data.pharmacy_notes,
        pharmacy_items: data.pharmacy_items,
    })).patch(route("operations.update", props.operation.id));
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
</script>

<template>
    <Head :title="`Editar Operación #${operation.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        ✏️ Editar Operación #{{ operation.id }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Estado actual: {{ getStatusLabel(operation.status) }}
                    </p>
                </div>
                <Link
                    :href="route('operations.index')"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    ← Volver a agenda
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">
                            Datos de la operación
                        </h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label
                                    for="operation_room_id"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Sala habilitada
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="operation_room_id"
                                    v-model.number="form.operation_room_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option
                                        v-for="room in rooms"
                                        :key="room.id"
                                        :value="room.id"
                                    >
                                        {{ room.name }} ({{ room.code }})
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.operation_room_id"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.operation_room_id }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="doctor_id"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Médico responsable
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="doctor_id"
                                    v-model.number="form.doctor_id"
                                    required
                                    :disabled="isDoctor"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                                >
                                    <option
                                        v-for="doctor in doctors"
                                        :key="doctor.id"
                                        :value="doctor.id"
                                    >
                                        {{ doctor.name }}
                                        <span v-if="doctor.specialty">
                                            - {{ doctor.specialty }}
                                        </span>
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.doctor_id"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.doctor_id }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="patient_id"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Paciente
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="patient_id"
                                    v-model.number="form.patient_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option
                                        v-for="patient in patients"
                                        :key="patient.id"
                                        :value="patient.id"
                                    >
                                        {{ patient.last_name }},
                                        {{ patient.first_name }}
                                        <span v-if="patient.dni"
                                            >(DNI {{ patient.dni }})</span
                                        >
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.patient_id"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.patient_id }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="operation_type"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Tipo de operación
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="operation_type"
                                    v-model="form.operation_type"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="form.errors.operation_type"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.operation_type }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="scheduled_date"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Fecha
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="scheduled_date"
                                    v-model="form.scheduled_date"
                                    type="date"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            <div>
                                <label
                                    for="scheduled_time"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Hora de inicio
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="scheduled_time"
                                    v-model="form.scheduled_time"
                                    type="time"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            <div>
                                <label
                                    for="estimated_duration_minutes"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Duración estimada (min)
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="estimated_duration_minutes"
                                    v-model.number="
                                        form.estimated_duration_minutes
                                    "
                                    type="number"
                                    min="15"
                                    max="720"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="
                                        form.errors.estimated_duration_minutes
                                    "
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.estimated_duration_minutes }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="urgency"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Prioridad
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="urgency"
                                    v-model="form.urgency"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="scheduled">
                                        Programada
                                    </option>
                                    <option value="urgent">Urgente</option>
                                    <option value="emergency">
                                        Emergencia
                                    </option>
                                </select>
                            </div>

                            <div v-if="form.urgency === 'emergency'">
                                <label
                                    for="reschedule_strategy"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Estrategia de reprogramación
                                </label>
                                <select
                                    id="reschedule_strategy"
                                    v-model="form.reschedule_strategy"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="none">
                                        Sin mover otras operaciones
                                    </option>
                                    <option value="forward">
                                        Atrasar operaciones en conflicto
                                    </option>
                                    <option value="backward">
                                        Adelantar operaciones en conflicto
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.reschedule_strategy"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.reschedule_strategy }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-4">
                            <div>
                                <label
                                    for="clinical_notes"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Notas clínicas
                                </label>
                                <textarea
                                    id="clinical_notes"
                                    v-model="form.clinical_notes"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>
                            <div>
                                <label
                                    for="pharmacy_notes"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Notas para farmacia
                                </label>
                                <textarea
                                    id="pharmacy_notes"
                                    v-model="form.pharmacy_notes"
                                    rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Requerimientos de farmacia
                            </h3>
                            <button
                                type="button"
                                class="rounded-md bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100"
                                @click="addRequirement"
                            >
                                + Agregar insumo
                            </button>
                        </div>

                        <div
                            v-if="form.pharmacy_items.length === 0"
                            class="rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500"
                        >
                            Sin insumos cargados.
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="(item, index) in form.pharmacy_items"
                                :key="index"
                                class="rounded-lg border border-gray-200 p-4"
                            >
                                <div
                                    class="grid grid-cols-1 gap-3 md:grid-cols-5"
                                >
                                    <div class="md:col-span-2">
                                        <label
                                            class="block text-xs font-medium uppercase text-gray-500"
                                        >
                                            Item de farmacia
                                        </label>
                                        <select
                                            v-model.number="
                                                item.pharmacy_item_id
                                            "
                                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            @change="
                                                onSelectPharmacyItem(index)
                                            "
                                        >
                                            <option :value="null">
                                                Manual / Sin catálogo
                                            </option>
                                            <option
                                                v-for="catalogItem in pharmacyItems"
                                                :key="catalogItem.id"
                                                :value="catalogItem.id"
                                            >
                                                {{ catalogItem.name }} ({{
                                                    catalogItem.code
                                                }}) - Stock:
                                                {{ catalogItem.current_stock }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label
                                            class="block text-xs font-medium uppercase text-gray-500"
                                        >
                                            Nombre solicitado
                                        </label>
                                        <input
                                            v-model="item.requested_item_name"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="block text-xs font-medium uppercase text-gray-500"
                                        >
                                            Cantidad
                                        </label>
                                        <input
                                            v-model.number="
                                                item.quantity_required
                                            "
                                            type="number"
                                            min="1"
                                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="block text-xs font-medium uppercase text-gray-500"
                                        >
                                            Unidad
                                        </label>
                                        <input
                                            v-model="item.unit_measurement"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                    </div>

                                    <div class="md:col-span-3">
                                        <label
                                            class="block text-xs font-medium uppercase text-gray-500"
                                        >
                                            Notas
                                        </label>
                                        <input
                                            v-model="item.notes"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                    </div>

                                    <div
                                        class="flex items-end justify-end md:col-span-1"
                                    >
                                        <button
                                            type="button"
                                            class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100"
                                            @click="removeRequirement(index)"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <Link
                            :href="route('operations.index')"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? "Guardando..."
                                    : "Guardar cambios"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
