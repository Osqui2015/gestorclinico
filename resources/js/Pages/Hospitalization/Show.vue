<script setup lang="ts">
import { ref } from "vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
    phone: string | null;
    email: string | null;
}

interface Doctor {
    id: number;
    name: string;
    specialty: string | null;
}

interface Bed {
    id: number;
    bed_number: string;
    status: string;
    bed_type: string;
    observations: string | null;
    full_name: string;
    room: {
        id: number;
        name: string;
        code: string;
        floor: number | null;
        wing: string | null;
    };
    currentHospitalization: Hospitalization | null;
    hospitalizations: Hospitalization[];
    cleaningLogs: CleaningLog[];
}

interface Hospitalization {
    id: number;
    patient_id: number;
    bed_id: number;
    doctor_id: number;
    operation_id: number | null;
    admission_date: string;
    expected_discharge_date: string | null;
    actual_discharge_date: string | null;
    admission_reason: string;
    admission_type: string;
    status: string;
    discharge_notes: string | null;
    diagnosis: string | null;
    treatment: string | null;
    daily_observations: string | null;
    days_hospitalized: number;
    patient: Patient;
    doctor: Doctor;
    dischargeAuthorizer?: Doctor;
}

interface CleaningLog {
    id: number;
    completed_at: string;
    cleaning_type: string;
    notes: string | null;
    duration_in_minutes: number | null;
    cleaner: {
        id: number;
        name: string;
    };
}

interface Props {
    bed: Bed;
    canManage: boolean;
}

const props = defineProps<Props>();

const activeTab = ref<"current" | "history" | "cleaning">("current");

// Formulario para actualizar fecha de alta
const dischargeDateForm = useForm({
    expected_discharge_date:
        props.bed.currentHospitalization?.expected_discharge_date || "",
    discharge_notes: props.bed.currentHospitalization?.discharge_notes || "",
});

// Formulario para dar alta
const dischargeForm = useForm({
    discharge_notes: "",
});

// Formulario para transferencia
const transferForm = useForm({
    new_bed_id: "" as number | "",
    transfer_reason: "",
});

// Formulario para observaciones diarias
const observationsForm = useForm({
    daily_observations:
        props.bed.currentHospitalization?.daily_observations || "",
});

// Formulario para limpieza
const cleaningForm = useForm({
    cleaning_type: "discharge",
    notes: "",
});

const updateDischargeDate = () => {
    dischargeDateForm.post(
        route(
            "hospitalizations.hospitalizations.update-discharge-date",
            props.bed.currentHospitalization!.id,
        ),
        {
            preserveScroll: true,
            onSuccess: () => {
                alert("Fecha de alta actualizada exitosamente");
            },
        },
    );
};

const dischargePatient = () => {
    if (
        !confirm(
            "¿Está seguro de dar el alta al paciente? La cama quedará marcada como pendiente de limpieza.",
        )
    ) {
        return;
    }

    dischargeForm.post(
        route(
            "hospitalizations.hospitalizations.discharge",
            props.bed.currentHospitalization!.id,
        ),
        {
            preserveScroll: true,
        },
    );
};

const updateObservations = () => {
    observationsForm.post(
        route(
            "hospitalizations.hospitalizations.update-observations",
            props.bed.currentHospitalization!.id,
        ),
        {
            preserveScroll: true,
            onSuccess: () => {
                alert("Observaciones actualizadas exitosamente");
            },
        },
    );
};

const startCleaning = () => {
    router.post(
        route("hospitalizations.beds.start-cleaning", props.bed.id),
        {},
        {
            preserveScroll: true,
        },
    );
};

