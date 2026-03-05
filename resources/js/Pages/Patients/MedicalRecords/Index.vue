<script setup lang="ts">
import { computed } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

interface User {
    id: number;
    name: string;
}

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
}

interface MedicalRecord {
    id: number;
    patient_id: number;
    doctor_id: number;
    reason: string | null;
    diagnosis: string | null;
    treatment: string | null;
    private_notes: string | null;
    doctor: User;
    created_at: string;
    updated_at: string;
}

interface PaginationData {
    data: MedicalRecord[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface Props {
    patient: Patient;
    medicalRecords: PaginationData;
}

const props = defineProps<Props>();

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("es-ES", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const truncateText = (text: string | null, length: number = 100) => {
    if (!text) return "-";
    return text.length > length ? text.substring(0, length) + "..." : text;
};

const page = usePage();
const auth = computed(() => page.props.auth);
</script>

<template>
    <Head
        :title="`Historia Clínica - ${patient.first_name} ${patient.last_name}`"
    />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 "
            >
                Historia clínica: {{ patient.first_name }}
                {{ patient.last_name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <!-- Header with action button -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 ">
                            Mostrando
                            <strong>{{ medicalRecords.from }}</strong> a
                            <strong>{{ medicalRecords.to }}</strong> de
                            <strong>{{ medicalRecords.total }}</strong>
                            registros
                        </p>
                    </div>
                    <Link
                        :href="
                            route('patients.medical-records.create', patient.id)
                        "
                        class="rounded-lg bg-primary-600 px-4 py-2 font-semibold text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2  "
                    >
                        + Nueva Evolución
                    </Link>
                </div>

                <!-- Records List -->
                <div v-if="medicalRecords.data.length > 0" class="space-y-4">
                    <div
                        v-for="record in medicalRecords.data"
                        :key="record.id"
                        class="rounded-lg border border-gray-200 bg-white shadow-sm transition-all hover:shadow-md  "
                    >
                        <div
                            class="border-b border-gray-200 px-6 py-4 "
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3
                                        class="font-semibold text-gray-900 "
                                    >
                                        {{ record.doctor.name }}
                                    </h3>
                                    <p
                                        class="text-sm text-gray-500 "
                                    >
                                        {{ formatDate(record.created_at) }}
                                    </p>
                                </div>
                                <Link
                                    :href="
                                        route('patients.medical-records.show', [
                                            patient.id,
                                            record.id,
                                        ])
                                    "
                                    class="inline-flex items-center rounded-lg border border-primary-300 bg-primary-50 px-3 py-1 text-sm font-medium text-primary-700 hover:bg-primary-100    "
                                >
                                    Ver detalle →
                                </Link>
                            </div>
                        </div>

                        <!-- Content Preview -->
                        <div class="space-y-4 px-6 py-4">
                            <!-- Reason -->
                            <div v-if="record.reason">
                                <p
                                    class="text-xs font-semibold uppercase text-gray-600 "
                                >
                                    Motivo
                                </p>
                                <p
                                    class="mt-1 text-sm text-gray-700 "
                                >
                                    {{ truncateText(record.reason, 150) }}
                                </p>
                            </div>

                            <!-- Diagnosis -->
                            <div v-if="record.diagnosis">
                                <p
                                    class="text-xs font-semibold uppercase text-gray-600 "
                                >
                                    Diagnóstico
                                </p>
                                <p
                                    class="mt-1 text-sm text-gray-700 "
                                >
                                    {{ truncateText(record.diagnosis, 150) }}
                                </p>
                            </div>

                            <!-- Treatment -->
                            <div v-if="record.treatment">
                                <p
                                    class="text-xs font-semibold uppercase text-gray-600 "
                                >
                                    Tratamiento
                                </p>
                                <p
                                    class="mt-1 text-sm text-gray-700 "
                                >
                                    {{ truncateText(record.treatment, 150) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="rounded-lg border border-dashed border-gray-300 bg-gray-50 py-12 text-center  "
                >
                    <p class="text-gray-600 ">
                        No hay registros de evoluciones clínicas por el momento.
                    </p>
                    <Link
                        :href="
                            route('patients.medical-records.create', patient.id)
                        "
                        class="mt-4 inline-block rounded-lg bg-primary-600 px-4 py-2 font-semibold text-white hover:bg-primary-700"
                    >
                        Crear primera evolución
                    </Link>
                </div>

                <!-- Pagination -->
                <div
                    v-if="medicalRecords.last_page > 1"
                    class="mt-8 flex items-center justify-between"
                >
                    <Link
                        v-if="medicalRecords.current_page > 1"
                        :href="
                            route('patients.medical-records.index', {
                                patient_id: patient.id,
                                page: medicalRecords.current_page - 1,
                            })
                        "
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50    "
                    >
                        ← Anterior
                    </Link>
                    <span class="text-sm text-gray-600 ">
                        Página {{ medicalRecords.current_page }} de
                        {{ medicalRecords.last_page }}
                    </span>
                    <Link
                        v-if="
                            medicalRecords.current_page <
                            medicalRecords.last_page
                        "
                        :href="
                            route('patients.medical-records.index', {
                                patient_id: patient.id,
                                page: medicalRecords.current_page + 1,
                            })
                        "
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50    "
                    >
                        Siguiente →
                    </Link>
                </div>

                <!-- Back button -->
                <div class="mt-8">
                    <Link
                        :href="route('patients.show', patient.id)"
                        class="text-primary-600 hover:text-primary-700  "
                    >
                        ← Volver al perfil del paciente
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
