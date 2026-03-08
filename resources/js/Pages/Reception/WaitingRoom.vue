<script setup lang="ts">
import { computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface WaitingAppointment {
    id: number;
    scheduled_at: string;
    checked_in_at: string | null;
    status: string;
    patient: {
        id: number;
        first_name: string;
        last_name: string;
        dni: string;
    };
    doctor: {
        id: number;
        name: string;
        specialty: string | null;
    };
}

const props = defineProps<{
    appointments: WaitingAppointment[];
}>();

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

const rows = computed(() => {
    const now = new Date().getTime();
    return props.appointments.map((appointment) => {
        const checkedIn = appointment.checked_in_at
            ? new Date(appointment.checked_in_at).getTime()
            : null;
        const waitingMinutes = checkedIn
            ? Math.max(0, Math.round((now - checkedIn) / 60000))
            : 0;

        return {
            ...appointment,
            waitingMinutes,
        };
    });
});

const formatTime = (value: string | null) => {
    if (!value) {
        return "-";
    }

    return new Date(value).toLocaleTimeString("es-AR", {
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>

<template>
    <Head title="Sala de espera" />
    <AuthenticatedLayout>
        <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Sala de espera
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Pacientes con check-in pendiente de llamado
                    </p>
                </div>
                <Link
                    :href="route('reception.dashboard')"
                    class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300"
                >
                    Volver al panel
                </Link>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Paciente
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Doctor
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Turno
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Check-in
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Espera
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="appointment in rows" :key="appointment.id">
                            <td class="px-4 py-3 text-sm text-gray-800">
                                <p class="font-medium text-gray-900">
                                    {{ appointment.patient.last_name }},
                                    {{ appointment.patient.first_name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    DNI {{ appointment.patient.dni }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <p>{{ appointment.doctor.name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ appointment.doctor.specialty || "-" }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ formatTime(appointment.scheduled_at) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ formatTime(appointment.checked_in_at) }}
                            </td>
                            <td
                                class="px-4 py-3 text-sm font-semibold"
                                :class="
                                    appointment.waitingMinutes >= 30
                                        ? 'text-red-600'
                                        : 'text-gray-800'
                                "
                            >
                                {{ appointment.waitingMinutes }} min
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
                        <tr v-if="rows.length === 0">
                            <td
                                colspan="6"
                                class="px-4 py-10 text-center text-sm text-gray-500"
                            >
                                No hay pacientes en sala de espera.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