const markCleaned = () => {
    cleaningForm.post(
        route("hospitalizations.beds.mark-cleaned", props.bed.id),
        {
            preserveScroll: true,
        },
    );
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

const getAdmissionTypeLabel = (type: string): string => {
    const labels: Record<string, string> = {
        emergency: "Emergencia",
        scheduled: "Programada",
        post_surgical: "Post-quirúrgica",
        transfer: "Transferencia",
    };
    return labels[type] || type;
};

const getCleaningTypeLabel = (type: string): string => {
    const labels: Record<string, string> = {
        routine: "Rutina",
        deep: "Profunda",
        discharge: "Post-alta",
        disinfection: "Desinfección",
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

const formatDate = (value: string | null): string => {
    if (!value) return "-";
    return new Date(value).toLocaleDateString("es-AR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
};

const canDischarge = (): boolean => {
    const hosp = props.bed.currentHospitalization;
    if (!hosp || !hosp.expected_discharge_date) return false;
    const expectedDate = new Date(hosp.expected_discharge_date);
    return expectedDate <= new Date();
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Cama ${bed.full_name}`" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <a
                                :href="route('hospitalizations.index')"
                                class="text-sm text-indigo-600 hover:text-indigo-900 mb-2 inline-block"
                            >
                                ← Volver al listado
                            </a>
                            <h2 class="text-2xl font-bold text-gray-900">
                                🛏️ {{ bed.full_name }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ bed.room.name }} - Piso
                                {{ bed.room.floor }} -
                                {{ getBedTypeLabel(bed.bed_type) }}
                            </p>
                        </div>
                        <div>
                            <span
                                :class="getStatusClass(bed.status)"
                                class="px-3 py-1 text-sm font-semibold rounded-full"
                            >
                                {{ getStatusLabel(bed.status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="-mb-px flex space-x-8">
                        <button
                            @click="activeTab = 'current'"
                            :class="[
                                activeTab === 'current'
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                            ]"
                        >
                            Internación Actual
                        </button>
                        <button
                            @click="activeTab = 'history'"
                            :class="[
                                activeTab === 'history'
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                            ]"
                        >
                            Historial ({{ bed.hospitalizations.length }})
                        </button>
                        <button
                            @click="activeTab = 'cleaning'"
                            :class="[
                                activeTab === 'cleaning'
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                            ]"
                        >
                            Limpiezas ({{ bed.cleaningLogs.length }})
                        </button>
                    </nav>
                </div>

                <!-- Current Hospitalization Tab -->
                <div v-if="activeTab === 'current'">
                    <div v-if="bed.currentHospitalization" class="space-y-6">
                        <!-- Patient Info -->
                        <div class="bg-white shadow rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                👤 Información del Paciente
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-500"
                                        >Nombre Completo</label
                                    >
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{
                                            bed.currentHospitalization.patient
                                                .first_name
                                        }}
                                        {{
                                            bed.currentHospitalization.patient
                                                .last_name
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-500"
                                        >DNI</label
                                    >
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{
                                            bed.currentHospitalization.patient
                                                .dni
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-500"
                                        >Teléfono</label
                                    >
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{
                                            bed.currentHospitalization.patient
                                                .phone || "-"
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-500"
                                        >Email</label
                                    >
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{
                                            bed.currentHospitalization.patient
                                                .email || "-"
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Hospitalization Info -->
                        <div class="bg-white shadow rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                🏥 Información de Internación
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-500"
                                        >Médico Responsable</label
                                    >
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{
                                            bed.currentHospitalization.doctor
                                                .name
                                        }}
                                        <span
                                            v-if="
                                                bed.currentHospitalization
                                                    .doctor.specialty
                                            "
                                            class="text-gray-500"
                                        >
                                            ({{
                                                bed.currentHospitalization
                                                    .doctor.specialty
                                            }})
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-500"
                                        >Tipo de Admisión</label
                                    >
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{
                                            getAdmissionTypeLabel(
                                                bed.currentHospitalization
                                                    .admission_type,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-500"
                                        >Fecha de Ingreso</label
                                    >
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{
                                            formatDateTime(
                                                bed.currentHospitalization
                                                    .admission_date,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-500"
                                        >Días Internado</label
                                    >
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{
                                            bed.currentHospitalization
                                                .days_hospitalized
                                        }}
                                        días
                                    </p>
                                </div>
                                <div class="md:col-span-2">
                                    <label
                                        class="block text-sm font-medium text-gray-500"
                                        >Motivo de Internación</label
                                    >
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{
                                            bed.currentHospitalization
                                                .admission_reason
                                        }}
                                    </p>
                                </div>
                                <div
                                    v-if="bed.currentHospitalization.diagnosis"
                                    class="md:col-span-2"
                                >
                                    <label
                                        class="block text-sm font-medium text-gray-500"
                                        >Diagnóstico</label
                                    >
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{
                                            bed.currentHospitalization.diagnosis
                                        }}
                                    </p>
                                </div>
                                <div
                                    v-if="bed.currentHospitalization.treatment"
                                    class="md:col-span-2"
                                >
                                    <label
                                        class="block text-sm font-medium text-gray-500"
                                        >Tratamiento</label
                                    >
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{
                                            bed.currentHospitalization.treatment
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Daily Observations (Editable) -->
                        <div
                            v-if="canManage"
                            class="bg-white shadow rounded-lg p-6"
                        >
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                📝 Observaciones Diarias
                            </h3>
                            <form @submit.prevent="updateObservations">
                                <textarea
                                    v-model="
                                        observationsForm.daily_observations
                                    "
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Ingrese observaciones sobre el estado del paciente..."
                                ></textarea>
                                <div class="mt-3">
                                    <button
                                        type="submit"
                                        :disabled="observationsForm.processing"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50"
                                    >
                                        Guardar Observaciones
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Discharge Management -->
                        <div
                            v-if="canManage"
                            class="bg-white shadow rounded-lg p-6"
                        >
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                📅 Gestión de Alta
                            </h3>

                            <!-- Update Expected Discharge Date -->
                            <form
                                @submit.prevent="updateDischargeDate"
                                class="mb-6"
                            >
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                >
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Fecha Estimada de Alta</label
                                        >
                                        <input
                                            v-model="
                                                dischargeDateForm.expected_discharge_date
                                            "
                                            type="date"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Notas de Alta</label
                                        >
                                        <input
                                            v-model="
                                                dischargeDateForm.discharge_notes
                                            "
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            placeholder="Opcional..."
                                        />
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button
                                        type="submit"
                                        :disabled="dischargeDateForm.processing"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
                                    >
                                        Actualizar Fecha de Alta
                                    </button>
                                </div>
                            </form>

                            <!-- Discharge Patient -->
                            <div v-if="canDischarge()" class="border-t pt-6">
                                <div class="bg-green-50 p-4 rounded-md mb-4">
                                    <p class="text-sm text-green-800">
                                        ✓ El paciente está listo para el alta
                                        según la fecha estimada.
                                    </p>
                                </div>
                                <form @submit.prevent="dischargePatient">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Notas Finales de Alta</label
                                        >
                                        <textarea
                                            v-model="
                                                dischargeForm.discharge_notes
                                            "
                                            rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            placeholder="Observaciones finales del alta..."
                                        ></textarea>
                                    </div>
                                    <div class="mt-3">
                                        <button
                                            type="submit"
                                            :disabled="dischargeForm.processing"
                                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:opacity-50"
                                        >
                                            ✓ Dar Alta al Paciente
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State - No Current Hospitalization -->
                    <div v-else class="bg-white shadow rounded-lg p-12">
                        <div class="text-center">
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
                                Cama Disponible
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Esta cama no tiene un paciente internado
                                actualmente.
                            </p>
                            <div class="mt-6">
                                <a
                                    v-if="bed.status === 'available'"
                                    :href="
                                        route('hospitalizations.create', {
                                            bed_id: bed.id,
                                        })
                                    "
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                                >
                                    Registrar Nueva Internación
                                </a>
                                <div
                                    v-else-if="
                                        bed.status === 'pending_cleaning'
                                    "
                                    class="space-y-3"
                                >
                                    <p
                                        class="text-sm text-yellow-600 font-medium"
                                    >
                                        ⚠️ La cama necesita limpieza antes de
                                        ser utilizada
                                    </p>
                                    <button
                                        v-if="canManage"
                                        @click="startCleaning"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
                                    >
                                        Iniciar Limpieza
                                    </button>
                                </div>
                                <div
                                    v-else-if="bed.status === 'cleaning'"
                                    class="space-y-3"
                                >
                                    <p
                                        class="text-sm text-blue-600 font-medium"
                                    >
                                        🧹 Limpieza en proceso...
                                    </p>
                                    <form
                                        v-if="canManage"
                                        @submit.prevent="markCleaned"
                                        class="max-w-md mx-auto"
                                    >
                                        <div class="space-y-3">
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-gray-700"
                                                    >Tipo de Limpieza</label
                                                >
                                                <select
                                                    v-model="
                                                        cleaningForm.cleaning_type
                                                    "
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                >
                                                    <option value="routine">
                                                        Rutina
                                                    </option>
                                                    <option value="deep">
                                                        Profunda
                                                    </option>
                                                    <option value="discharge">
                                                        Post-alta
                                                    </option>
                                                    <option
                                                        value="disinfection"
                                                    >
                                                        Desinfección
                                                    </option>
                                                </select>
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-gray-700"
                                                    >Notas</label
                                                >
                                                <input
                                                    v-model="cleaningForm.notes"
                                                    type="text"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    placeholder="Opcional..."
                                                />
                                            </div>
                                            <button
                                                type="submit"
                                                :disabled="
                                                    cleaningForm.processing
                                                "
                                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:opacity-50"
                                            >
                                                ✓ Marcar como Limpia
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Tab -->
                <div
                    v-if="activeTab === 'history'"
                    class="bg-white shadow rounded-lg overflow-hidden"
                >
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            📋 Historial de Internaciones
                        </h3>
                    </div>
                    <div
                        v-if="bed.hospitalizations.length > 0"
                        class="divide-y divide-gray-200"
                    >
                        <div
                            v-for="hosp in bed.hospitalizations"
                            :key="hosp.id"
                            class="p-6 hover:bg-gray-50"
                        >
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h4
                                            class="text-base font-medium text-gray-900"
                                        >
                                            {{ hosp.patient.first_name }}
                                            {{ hosp.patient.last_name }}
                                        </h4>
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800"
                                        >
                                            {{ hosp.patient.dni }}
                                        </span>
                                    </div>
                                    <div
                                        class="grid grid-cols-2 gap-4 text-sm text-gray-600"
                                    >
                                        <div>
                                            <span class="font-medium"
                                                >Ingreso:</span
                                            >
                                            {{
                                                formatDateTime(
                                                    hosp.admission_date,
                                                )
                                            }}
                                        </div>
                                        <div>
                                            <span class="font-medium"
                                                >Alta:</span
                                            >
                                            {{
                                                hosp.actual_discharge_date
                                                    ? formatDateTime(
                                                          hosp.actual_discharge_date,
                                                      )
                                                    : "Activo"
                                            }}
                                        </div>
                                        <div>
                                            <span class="font-medium"
                                                >Médico:</span
                                            >
                                            {{ hosp.doctor.name }}
                                        </div>
                                        <div>
                                            <span class="font-medium"
                                                >Días:</span
                                            >
                                            {{ hosp.days_hospitalized }} días
                                        </div>
                                        <div class="col-span-2">
                                            <span class="font-medium"
                                                >Motivo:</span
                                            >
                                            {{ hosp.admission_reason }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="p-12 text-center text-gray-500">
                        No hay historial de internaciones para esta cama.
                    </div>
                </div>

                <!-- Cleaning Tab -->
                <div
                    v-if="activeTab === 'cleaning'"
                    class="bg-white shadow rounded-lg overflow-hidden"
                >
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            🧹 Historial de Limpiezas
                        </h3>
                    </div>
                    <div
                        v-if="bed.cleaningLogs.length > 0"
                        class="divide-y divide-gray-200"
                    >
                        <div
                            v-for="log in bed.cleaningLogs"
                            :key="log.id"
                            class="p-6"
                        >
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800"
                                        >
                                            {{
                                                getCleaningTypeLabel(
                                                    log.cleaning_type,
                                                )
                                            }}
                                        </span>
                                        <span class="text-sm text-gray-600">
                                            {{
                                                formatDateTime(log.completed_at)
                                            }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium"
                                            >Realizado por:</span
                                        >
                                        {{ log.cleaner.name }}
                                    </p>
                                    <p
                                        v-if="log.duration_in_minutes"
                                        class="text-sm text-gray-600"
                                    >
                                        <span class="font-medium"
                                            >Duración:</span
                                        >
                                        {{ log.duration_in_minutes }} minutos
                                    </p>
                                    <p
                                        v-if="log.notes"
                                        class="text-sm text-gray-600 mt-2"
                                    >
                                        <span class="font-medium">Notas:</span>
                                        {{ log.notes }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="p-12 text-center text-gray-500">
                        No hay historial de limpiezas para esta cama.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
