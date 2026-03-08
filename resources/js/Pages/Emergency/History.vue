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

interface EmergencyAdmission {
    id: number;
    admission_time: string;
    discharged_at: string | null;
    triage_level: number;
    chief_complaint: string;
    status: string;
    diagnosis: string | null;
    patient: Patient;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    admissions: {
        data: EmergencyAdmission[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
        links: PaginationLink[];
    };
}

const props = defineProps<Props>();

const triageLevels: Record<number, { label: string; color: string }> = {
    1: { label: "Resucitación", color: "bg-red-100 text-red-800" },
    2: { label: "Emergencia", color: "bg-red-50 text-red-700" },
    3: { label: "Urgencia", color: "bg-yellow-100 text-yellow-800" },
    4: { label: "Menor", color: "bg-blue-100 text-blue-800" },
    5: { label: "Sin urgencia", color: "bg-green-100 text-green-800" },
};

const getTriageLabel = (level: number) =>
    triageLevels[level]?.label || "Desconocido";
const getTriageColor = (level: number) =>
    triageLevels[level]?.color || "bg-gray-100";

const viewDetails = (id: number) => {
    router.get(route("emergency.show", id));
};

const calculateTime = (admission: string, discharge: string | null) => {
    const start = new Date(admission);
    const end = discharge ? new Date(discharge) : new Date();
    const diffMs = end.getTime() - start.getTime();
    const hours = Math.floor(diffMs / 3600000);
    const mins = Math.floor((diffMs % 3600000) / 60000);
    return hours > 0 ? `${hours}h ${mins}m` : `${mins}m`;
};
</script>

<template>
    <Head title="Historial de Emergencias" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Historial de Admisiones de Emergencia
                </h1>
                <p class="text-gray-600 mt-2">
                    Admisiones finalizadas y cerradas
                </p>
            </div>

            <!-- History Table -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Admisiones ({{ admissions.total }} total)
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Paciente
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    DNI
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Fecha Admisión
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Triage
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Motivo
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Diagnóstico
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Duración
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
                                v-for="admission in admissions.data"
                                :key="admission.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4">
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ admission.patient.first_name }}
                                        {{ admission.patient.last_name }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600">
                                        {{ admission.patient.dni }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900">
                                        {{
                                            new Date(
                                                admission.admission_time,
                                            ).toLocaleString("es-AR")
                                        }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium',
                                            getTriageColor(
                                                admission.triage_level,
                                            ),
                                        ]"
                                    >
                                        {{
                                            getTriageLabel(
                                                admission.triage_level,
                                            )
                                        }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900">
                                        {{ admission.chief_complaint }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900">
                                        {{ admission.diagnosis || "-" }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{
                                            calculateTime(
                                                admission.admission_time,
                                                admission.discharged_at,
                                            )
                                        }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <button
                                        @click="viewDetails(admission.id)"
                                        class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                                    >
                                        Ver Detalle
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="admissions.last_page > 1"
                    class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-between items-center"
                >
                    <p class="text-sm text-gray-600">
                        Mostrando {{ admissions.from }} a {{ admissions.to }} de
                        {{ admissions.total }} admisiones
                    </p>
                    <div class="flex gap-1">
                        <a
                            v-for="link in admissions.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1 rounded text-sm',
                                link.active
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-300 text-gray-900 hover:bg-gray-400',
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
