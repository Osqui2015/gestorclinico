<script setup lang="ts">
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface Appointment {
    id: number;
    scheduled_at: string;
    duration: number | null;
    status: string;
    status_label: string;
    status_color: string;
    confirmed: boolean;
    checked_in_at: string | null;
    is_walk_in: boolean;
    patient: {
        id: number;
        full_name: string;
        dni: string;
        phone: string | null;
        has_allergies: boolean;
        allergies: string | null;
        health_insurance: string | null;
        member_number: string | null;
    };
    doctor: {
        id: number;
        name: string;
        specialty: string | null;
    };
    reason: string | null;
}

interface DoctorSummary {
    id: number;
    name: string;
    specialty: string | null;
    today_appointments: number;
    pending_appointments: number;
}

const props = defineProps<{
    appointments: Appointment[];
    stats: {
        total: number;
        pending: number;
        checked_in: number;
        attending: number;
        completed: number;
        cancelled: number;
        not_confirmed: number;
    };
    doctors: DoctorSummary[];
    selectedDate: string;
    isToday: boolean;
}>();

const selectedDate = ref(props.selectedDate);

const applyDate = () => {
    router.get(
        route("reception.dashboard"),
        { date: selectedDate.value },
        { preserveState: true, preserveScroll: true },
    );
};

const checkIn = (appointmentId: number) => {
    router.post(
        route("reception.appointments.check-in", appointmentId),
        {},
        { preserveScroll: true },
    );
};

const confirmAppointment = (appointmentId: number) => {
    router.post(
        route("reception.appointments.confirm", appointmentId),
        {},
        { preserveScroll: true },
    );
};

const badgeClass = (color: string) => {
    const map: Record<string, string> = {
        gray: "bg-gray-100 text-gray-700",
        yellow: "bg-yellow-100 text-yellow-800",
        blue: "bg-blue-100 text-blue-800",
        green: "bg-green-100 text-green-800",
        red: "bg-red-100 text-red-800",
    };
    return map[color] || map.gray;
};

const formatTime = (value: string) => {
    return new Date(value).toLocaleTimeString("es-AR", {
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>

<template>
    <Head title="Recepcion" />
    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Panel de recepcion
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        {{
                            isToday
                                ? "Turnos de hoy"
                                : "Turnos del dia seleccionado"
                        }}
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
                        :href="route('reception.waiting-room')"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                    >
                        Sala de espera
                    </Link>
                </div>
            </div>

            <div
                class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-7"
            >
                <div class="rounded-lg bg-white p-4 shadow">
                    <p class="text-xs uppercase text-gray-500">Total</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">
                        {{ stats.total }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <p class="text-xs uppercase text-gray-500">Pendientes</p>
                    <p class="mt-1 text-2xl font-bold text-yellow-600">
                        {{ stats.pending }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <p class="text-xs uppercase text-gray-500">Check-in</p>
                    <p class="mt-1 text-2xl font-bold text-indigo-600">
                        {{ stats.checked_in }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <p class="text-xs uppercase text-gray-500">Atendiendo</p>
                    <p class="mt-1 text-2xl font-bold text-blue-600">
                        {{ stats.attending }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <p class="text-xs uppercase text-gray-500">Completados</p>
                    <p class="mt-1 text-2xl font-bold text-green-600">
                        {{ stats.completed }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <p class="text-xs uppercase text-gray-500">Cancelados</p>
                    <p class="mt-1 text-2xl font-bold text-red-600">
                        {{ stats.cancelled }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <p class="text-xs uppercase text-gray-500">Sin confirmar</p>
                    <p class="mt-1 text-2xl font-bold text-orange-600">
                        {{ stats.not_confirmed }}
                    </p>
                </div>
            </div>

            <div class="mb-6 overflow-hidden rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Turnos del dia
                    </h2>
                </div>
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
                                Doctor
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Estado
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr
                            v-for="appointment in appointments"
                            :key="appointment.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ formatTime(appointment.scheduled_at) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <p class="font-medium text-gray-900">
                                    {{ appointment.patient.full_name }}
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
                            <td class="px-4 py-3 text-sm">
                                <span
                                    :class="
                                        badgeClass(appointment.status_color)
                                    "
                                    class="rounded-full px-2 py-1 text-xs font-semibold"
                                >
                                    {{ appointment.status_label }}
                                </span>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{
                                        appointment.confirmed
                                            ? "Confirmado"
                                            : "Sin confirmar"
                                    }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <div class="flex justify-end gap-2">
                                    <button
                                        v-if="!appointment.confirmed"
                                        type="button"
                                        class="rounded bg-blue-100 px-2 py-1 font-semibold text-blue-700 hover:bg-blue-200"
                                        @click="
                                            confirmAppointment(appointment.id)
                                        "
                                    >
                                        Confirmar
                                    </button>
                                    <button
                                        v-if="!appointment.checked_in_at"
                                        type="button"
                                        class="rounded bg-green-100 px-2 py-1 font-semibold text-green-700 hover:bg-green-200"
                                        @click="checkIn(appointment.id)"
                                    >
                                        Check-in
                                    </button>
                                    <Link
                                        :href="
                                            route(
                                                'reception.by-doctor',
                                                appointment.doctor.id,
                                            )
                                        "
                                        class="rounded bg-gray-100 px-2 py-1 font-semibold text-gray-700 hover:bg-gray-200"
                                    >
                                        Ver doctor
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="appointments.length === 0">
                            <td
                                colspan="5"
                                class="px-4 py-10 text-center text-sm text-gray-500"
                            >
                                No hay turnos para este dia.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">
                    Profesionales con agenda
                </h2>
                <div
                    class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="doctor in doctors"
                        :key="doctor.id"
                        class="rounded-lg border border-gray-200 p-4"
                    >
                        <p class="font-semibold text-gray-900">
                            {{ doctor.name }}
                        </p>
                        <p class="text-sm text-gray-500">
                            {{ doctor.specialty || "Sin especialidad" }}
                        </p>
                        <p class="mt-2 text-sm text-gray-700">
                            Turnos: {{ doctor.today_appointments }}
                        </p>
                        <p class="text-sm text-gray-700">
                            Pendientes: {{ doctor.pending_appointments }}
                        </p>
                        <Link
                            :href="route('reception.by-doctor', doctor.id)"
                            class="mt-3 inline-block text-sm font-semibold text-indigo-600 hover:text-indigo-800"
                        >
                            Ver detalle
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
