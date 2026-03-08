<script setup lang="ts">
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface Doctor {
    id: number;
    name: string;
    specialty?: string | null;
    email?: string | null;
}

interface Appointment {
    id: number;
    scheduled_at: string;
    status: string;
    checked_in_at: string | null;
    confirmed: boolean;
    patient: {
        id: number;
        first_name: string;
        last_name: string;
        dni: string;
        phone: string | null;
    };
    reason: string | null;
}

const props = defineProps<{
    doctor: Doctor;
    appointments: Appointment[];
    selectedDate: string;
}>();

const selectedDate = ref(props.selectedDate);

const applyDate = () => {
    router.get(
        route("reception.by-doctor", props.doctor.id),
        { date: selectedDate.value },
        { preserveState: true, preserveScroll: true },
    );
};

const statusLabel = (status: string) => {
    const map: Record<string, string> = {
        pending: "Pendiente",
        called: "Llamado",
        attending: "Atendiendo",
        completed: "Completado",
        cancelled: "Cancelado",
    };
    return map[status] || status;
};

const statusClass = (status: string) => {
    const map: Record<string, string> = {
        pending: "bg-yellow-100 text-yellow-800",
        called: "bg-blue-100 text-blue-800",
        attending: "bg-indigo-100 text-indigo-800",
        completed: "bg-green-100 text-green-800",
        cancelled: "bg-red-100 text-red-800",
    };
    return map[status] || "bg-gray-100 text-gray-800";
};

const formatTime = (value: string) => {
    return new Date(value).toLocaleTimeString("es-AR", {
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>

<template>
    <Head :title="`Agenda de ${doctor.name}`" />
    <AuthenticatedLayout>
        <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ doctor.name }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ doctor.specialty || "Sin especialidad" }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <input
                        v-model="selectedDate"
                        type="date"
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        @change="applyDate"
                    />
                    <Link
                        :href="route('reception.dashboard')"
                        class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300"
                    >
                        Volver
                    </Link>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Hora
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Paciente
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Motivo
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Check-in
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr
                            v-for="appointment in appointments"
                            :key="appointment.id"
                        >
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ formatTime(appointment.scheduled_at) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <p class="font-medium text-gray-900">
                                    {{ appointment.patient.last_name }},
                                    {{ appointment.patient.first_name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    DNI {{ appointment.patient.dni }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ appointment.reason || "-" }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{
                                    appointment.checked_in_at
                                        ? formatTime(appointment.checked_in_at)
                                        : "-"
                                }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span
                                    :class="statusClass(appointment.status)"
                                    class="rounded-full px-2 py-1 text-xs font-semibold"
                                >
                                    {{ statusLabel(appointment.status) }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="appointments.length === 0">
                            <td
                                colspan="5"
                                class="px-4 py-10 text-center text-sm text-gray-500"
                            >
                                Sin turnos para este profesional en la fecha
                                seleccionada.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
