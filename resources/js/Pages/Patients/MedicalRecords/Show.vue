<script setup lang="ts">
import { computed } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

interface User {
    id: number;
    name: string;
    email: string;
}

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
    birth_date: string;
}

interface Prescription {
    id: number;
    diagnosis: string;
    medications: Array<{
        name: string;
        dosage: string;
        frequency: string;
        duration: string;
    }>;
    created_at: string;
}

interface MedicalRecord {
    id: number;
    patient_id: number;
    doctor_id: number;
    reason: string | null;
    diagnosis: string | null;
    treatment: string | null;
    private_notes: string | null;
    is_first_consultation: boolean;
    health_background: string | null;
    attachments: any;
    doctor: User;
    prescriptions?: Prescription[];
    created_at: string;
    updated_at: string;
}

interface Props {
    patient: Patient;
    medicalRecord: MedicalRecord;
    can: {
        edit: boolean;
        delete: boolean;
    };
}

const props = defineProps<Props>();

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);

const formattedCreatedAt = computed(() => {
    const date = new Date(props.medicalRecord.created_at);
    return date.toLocaleDateString("es-ES", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
});

const formattedUpdatedAt = computed(() => {
    const date = new Date(props.medicalRecord.updated_at);
    return date.toLocaleDateString("es-ES", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
});

const showDeleteConfirm = () => {
    if (
        confirm(
            "¿Estás seguro de que deseas eliminar este registro? Esta acción no se puede deshacer.",
        )
    ) {
        deleteRecord();
    }
};

const deleteRecord = () => {
    const form = new FormData();
    form.append("_method", "DELETE");

    fetch(
        route("patients.medical-records.destroy", [
            props.patient.id,
            props.medicalRecord.id,
        ]),
        {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") || "",
            },
            body: form,
        },
    ).then(() => {
        window.location.href = route("patients.show", props.patient.id);
    });
};
</script>

<template>
    <Head :title="`Evolución - ${patient.first_name} ${patient.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Evolución clínica: {{ patient.first_name }}
                {{ patient.last_name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-6 rounded-lg bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ medicalRecord.doctor.name }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    {{ medicalRecord.doctor.email }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-600">
                                    Creado:
                                    <span class="text-gray-900">{{
                                        formattedCreatedAt
                                    }}</span>
                                </p>
                                <p
                                    v-if="
                                        medicalRecord.created_at !==
                                        medicalRecord.updated_at
                                    "
                                    class="text-xs text-gray-500"
                                >
                                    Editado: {{ formattedUpdatedAt }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Content Section -->
                    <div class="space-y-6 px-6 py-4">
                        <!-- Reason -->
                        <div v-if="medicalRecord.reason">
                            <h4
                                class="mb-2 text-sm font-semibold text-gray-700"
                            >
                                Motivo de consulta
                            </h4>
                            <p
                                class="whitespace-pre-wrap rounded-lg bg-gray-50 p-4 text-gray-800"
                            >
                                {{ medicalRecord.reason }}
                            </p>
                        </div>

                        <!-- Diagnosis -->
                        <div v-if="medicalRecord.diagnosis">
                            <h4
                                class="mb-2 text-sm font-semibold text-gray-700"
                            >
                                Diagnóstico
                            </h4>
                            <p
                                class="whitespace-pre-wrap rounded-lg bg-gray-50 p-4 text-gray-800"
                            >
                                {{ medicalRecord.diagnosis }}
                            </p>
                        </div>

                        <!-- Treatment -->
                        <div v-if="medicalRecord.treatment">
                            <h4
                                class="mb-2 text-sm font-semibold text-gray-700"
                            >
                                Tratamiento
                            </h4>
                            <p
                                class="whitespace-pre-wrap rounded-lg bg-gray-50 p-4 text-gray-800"
                            >
                                {{ medicalRecord.treatment }}
                            </p>
                        </div>

                        <!-- Health Background -->
                        <div v-if="medicalRecord.health_background">
                            <h4
                                class="mb-2 text-sm font-semibold text-gray-700"
                            >
                                Antecedentes de salud
                            </h4>
                            <p
                                class="whitespace-pre-wrap rounded-lg bg-primary-50 p-4 text-gray-800"
                            >
                                {{ medicalRecord.health_background }}
                            </p>
                        </div>

                        <!-- Private Notes - Only visible to doctor who created them -->
                        <div
                            v-if="
                                medicalRecord.private_notes &&
                                medicalRecord.doctor_id === currentUser?.id
                            "
                            class="rounded-lg border border-warning-200 bg-warning-50 p-4"
                        >
                            <h4
                                class="mb-2 text-sm font-semibold text-warning-900"
                            >
                                Notas privadas
                            </h4>
                            <p
                                class="whitespace-pre-wrap text-sm text-warning-800"
                            >
                                {{ medicalRecord.private_notes }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Patient Info Card -->
                <div class="mb-6 rounded-lg bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="font-semibold text-gray-900">
                            Información del paciente
                        </h3>
                    </div>
                    <div
                        class="grid grid-cols-2 gap-4 px-6 py-4 md:grid-cols-4"
                    >
                        <div>
                            <span
                                class="text-xs font-semibold uppercase text-gray-600"
                                >Nombre</span
                            >
                            <p class="mt-1 text-gray-900">
                                {{ patient.first_name }} {{ patient.last_name }}
                            </p>
                        </div>
                        <div>
                            <span
                                class="text-xs font-semibold uppercase text-gray-600"
                                >DNI</span
                            >
                            <p class="mt-1 text-gray-900">
                                {{ patient.dni }}
                            </p>
                        </div>
                        <div>
                            <span
                                class="text-xs font-semibold uppercase text-gray-600"
                                >Nacimiento</span
                            >
                            <p class="mt-1 text-gray-900">
                                {{
                                    new Date(
                                        patient.birth_date,
                                    ).toLocaleDateString("es-ES")
                                }}
                            </p>
                        </div>
                        <div>
                            <span
                                class="text-xs font-semibold uppercase text-gray-600"
                                >ID Paciente</span
                            >
                            <p class="mt-1 font-mono text-gray-900">
                                #{{ patient.id }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Prescriptions Section -->
                <div
                    v-if="
                        medicalRecord.prescriptions &&
                        medicalRecord.prescriptions.length > 0
                    "
                    class="mb-6 rounded-lg bg-white shadow-sm"
                >
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="font-semibold text-gray-900">
                            Recetas de esta consulta
                        </h3>
                    </div>
                    <div class="px-6 py-4">
                        <div
                            v-for="prescription in medicalRecord.prescriptions"
                            :key="prescription.id"
                            class="mb-4 rounded-lg border border-success-200 bg-success-50 p-4 last:mb-0"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-success-900">
                                        Diagnóstico:
                                        {{ prescription.diagnosis }}
                                    </h4>
                                    <p class="mt-2 text-sm text-success-800">
                                        <strong>Medicamentos:</strong>
                                        {{
                                            prescription.medications
                                                .map((m) => m.name)
                                                .join(", ")
                                        }}
                                    </p>
                                    <p class="mt-1 text-xs text-success-700">
                                        Creada:
                                        {{
                                            new Date(
                                                prescription.created_at,
                                            ).toLocaleDateString("es-ES", {
                                                year: "numeric",
                                                month: "long",
                                                day: "numeric",
                                                hour: "2-digit",
                                                minute: "2-digit",
                                            })
                                        }}
                                    </p>
                                </div>
                                <Link
                                    :href="
                                        route(
                                            'prescriptions.show',
                                            prescription.id,
                                        )
                                    "
                                    class="ml-4 rounded-lg bg-success-600 px-4 py-2 text-sm font-semibold text-white hover:bg-success-700"
                                >
                                    Ver Receta
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <Link
                        :href="
                            route('prescriptions.create-for-medical-record', [
                                patient.id,
                                medicalRecord.id,
                            ])
                        "
                        class="rounded-lg bg-success-600 px-6 py-2 font-semibold text-white hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-success-500 focus:ring-offset-2"
                    >
                        📝 Nueva Receta
                    </Link>

                    <Link
                        v-if="can.edit"
                        :href="
                            route('patients.medical-records.edit', [
                                patient.id,
                                medicalRecord.id,
                            ])
                        "
                        class="rounded-lg bg-primary-600 px-6 py-2 font-semibold text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                    >
                        Editar
                    </Link>

                    <button
                        v-if="can.delete"
                        @click="showDeleteConfirm"
                        class="rounded-lg bg-danger-600 px-6 py-2 font-semibold text-white hover:bg-danger-700 focus:outline-none focus:ring-2 focus:ring-danger-500 focus:ring-offset-2"
                    >
                        Eliminar
                    </button>

                    <Link
                        :href="route('patients.show', patient.id)"
                        class="rounded-lg border border-gray-300 bg-white px-6 py-2 font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Volver
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
