<script setup lang="ts">
import { format } from "date-fns";
import { es } from "date-fns/locale";
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface User {
    id: number;
    name: string;
    specialty?: string;
}

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
    phone?: string;
    email?: string;
}

interface Appointment {
    id: number;
    doctor_id: number;
    patient_id: number;
    scheduled_at: string;
    status: "pending" | "confirmed" | "completed" | "cancelled";
    reason: string | null;
    notes: string | null;
    doctor: User;
    patient: Patient;
    created_at: string;
    updated_at: string;
}

interface Props {
    appointment: Appointment;
}

const props = defineProps<Props>();

const formatDateTime = (date: string) => {
    return format(new Date(date), "dd MMM yyyy HH:mm", { locale: es });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: "gray",
        confirmed: "blue",
        completed: "green",
        cancelled: "red",
    };
    return colors[status] || "gray";
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        pending: "Pendiente",
        confirmed: "Confirmada",
        completed: "Completada",
        cancelled: "Cancelada",
    };
    return labels[status] || "Desconocido";
};

const getStatusBgClass = (status: string) => {
    const classes: Record<string, string> = {
        gray: "bg-gray-100 ",
        blue: "bg-primary-100 ",
        green: "bg-success-100 ",
        red: "bg-danger-100 ",
    };
    return classes[getStatusColor(status)] || classes.gray;
};

const getStatusTextClass = (status: string) => {
    const classes: Record<string, string> = {
        gray: "text-gray-800 ",
        blue: "text-primary-800 ",
        green: "text-success-800 ",
        red: "text-danger-800 ",
    };
    return classes[getStatusColor(status)] || classes.gray;
};
</script>

<template>
    <Head
        :title="`Cita - ${appointment.patient.first_name} ${appointment.patient.last_name}`"
    />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 "
            >
                Detalles de la cita
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white shadow-sm ">
                    <!-- Header -->
                    <div
                        class="border-b border-gray-200 bg-gradient-to-r from-primary-50 to-primary-50 p-6   "
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <h1
                                    class="text-3xl font-bold text-gray-900 "
                                >
                                    {{ appointment.patient.first_name }}
                                    {{ appointment.patient.last_name }}
                                </h1>
                                <p
                                    class="mt-1 text-sm text-gray-600 "
                                >
                                    {{
                                        formatDateTime(appointment.scheduled_at)
                                    }}
                                </p>
                            </div>
                            <span
                                :class="{
                                    'rounded-full px-4 py-2 text-lg font-bold': true,
                                    [getStatusBgClass(appointment.status)]:
                                        true,
                                    [getStatusTextClass(appointment.status)]:
                                        true,
                                }"
                            >
                                {{ getStatusLabel(appointment.status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                            <!-- Doctor Information -->
                            <div>
                                <h3
                                    class="mb-4 text-lg font-semibold text-gray-900 "
                                >
                                    Médico
                                </h3>
                                <div class="space-y-3">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 "
                                        >
                                            Nombre
                                        </label>
                                        <p
                                            class="mt-1 text-gray-900 "
                                        >
                                            {{ appointment.doctor.name }}
                                        </p>
                                    </div>
                                    <div v-if="appointment.doctor.specialty">
                                        <label
                                            class="block text-sm font-medium text-gray-700 "
                                        >
                                            Especialidad
                                        </label>
                                        <p
                                            class="mt-1 text-gray-900 "
                                        >
                                            {{ appointment.doctor.specialty }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Patient Information -->
                            <div>
                                <h3
                                    class="mb-4 text-lg font-semibold text-gray-900 "
                                >
                                    Paciente
                                </h3>
                                <div class="space-y-3">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 "
                                        >
                                            DNI
                                        </label>
                                        <p
                                            class="mt-1 font-mono text-gray-900 "
                                        >
                                            {{ appointment.patient.dni }}
                                        </p>
                                    </div>
                                    <div v-if="appointment.patient.phone">
                                        <label
                                            class="block text-sm font-medium text-gray-700 "
                                        >
                                            Teléfono
                                        </label>
                                        <p
                                            class="mt-1 text-gray-900 "
                                        >
                                            {{ appointment.patient.phone }}
                                        </p>
                                    </div>
                                    <div v-if="appointment.patient.email">
                                        <label
                                            class="block text-sm font-medium text-gray-700 "
                                        >
                                            Email
                                        </label>
                                        <p
                                            class="mt-1 text-gray-900 "
                                        >
                                            {{ appointment.patient.email }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Appointment Details -->
                        <div
                            class="mt-8 border-t border-gray-200 pt-8 "
                        >
                            <h3
                                class="mb-4 text-lg font-semibold text-gray-900 "
                            >
                                Detalles de la cita
                            </h3>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 "
                                    >
                                        Fecha y hora
                                    </label>
                                    <p
                                        class="mt-1 text-gray-900 "
                                    >
                                        {{
                                            formatDateTime(
                                                appointment.scheduled_at,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 "
                                    >
                                        Estado
                                    </label>
                                    <p
                                        class="mt-1 text-gray-900 "
                                    >
                                        {{ getStatusLabel(appointment.status) }}
                                    </p>
                                </div>
                                <div v-if="appointment.reason">
                                    <label
                                        class="block text-sm font-medium text-gray-700 "
                                    >
                                        Motivo de la consulta
                                    </label>
                                    <p
                                        class="mt-1 text-gray-900 "
                                    >
                                        {{ appointment.reason }}
                                    </p>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div v-if="appointment.notes" class="mt-6">
                                <label
                                    class="block text-sm font-medium text-gray-700 "
                                >
                                    Notas
                                </label>
                                <div
                                    class="mt-2 rounded-lg bg-gray-50 p-4 "
                                >
                                    <p
                                        class="whitespace-pre-wrap text-sm text-gray-700 "
                                    >
                                        {{ appointment.notes }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Timestamps -->
                        <div
                            class="mt-6 border-t border-gray-200 pt-6 "
                        >
                            <p class="text-xs text-gray-500 ">
                                Registrada:
                                {{ formatDateTime(appointment.created_at) }}
                            </p>
                            <p
                                class="mt-1 text-xs text-gray-500 "
                            >
                                Última actualización:
                                {{ formatDateTime(appointment.updated_at) }}
                            </p>
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
                                        'prescriptions.create-from-appointment',
                                        appointment.id,
                                    )
                                "
                                class="rounded-lg bg-success-600 px-4 py-2 font-semibold text-white hover:bg-success-700 focus:outline-none"
                            >
                                📝 Crear Receta
                            </Link>
                            <Link
                                :href="
                                    route('appointments.edit', appointment.id)
                                "
                                class="rounded-lg bg-primary-600 px-4 py-2 font-semibold text-white hover:bg-primary-700 focus:outline-none"
                            >
                                Editar cita
                            </Link>
                            <Link
                                :href="route('appointments.index')"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50    "
                            >
                                Volver al listado
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
