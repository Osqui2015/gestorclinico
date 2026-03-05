<script setup lang="ts">
import { format } from "date-fns";
import { es } from "date-fns/locale";
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface User {
    id: number;
    name: string;
}

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
    birth_date: string;
    phone: string | null;
    email: string | null;
    created_at: string;
    updated_at: string;
}

interface MedicalRecord {
    id: number;
    reason: string | null;
    diagnosis: string | null;
    doctor: User;
    created_at: string;
}

interface Props {
    patient: Patient;
    medicalRecords: MedicalRecord[];
}

const props = defineProps<Props>();

const formatDate = (date: string) => {
    return format(new Date(date), "dd MMM yyyy", { locale: es });
};

const getAge = (birthDate: string) => {
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();

    if (
        monthDiff < 0 ||
        (monthDiff === 0 && today.getDate() < birth.getDate())
    ) {
        age--;
    }

    return age;
};
</script>

<template>
    <Head :title="`${patient.first_name} ${patient.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 "
            >
                Detalles del paciente
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white shadow-sm ">
                    <!-- Header -->
                    <div
                        class="border-b border-gray-200 bg-gradient-to-r from-primary-50 to-primary-50 p-6   "
                    >
                        <h1
                            class="text-3xl font-bold text-gray-900 "
                        >
                            {{ patient.first_name }} {{ patient.last_name }}
                        </h1>
                        <p
                            class="mt-1 text-sm text-gray-600 "
                        >
                            DNI:
                            <span class="font-mono font-semibold">{{
                                patient.dni
                            }}</span>
                        </p>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Left column -->
                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 "
                                    >
                                        Edad
                                    </label>
                                    <p
                                        class="mt-1 text-lg text-gray-900 "
                                    >
                                        {{ getAge(patient.birth_date) }} años
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 "
                                    >
                                        Fecha de nacimiento
                                    </label>
                                    <p
                                        class="mt-1 text-gray-900 "
                                    >
                                        {{ formatDate(patient.birth_date) }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 "
                                    >
                                        Teléfono
                                    </label>
                                    <p
                                        class="mt-1 text-gray-900 "
                                    >
                                        {{ patient.phone || "No registrado" }}
                                    </p>
                                </div>
                            </div>

                            <!-- Right column -->
                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 "
                                    >
                                        Correo electrónico
                                    </label>
                                    <p
                                        class="mt-1 text-gray-900 "
                                    >
                                        {{ patient.email || "No registrado" }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 "
                                    >
                                        Registrado el
                                    </label>
                                    <p
                                        class="mt-1 text-sm text-gray-600 "
                                    >
                                        {{ formatDate(patient.created_at) }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 "
                                    >
                                        Última actualización
                                    </label>
                                    <p
                                        class="mt-1 text-sm text-gray-600 "
                                    >
                                        {{ formatDate(patient.updated_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer / Actions -->
                    <div
                        class="border-t border-gray-200 bg-gray-50 p-6  "
                    >
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <Link
                                :href="
                                    route(
                                        'prescriptions.create-for-patient',
                                        patient.id,
                                    )
                                "
                                class="rounded-lg bg-success-600 px-4 py-2 font-semibold text-white hover:bg-success-700 focus:outline-none"
                            >
                                📝 Nueva Receta
                            </Link>
                            <Link
                                :href="route('patients.edit', patient.id)"
                                class="rounded-lg bg-primary-600 px-4 py-2 font-semibold text-white hover:bg-primary-700 focus:outline-none"
                            >
                                Editar información
                            </Link>
                            <Link
                                :href="route('patients.index')"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50    "
                            >
                                Volver al listado
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Medical Records Section -->
                <div
                    class="mt-8 rounded-lg bg-white shadow-sm "
                >
                    <div
                        class="border-b border-gray-200 p-6 "
                    >
                        <div class="flex items-center justify-between">
                            <h3
                                class="text-lg font-semibold text-gray-900 "
                            >
                                Historia Clínica
                            </h3>
                            <Link
                                :href="
                                    route(
                                        'patients.medical-records.create',
                                        patient.id,
                                    )
                                "
                                class="rounded-lg bg-primary-600 px-3 py-1 text-sm font-semibold text-white hover:bg-primary-700"
                            >
                                + Nueva Evolución
                            </Link>
                        </div>
                    </div>

                    <div
                        v-if="medicalRecords.length > 0"
                        class="border-t border-gray-200 "
                    >
                        <!-- Medical records timeline -->
                        <div
                            v-for="(record, index) in medicalRecords"
                            :key="record.id"
                            class="border-b border-gray-100 p-6 last:border-b-0 "
                        >
                            <div class="flex gap-4">
                                <!-- Timeline dot -->
                                <div class="flex flex-col items-center">
                                    <div
                                        class="h-3 w-3 rounded-full bg-primary-600"
                                    ></div>
                                    <div
                                        v-if="index < medicalRecords.length - 1"
                                        class="mt-2 h-8 w-px bg-gray-300 "
                                    ></div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1">
                                    <div
                                        class="flex items-start justify-between"
                                    >
                                        <div>
                                            <p
                                                class="font-semibold text-gray-900 "
                                            >
                                                {{ record.doctor.name }}
                                            </p>
                                            <p
                                                class="text-sm text-gray-500 "
                                            >
                                                {{
                                                    format(
                                                        new Date(
                                                            record.created_at,
                                                        ),
                                                        "dd MMM yyyy, HH:mm",
                                                        { locale: es },
                                                    )
                                                }}
                                            </p>
                                        </div>
                                        <Link
                                            :href="
                                                route(
                                                    'patients.medical-records.show',
                                                    [patient.id, record.id],
                                                )
                                            "
                                            class="text-sm font-semibold text-primary-600 hover:text-primary-700  "
                                        >
                                            Ver detalle →
                                        </Link>
                                    </div>

                                    <!-- Preview -->
                                    <div class="mt-3 space-y-2">
                                        <div
                                            v-if="record.reason"
                                            class="text-sm text-gray-700 "
                                        >
                                            <span class="font-medium"
                                                >Motivo:</span
                                            >
                                            {{ record.reason.substring(0, 100)
                                            }}<span
                                                v-if="
                                                    record.reason.length > 100
                                                "
                                                >...</span
                                            >
                                        </div>
                                        <div
                                            v-if="record.diagnosis"
                                            class="text-sm text-gray-700 "
                                        >
                                            <span class="font-medium"
                                                >Diagnóstico:</span
                                            >
                                            {{
                                                record.diagnosis.substring(
                                                    0,
                                                    100,
                                                )
                                            }}<span
                                                v-if="
                                                    record.diagnosis.length >
                                                    100
                                                "
                                                >...</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div v-else class="p-6">
                        <p class="text-center text-gray-600 ">
                            No hay registros de evoluciones clínicas por el
                            momento.
                        </p>
                    </div>

                    <!-- Footer with link to full history -->
                    <div
                        v-if="medicalRecords.length > 0"
                        class="border-t border-gray-200 bg-gray-50 p-4 text-center  "
                    >
                        <Link
                            :href="
                                route(
                                    'patients.medical-records.index',
                                    patient.id,
                                )
                            "
                            class="text-sm font-semibold text-primary-600 hover:text-primary-700  "
                        >
                            Ver historia clínica completa →
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
