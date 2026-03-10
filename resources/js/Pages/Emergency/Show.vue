<script setup lang="ts">
import { ref } from "vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
    phone: string;
    address: string;
}

interface User {
    id: number;
    name: string;
}

interface EmergencyEvolution {
    id: number;
    recorded_at: string;
    blood_pressure: string;
    heart_rate: number;
    respiratory_rate: number;
    temperature: number;
    oxygen_saturation: number;
    glucose_level: number;
    clinical_notes: string;
    treatment_notes: string;
    medications_given: string;
    recorded_by: User;
}

interface MedicalRecord {
    id: number;
    created_at: string;
    reason: string;
    diagnosis: string;
    treatment: string;
    doctor_name: string;
}

interface EmergencyAdmission {
    id: number;
    admission_time: string;
    triage_time: string;
    discharged_at: string | null;
    triage_level: number;
    chief_complaint: string;
    blood_pressure: string;
    heart_rate: number;
    respiratory_rate: number;
    temperature: number;
    oxygen_saturation: number;
    glucose_level: number;
    consciousness_level: string;
    status: string;
    diagnosis: string | null;
    treatment: string | null;
    discharge_instructions: string | null;
    observations: string | null;
    patient: Patient;
    attending_doctor: User;
    evolutions?: EmergencyEvolution[];
}

interface Props {
    admission: EmergencyAdmission;
    medicalHistory?: MedicalRecord[];
}

const props = withDefaults(defineProps<Props>(), {
    medicalHistory: () => [],
});

const showDischargeModal = ref(false);
const showPrescriptionModal = ref(false);
const showPharmacyModal = ref(false);
const showAdmitModal = ref(false);

const dischargeForm = useForm({
    status: "discharged",
    discharge_diagnosis: "",
    discharge_instructions: "",
    save_to_history: true,
});

const prescriptionForm = useForm({
    medication_name: "",
    dosage: "",
    instructions: "",
    quantity: "",
    duration: "",
});

const triageLevels: Record<
    number,
    { label: string; color: string; icon: string }
> = {
    1: { label: "Resucitación", color: "bg-red-600", icon: "🚨" },
    2: { label: "Emergencia", color: "bg-red-500", icon: "⚠️" },
    3: { label: "Urgencia", color: "bg-yellow-500", icon: "⏱️" },
    4: { label: "Menor", color: "bg-blue-500", icon: "📋" },
    5: { label: "Sin urgencia", color: "bg-green-500", icon: "✓" },
};

const statusLabels: Record<string, { label: string; color: string }> = {
    waiting: { label: "Esperando", color: "bg-gray-100 text-gray-800" },
    in_care: { label: "En atención", color: "bg-blue-100 text-blue-800" },
    observation: {
        label: "En observación",
        color: "bg-yellow-100 text-yellow-800",
    },
    discharged: { label: "Dado de alta", color: "bg-green-100 text-green-800" },
    admitted: { label: "Internado", color: "bg-purple-100 text-purple-800" },
    transferred: { label: "Derivado", color: "bg-orange-100 text-orange-800" },
};

const getTriageInfo = (level: number) =>
    triageLevels[level] || {
        label: "Desconocido",
        color: "bg-gray-500",
        icon: "?",
    };
const getStatusInfo = (status: string) =>
    statusLabels[status] || { label: status, color: "bg-gray-100" };

const goBack = () => {
    router.get(route("emergency.board"));
};

const editAdmission = () => {
    router.get(route("emergency.edit", props.admission.id));
};

const addEvolution = () => {
    router.get(route("emergency.evolution", props.admission.id));
};

const changeStatus = (newStatus: string) => {
    router.patch(
        route("emergency.change-status", props.admission.id),
        { status: newStatus },
        { preserveScroll: true },
    );
};

const submitDischarge = () => {
    dischargeForm.patch(route("emergency.change-status", props.admission.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDischargeModal.value = false;
            dischargeForm.reset();
        },
    });
};

const submitPrescription = () => {
    prescriptionForm.post(
        route("emergency.create-prescription", props.admission.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                showPrescriptionModal.value = false;
                prescriptionForm.reset();
            },
        },
    );
};

