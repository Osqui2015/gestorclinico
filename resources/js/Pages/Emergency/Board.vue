<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted } from 'vue';
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { echo } from '@/echo';

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
}

interface User {
    id: number;
    name: string;
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
}

interface Props {
    admissions: EmergencyAdmission[];
}

const props = defineProps<Props>();

let emergencyChannel: any = null;

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

const timeInEmergency = (admissionTime: string) => {
    const now = new Date();
    const admission = new Date(admissionTime);
    const diffMs = now.getTime() - admission.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const hours = Math.floor(diffMins / 60);
    const mins = diffMins % 60;
    return hours > 0 ? `${hours}h ${mins}m` : `${mins}m`;
};

const sortedAdmissions = computed(() => {
    return [...props.admissions].sort((a, b) => {
        // Ordenar por triage level (menor número primero = más grave)
        if (a.triage_level !== b.triage_level) {
            return a.triage_level - b.triage_level;
        }
        // Si mismo triage, por antigüedad
        return (
            new Date(a.admission_time).getTime() -
            new Date(b.admission_time).getTime()
        );
    });
});

const stats = computed(() => {
    const waiting = sortedAdmissions.value.filter(
        (a) => a.status === "waiting",
    ).length;
    const inCare = sortedAdmissions.value.filter(
        (a) => a.status === "in_care",
    ).length;
    const observation = sortedAdmissions.value.filter(
        (a) => a.status === "observation",
    ).length;
    const triage1 = sortedAdmissions.value.filter(
        (a) => a.triage_level === 1,
    ).length;
    return { waiting, inCare, observation, triage1 };
});

const createAdmission = () => {
    router.get(route("emergency.create"));
};

const viewDetails = (id: number) => {
    router.get(route("emergency.show", id));
};

const recordEvolution = (id: number) => {
    router.get(route("emergency.evolution", id));
};

const refreshBoard = (): void => {
    router.reload({
        only: ['admissions'],
    });
};

onMounted(() => {
    const echoInstance = echo;

    if (!echoInstance) {
        return;
    }

    emergencyChannel = echoInstance.channel('emergency-board');
    emergencyChannel.listen('.EmergencyBoardUpdated', refreshBoard);
});

onBeforeUnmount(() => {
    const echoInstance = echo;

    if (!echoInstance || !emergencyChannel) {
        return;
    }

    emergencyChannel.stopListening('.EmergencyBoardUpdated');
    echoInstance.leaveChannel('emergency-board');
    emergencyChannel = null;
});
</script>

<template>
    <Head title="Emergencias" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        Tablero de Emergencias
                    </h1>
                    <button
                        @click="createAdmission"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                    >
                        ➕ Nueva Admisión
                    </button>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-4 gap-4">
                    <div class="bg-gray-100 rounded-lg p-4">
                        <p class="text-gray-600 text-sm">Esperando Atención</p>
                        <p class="text-3xl font-bold text-gray-900">
                            {{ stats.waiting }}
                        </p>
                    </div>
                    <div class="bg-blue-100 rounded-lg p-4">
                        <p class="text-gray-600 text-sm">En Atención</p>
                        <p class="text-3xl font-bold text-blue-900">
                            {{ stats.inCare }}
                        </p>
                    </div>
                    <div class="bg-yellow-100 rounded-lg p-4">
                        <p class="text-gray-600 text-sm">En Observación</p>
                        <p class="text-3xl font-bold text-yellow-900">
                            {{ stats.observation }}
                        </p>
                    </div>
                    <div class="bg-red-100 rounded-lg p-4">
                        <p class="text-gray-600 text-sm">
                            Resucitación (Nivel 1)
                        </p>
                        <p class="text-3xl font-bold text-red-900">
                            {{ stats.triage1 }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Admissions Board -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Admisiones Activas
                    </h2>
                </div>

                <div
                    v-if="sortedAdmissions.length === 0"
                    class="px-6 py-8 text-center text-gray-500"
                >
                    <p>No hay admisiones activas en este momento</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Triage
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Paciente
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Motivo
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Vitales
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Tiempo
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Estado
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Médico
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="admission in sortedAdmissions"
                                :key="admission.id"
                                class="hover:bg-gray-50 transition"
                            >
                                <!-- Triage Level Badge -->
                                <td class="px-6 py-4">
                                    <div
                                        :class="[
                                            'px-3 py-2 rounded text-white text-sm font-semibold inline-flex items-center gap-2',
                                            getTriageInfo(
                                                admission.triage_level,
                                            ).color,
                                        ]"
                                    >
                                        <span>{{
                                            getTriageInfo(
                                                admission.triage_level,
                                            ).icon
                                        }}</span>
                                        <span
                                            >Nivel
                                            {{ admission.triage_level }}</span
                                        >
                                    </div>
                                </td>

                                <!-- Patient Name -->
                                <td class="px-6 py-4">
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ admission.patient.first_name }}
                                        {{ admission.patient.last_name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        DNI: {{ admission.patient.dni }}
                                    </div>
                                </td>

                                <!-- Chief Complaint -->
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ admission.chief_complaint }}
                                    </div>
                                </td>

                                <!-- Vitals -->
                                <td class="px-6 py-4">
                                    <div
                                        class="text-xs text-gray-600 space-y-1"
                                    >
                                        <div>
                                            PA: {{ admission.blood_pressure }}
                                        </div>
                                        <div>
                                            FC: {{ admission.heart_rate }} bpm
                                        </div>
                                        <div>
                                            O₂:
                                            {{ admission.oxygen_saturation }}%
                                        </div>
                                        <div>
                                            T: {{ admission.temperature }}°C
                                        </div>
                                    </div>
                                </td>

                                <!-- Time in Emergency -->
                                <td class="px-6 py-4">
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{
                                            timeInEmergency(
                                                admission.admission_time,
                                            )
                                        }}
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium',
                                            getStatusInfo(admission.status)
                                                .color,
                                        ]"
                                    >
                                        {{
                                            getStatusInfo(admission.status)
                                                .label
                                        }}
                                    </span>
                                </td>

                                <!-- Doctor -->
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{
                                            admission.attending_doctor?.name ||
                                            "Sin asignar"
                                        }}
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <button
                                            @click="viewDetails(admission.id)"
                                            class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                                        >
                                            Ver
                                        </button>
                                        <button
                                            @click="
                                                recordEvolution(admission.id)
                                            "
                                            class="text-green-600 hover:text-green-900 text-sm font-medium"
                                        >
                                            Evolución
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
