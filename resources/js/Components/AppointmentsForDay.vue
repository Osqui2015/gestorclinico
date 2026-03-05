<script setup lang="ts">
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

interface Patient {
    id: number;
    first_name?: string;
    last_name?: string;
    full_name: string;
    dni?: string;
    age?: number;
}

interface Appointment {
    id: number;
    scheduled_at: string;
    status: string;
    reason: string;
    notes: string;
    doctor?: { id: number; name: string };
    patient?: Patient;
}

interface Props {
    appointments: Appointment[];
    selectedDate: string;
    userRole?: string;
}

const props = defineProps<Props>();

const parseLocalDate = (dateString: string): Date => {
    const [year, month, day] = dateString.split("-").map(Number);
    return new Date(year, month - 1, day);
};

const getLocalDateKeyFromDateTime = (dateTime: string): string => {
    const date = new Date(dateTime);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
};

const appointmentsForDay = computed(() => {
    return props.appointments.filter((apt) => {
        const aptDate = getLocalDateKeyFromDateTime(apt.scheduled_at);
        return aptDate === props.selectedDate;
    });
});

const getStatusColor = (status: string): string => {
    return (
        {
            pending: "yellow",
            confirmed: "green",
            completed: "blue",
            cancelled: "red",
        }[status] || "gray"
    );
};

const getStatusLabel = (status: string): string => {
    return (
        {
            pending: "Pendiente",
            confirmed: "Confirmada",
            completed: "Completada",
            cancelled: "Cancelada",
        }[status] || status
    );
};

const formatTime = (datetime: string): string => {
    const date = new Date(datetime);
    return date.toLocaleTimeString("es-ES", {
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        Citas -
                        {{
                            parseLocalDate(selectedDate).toLocaleDateString(
                                "es-ES",
                                {
                                    weekday: "long",
                                    year: "numeric",
                                    month: "long",
                                    day: "numeric",
                                },
                            )
                        }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Lista de pacientes programados para hoy
                    </p>
                </div>
                <div class="flex gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50"
                    >
                        🔍 Filtrar
                    </button>
                    <Link
                        :href="route('appointments.create')"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-primary-700"
                    >
                        ➕ Nueva Cita
                    </Link>
                </div>
            </div>
        </div>

        <div class="divide-y divide-gray-200">
            <div
                v-if="appointmentsForDay.length === 0"
                class="p-12 text-center"
            >
                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100"
                >
                    <span class="text-3xl">📋</span>
                </div>
                <p class="mt-4 text-base font-medium text-gray-900">
                    No hay citas para este día
                </p>
                <p class="mt-1 text-sm text-gray-500">
                    Selecciona otro día o crea una nueva cita
                </p>
            </div>

            <div
                v-for="appointment in appointmentsForDay"
                :key="appointment.id"
                class="p-6 transition-colors hover:bg-gray-50"
            >
                <div class="flex items-start gap-4">
                    <!-- Time Badge -->
                    <div
                        class="flex flex-col items-center rounded-lg border-2 border-gray-200 px-4 py-2"
                    >
                        <div class="text-xs font-medium text-gray-500">🕐</div>
                        <div class="mt-1 text-lg font-bold text-gray-900">
                            {{ formatTime(appointment.scheduled_at) }}
                        </div>
                        <span
                            :class="[
                                'mt-2 inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                getStatusColor(appointment.status) ===
                                    'yellow' &&
                                    'bg-warning-100 text-warning-700',
                                getStatusColor(appointment.status) ===
                                    'green' &&
                                    'bg-success-100 text-success-700',
                                getStatusColor(appointment.status) === 'blue' &&
                                    'bg-primary-100 text-primary-700',
                                getStatusColor(appointment.status) === 'red' &&
                                    'bg-danger-100 text-danger-700',
                                getStatusColor(appointment.status) === 'gray' &&
                                    'bg-gray-100 text-gray-700',
                            ]"
                        >
                            {{ getStatusLabel(appointment.status) }}
                        </span>
                    </div>

                    <!-- Appointment Details -->
                    <div class="flex-1">
                        <!-- Patient Name -->
                        <div
                            v-if="appointment.patient"
                            class="flex items-center gap-2"
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200"
                            >
                                <span class="text-lg">👤</span>
                            </div>
                            <div>
                                <div class="text-base font-bold text-gray-900">
                                    {{ appointment.patient.full_name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    <span v-if="appointment.patient.dni"
                                        >DNI:
                                        {{ appointment.patient.dni }}</span
                                    >
                                    <span
                                        v-if="
                                            appointment.patient.dni &&
                                            appointment.patient.age
                                        "
                                    >
                                        •
                                    </span>
                                    <span v-if="appointment.patient.age"
                                        >{{
                                            appointment.patient.age
                                        }}
                                        años</span
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Doctor (for admin) -->
                        <div
                            v-if="userRole === 'admin' && appointment.doctor"
                            class="mt-2 text-sm text-gray-600"
                        >
                            👨‍⚕️ Dr. {{ appointment.doctor.name }}
                        </div>

                        <!-- Reason -->
                        <div v-if="appointment.reason" class="mt-3">
                            <div
                                class="text-xs font-semibold uppercase text-gray-500"
                            >
                                MOTIVO
                            </div>
                            <div class="mt-1 text-sm text-gray-900">
                                {{ appointment.reason }}
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="appointment.notes" class="mt-2">
                            <div
                                class="text-xs font-semibold uppercase text-gray-500"
                            >
                                NOTAS
                            </div>
                            <div class="mt-1 text-sm text-gray-700">
                                {{ appointment.notes }}
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-2">
                        <Link
                            :href="route('appointments.show', appointment.id)"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-primary-600 bg-white px-4 py-2 text-sm font-semibold text-primary-600 transition-colors hover:bg-primary-50"
                        >
                            Ver detalles →
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