const formatDuration = (admissionTime: string, dischargedAt: string | null) => {
    const start = new Date(admissionTime);
    const end = dischargedAt ? new Date(dischargedAt) : new Date();
    const diffMs = end.getTime() - start.getTime();
    const totalMins = Math.max(0, Math.floor(diffMs / 60000));
    const hours = Math.floor(totalMins / 60);
    const mins = totalMins % 60;
    return hours > 0 ? `${hours}h ${mins}m` : `${mins}m`;
};
</script>

<template>
    <Head :title="`Admisión #${admission.id}`" />
    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Admisión de Emergencia #{{ admission.id }}
                    </h1>
                    <p class="text-gray-600 mt-1">
                        {{
                            new Date(admission.admission_time).toLocaleString(
                                "es-AR",
                            )
                        }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <!-- Acciones según estado -->
                    <button
                        v-if="
                            admission.status === 'waiting' ||
                            admission.status === 'in_care'
                        "
                        @click="addEvolution"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm"
                    >
                        ➕ Evolución
                    </button>
                    <button
                        v-if="admission.status === 'waiting'"
                        @click="changeStatus('in_care')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm"
                    >
                        🩺 Iniciar Atención
                    </button>
                    <button
                        v-if="admission.status === 'in_care'"
                        @click="showPrescriptionModal = true"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm"
                    >
                        💊 Receta
                    </button>
                    <button
                        v-if="admission.status === 'in_care'"
                        @click="showDischargeModal = true"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm"
                    >
                        ✅ Dar de Alta
                    </button>
                    <button
                        v-if="admission.status === 'in_care'"
                        @click="changeStatus('observation')"
                        class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition text-sm"
                    >
                        👁️ A Observación
                    </button>
                    <button
                        @click="editAdmission"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition text-sm"
                    >
                        ✏️ Editar
                    </button>
                    <button
                        @click="goBack"
                        class="px-4 py-2 bg-gray-300 text-gray-900 rounded-lg hover:bg-gray-400 transition text-sm"
                    >
                        ← Volver
                    </button>
                </div>
            </div>

            <!-- Status and Triage Badges -->
            <div class="flex gap-4 mb-8">
                <div
                    :class="[
                        'px-4 py-2 rounded-lg text-white text-sm font-semibold inline-flex items-center gap-2',
                        getTriageInfo(admission.triage_level).color,
                    ]"
                >
                    <span>{{
                        getTriageInfo(admission.triage_level).icon
                    }}</span>
                    <span>{{
                        getTriageInfo(admission.triage_level).label
                    }}</span>
                </div>
                <div
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-semibold inline-flex items-center gap-2',
                        getStatusInfo(admission.status).color,
                    ]"
                >
                    {{ getStatusInfo(admission.status).label }}
                </div>
            </div>

            <div class="grid grid-cols-3 gap-6">
                <!-- Patient Info -->
                <div class="col-span-1 bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        Datos del Paciente
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Nombre</p>
                            <p class="font-medium text-gray-900">
                                {{ admission.patient.first_name }}
                                {{ admission.patient.last_name }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">DNI</p>
                            <p class="font-medium text-gray-900">
                                {{ admission.patient.dni }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Teléfono</p>
                            <p class="font-medium text-gray-900">
                                {{ admission.patient.phone }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Dirección</p>
                            <p class="font-medium text-gray-900">
                                {{ admission.patient.address }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Main Admission Details -->
                <div class="col-span-2 space-y-6">
                    <!-- Chief Complaint and Medical Info -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">
                            Información Médica
                        </h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-gray-600">
                                    Motivo de Consulta
                                </p>
                                <p class="font-medium text-gray-900">
                                    {{ admission.chief_complaint }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">
                                    Médico Asignado
                                </p>
                                <p class="font-medium text-gray-900">
                                    {{
                                        admission.attending_doctor?.name ||
                                        "Sin asignar"
                                    }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">
                                    Nivel de Conciencia
                                </p>
                                <p class="font-medium text-gray-900">
                                    {{ admission.consciousness_level }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">
                                    Tiempo en Emergencia
                                </p>
                                <p class="font-medium text-gray-900">
                                    {{
                                        formatDuration(
                                            admission.admission_time,
                                            admission.discharged_at,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Diagnosis and Treatment -->
                        <div
                            class="mt-4 pt-4 border-t border-gray-200 space-y-3"
                        >
                            <div>
                                <p class="text-sm text-gray-600">Diagnóstico</p>
                                <p class="text-gray-900">
                                    {{
                                        admission.diagnosis || "No especificado"
                                    }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Tratamiento</p>
                                <p class="text-gray-900">
                                    {{
                                        admission.treatment || "No especificado"
                                    }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">
                                    Instrucciones de Alta
                                </p>
                                <p class="text-gray-900">
                                    {{
                                        admission.discharge_instructions ||
                                        "No especificado"
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Vital Signs -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">
                            Signos Vitales Iniciales
                        </h2>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-gray-50 p-4 rounded">
                                <p class="text-sm text-gray-600">
                                    Presión Arterial
                                </p>
                                <p class="text-xl font-bold text-gray-900">
                                    {{ admission.blood_pressure }}
                                </p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded">
                                <p class="text-sm text-gray-600">
                                    Frecuencia Cardíaca
                                </p>
                                <p class="text-xl font-bold text-gray-900">
                                    {{ admission.heart_rate }} bpm
                                </p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded">
                                <p class="text-sm text-gray-600">
                                    Frecuencia Respiratoria
                                </p>
                                <p class="text-xl font-bold text-gray-900">
                                    {{ admission.respiratory_rate }} resp/min
                                </p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded">
                                <p class="text-sm text-gray-600">Temperatura</p>
                                <p class="text-xl font-bold text-gray-900">
                                    {{ admission.temperature }}°C
                                </p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded">
                                <p class="text-sm text-gray-600">
                                    Saturación O₂
                                </p>
                                <p class="text-xl font-bold text-gray-900">
                                    {{ admission.oxygen_saturation }}%
                                </p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded">
                                <p class="text-sm text-gray-600">Glucosa</p>
                                <p class="text-xl font-bold text-gray-900">
                                    {{ admission.glucose_level }} mg/dL
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Evolution History -->
            <div class="mt-8 bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    Historial de Evolución
                </h2>
                <div
                    v-if="
                        !admission.evolutions ||
                        admission.evolutions.length === 0
                    "
                    class="text-center text-gray-500 py-8"
                >
                    No hay registros de evolución
                </div>
                <div v-else class="space-y-4">
                    <div
                        v-for="evolution in admission.evolutions"
                        :key="evolution.id"
                        class="border-l-4 border-blue-500 pl-4 py-2"
                    >
                        <div class="flex justify-between items-start mb-2">
                            <p class="font-semibold text-gray-900">
                                {{
                                    new Date(
                                        evolution.recorded_at,
                                    ).toLocaleString("es-AR")
                                }}
                            </p>
                            <p class="text-sm text-gray-600">
                                Registrado por: {{ evolution.recorded_by.name }}
                            </p>
                        </div>
                        <div class="grid grid-cols-6 gap-2 mb-3 text-sm">
                            <div>
                                <p class="text-gray-600">
                                    PA: {{ evolution.blood_pressure }}
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-600">
                                    FC: {{ evolution.heart_rate }} bpm
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-600">
                                    O₂: {{ evolution.oxygen_saturation }}%
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-600">
                                    T: {{ evolution.temperature }}°C
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-600">
                                    RR: {{ evolution.respiratory_rate }}/min
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-600">
                                    Glu: {{ evolution.glucose_level }} mg/dL
                                </p>
                            </div>
                        </div>
                        <p class="text-gray-700 text-sm mb-2">
                            {{ evolution.clinical_notes }}
                        </p>
                        <p
                            v-if="evolution.treatment_notes"
                            class="text-gray-700 text-sm"
                        >
                            Tratamiento: {{ evolution.treatment_notes }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Medical History Section -->
            <div class="mt-8 bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    📋 Historial Médico del Paciente
                </h2>
                <div
                    v-if="medicalHistory.length === 0"
                    class="text-center text-gray-500 py-8"
                >
                    No hay consultas médicas previas registradas
                </div>
                <div v-else class="space-y-4">
                    <div
                        v-for="record in medicalHistory"
                        :key="record.id"
                        class="border-l-4 border-green-500 pl-4 py-3 bg-green-50 rounded"
                    >
                        <div class="flex justify-between items-start mb-2">
                            <p class="font-semibold text-gray-900">
                                {{
                                    new Date(record.created_at).toLocaleString(
                                        "es-AR",
                                    )
                                }}
                            </p>
                            <p class="text-sm text-gray-600">
                                Dr. {{ record.doctor_name }}
                            </p>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="font-semibold text-gray-700"
                                    >Motivo:</span
                                >
                                <span class="text-gray-900">{{
                                    record.reason
                                }}</span>
                            </div>
                            <div>
                                <span class="font-semibold text-gray-700"
                                    >Diagnóstico:</span
                                >
                                <span class="text-gray-900">{{
                                    record.diagnosis
                                }}</span>
                            </div>
                            <div>
                                <span class="font-semibold text-gray-700"
                                    >Tratamiento:</span
                                >
                                <span class="text-gray-900">{{
                                    record.treatment
                                }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modals -->
            <!-- Discharge Modal -->
            <div
                v-if="showDischargeModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
                @click.self="showDischargeModal = false"
            >
                <div
                    class="w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl"
                    @click.stop
                >
                    <h3 class="mb-4 text-xl font-bold text-gray-900">
                        Dar de Alta al Paciente
                    </h3>
                    <form @submit.prevent="submitDischarge" class="space-y-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Diagnóstico de Alta *
                            </label>
                            <textarea
                                v-model="dischargeForm.discharge_diagnosis"
                                rows="3"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Diagnóstico final del paciente"
                            ></textarea>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Instrucciones de Alta *
                            </label>
                            <textarea
                                v-model="dischargeForm.discharge_instructions"
                                rows="4"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Indicaciones para el paciente, medicación, controles, etc."
                            ></textarea>
                        </div>
                        <div class="flex items-center">
                            <input
                                v-model="dischargeForm.save_to_history"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            <label class="ml-2 block text-sm text-gray-700">
                                Guardar en historial médico del paciente
                            </label>
                        </div>
                        <div class="flex justify-end gap-2 pt-4">
                            <button
                                type="button"
                                @click="showDischargeModal = false"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="dischargeForm.processing"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Confirmar Alta
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Prescription Modal -->
            <div
                v-if="showPrescriptionModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
                @click.self="showPrescriptionModal = false"
            >
                <div
                    class="w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl"
                    @click.stop
                >
                    <h3 class="mb-4 text-xl font-bold text-gray-900">
                        Crear Receta
                    </h3>
                    <form
                        @submit.prevent="submitPrescription"
                        class="space-y-4"
                    >
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Medicamento *
                            </label>
                            <input
                                v-model="prescriptionForm.medication_name"
                                type="text"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="Nombre del medicamento"
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Dosis *
                                </label>
                                <input
                                    v-model="prescriptionForm.dosage"
                                    type="text"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    placeholder="Ej: 500mg"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Cantidad
                                </label>
                                <input
                                    v-model="prescriptionForm.quantity"
                                    type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    placeholder="Ej: 1 caja"
                                />
                            </div>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Instrucciones
                            </label>
                            <textarea
                                v-model="prescriptionForm.instructions"
                                rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="Cada 8 horas por vía oral con alimentos"
                            ></textarea>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Duración
                            </label>
                            <input
                                v-model="prescriptionForm.duration"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="Ej: 7 días"
                            />
                        </div>
                        <div class="flex justify-end gap-2 pt-4">
                            <button
                                type="button"
                                @click="showPrescriptionModal = false"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="prescriptionForm.processing"
                                class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-500 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Crear Receta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
