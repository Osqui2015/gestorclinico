<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {
    format,
    startOfMonth,
    endOfMonth,
    eachDayOfInterval,
    isSameMonth,
    isSameDay,
    isToday,
    addMonths,
    subMonths,
    startOfWeek,
    endOfWeek,
} from "date-fns";
import { es } from "date-fns/locale";

interface Doctor {
    id: number;
    name: string;
}

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
}

interface Appointment {
    id: number;
    scheduled_at: string;
    status: "pending" | "confirmed" | "completed" | "cancelled";
    reason: string | null;
    doctor: Doctor;
    patient: Patient;
}

interface Props {
    appointments: Appointment[];
    doctors: Doctor[];
    currentMonth: string;
    currentDoctorFilter: number | null;
}

const props = defineProps<Props>();
const page = usePage();
const isDoctor = computed(() => page.props.auth?.user?.role === "doctor");

const currentMonth = ref(new Date(`${props.currentMonth}-01T00:00:00`));
const selectedDoctor = ref<number | null>(props.currentDoctorFilter ?? null);

// Get calendar days for current month view
const calendarDays = computed(() => {
    const monthStart = startOfMonth(currentMonth.value);
    const monthEnd = endOfMonth(currentMonth.value);

    // Get start of calendar (may include days from previous month)
    const calendarStart = startOfWeek(monthStart, { weekStartsOn: 0 });

    // Get end of calendar (may include days from next month)
    const calendarEnd = endOfWeek(monthEnd, { weekStartsOn: 0 });

    return eachDayOfInterval({ start: calendarStart, end: calendarEnd });
});

// Get appointments for a specific day
const getAppointmentsForDay = (day: Date) => {
    return props.appointments.filter((appointment) => {
        const appointmentDate = new Date(appointment.scheduled_at);
        return (
            isSameDay(appointmentDate, day) &&
            (selectedDoctor.value === null ||
                appointment.doctor.id === selectedDoctor.value)
        );
    });
};

// Get color based on status
const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: "bg-amber-400",
        confirmed: "bg-blue-500",
        completed: "bg-emerald-500",
        cancelled: "bg-red-400",
    };
    return colors[status] || "bg-gray-400";
};

// Navigate months
const previousMonth = () => {
    currentMonth.value = subMonths(currentMonth.value, 1);
    loadAppointments();
};

const nextMonth = () => {
    currentMonth.value = addMonths(currentMonth.value, 1);
    loadAppointments();
};

const goToday = () => {
    currentMonth.value = new Date();
    loadAppointments();
};

// Load appointments for current month
const loadAppointments = () => {
    router.get(
        route("appointments.calendar"),
        {
            month: format(currentMonth.value, "yyyy-MM"),
            doctor_id: selectedDoctor.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

// Watch doctor filter
watch(selectedDoctor, () => {
    loadAppointments();
});

// Format time
const formatTime = (dateString: string) => {
    return format(new Date(dateString), "HH:mm", { locale: es });
};

// Get day of week labels
const weekDays = [
    "Domingo",
    "Lunes",
    "Martes",
    "Miércoles",
    "Jueves",
    "Viernes",
    "Sábado",
];
</script>

<template>
    <Head title="Calendario de Citas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    📆 Calendario de Citas
                </h2>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white shadow-sm">
                    <!-- Calendar Header -->
                    <div class="border-b border-gray-200 p-6">
                        <div
                            class="flex flex-wrap items-center justify-between gap-4"
                        >
                            <!-- Month/Year Display and Navigation -->
                            <div class="flex items-center gap-4">
                                <h3 class="text-2xl font-bold text-gray-900">
                                    {{
                                        format(currentMonth, "MMMM yyyy", {
                                            locale: es,
                                        })
                                    }}
                                </h3>
                                <div class="flex gap-2">
                                    <button
                                        @click="previousMonth"
                                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                    >
                                        ← Anterior
                                    </button>
                                    <button
                                        @click="goToday"
                                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                    >
                                        Hoy
                                    </button>
                                    <button
                                        @click="nextMonth"
                                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                    >
                                        Siguiente →
                                    </button>
                                </div>
                            </div>

                            <!-- Doctor Filter -->
                            <div class="flex items-center gap-3">
                                <label
                                    class="text-sm font-medium text-gray-700"
                                >
                                    Filtrar por médico:
                                </label>
                                <select
                                    v-model="selectedDoctor"
                                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm"
                                >
                                    <option v-if="!isDoctor" :value="null">
                                        Todos los médicos
                                    </option>
                                    <option
                                        v-for="doctor in doctors"
                                        :key="doctor.id"
                                        :value="doctor.id"
                                    >
                                        {{ doctor.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div
                            class="mt-4 flex flex-wrap items-center gap-4 text-sm"
                        >
                            <div class="flex items-center gap-2">
                                <div class="h-3 w-3 rounded bg-amber-400"></div>
                                <span class="text-gray-600">Pendiente</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="h-3 w-3 rounded bg-blue-500"></div>
                                <span class="text-gray-600">Confirmada</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div
                                    class="h-3 w-3 rounded bg-emerald-500"
                                ></div>
                                <span class="text-gray-600">Completada</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="h-3 w-3 rounded bg-red-400"></div>
                                <span class="text-gray-600">Cancelada</span>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="p-4">
                        <!-- Week Day Headers -->
                        <div class="mb-2 grid grid-cols-7 gap-px">
                            <div
                                v-for="day in weekDays"
                                :key="day"
                                class="py-2 text-center text-sm font-semibold text-gray-700"
                            >
                                {{ day }}
                            </div>
                        </div>

                        <!-- Calendar Days -->
                        <div
                            class="grid grid-cols-7 gap-px border border-gray-200 bg-gray-200"
                        >
                            <div
                                v-for="day in calendarDays"
                                :key="day.toString()"
                                class="min-h-[120px] bg-white p-2"
                                :class="{
                                    'bg-gray-50': !isSameMonth(
                                        day,
                                        currentMonth,
                                    ),
                                    'ring-2 ring-primary-500 ring-inset':
                                        isToday(day),
                                }"
                            >
                                <!-- Day Number -->
                                <div
                                    class="mb-1 flex items-center justify-between"
                                >
                                    <span
                                        class="flex h-7 w-7 items-center justify-center rounded-full text-sm font-semibold"
                                        :class="{
                                            'bg-primary-600 text-white':
                                                isToday(day),
                                            'text-gray-900':
                                                isSameMonth(
                                                    day,
                                                    currentMonth,
                                                ) && !isToday(day),
                                            'text-gray-400': !isSameMonth(
                                                day,
                                                currentMonth,
                                            ),
                                        }"
                                    >
                                        {{ format(day, "d") }}
                                    </span>
                                    <span
                                        v-if="
                                            getAppointmentsForDay(day).length >
                                            0
                                        "
                                        class="text-xs font-medium text-gray-500"
                                    >
                                        {{ getAppointmentsForDay(day).length }}
                                    </span>
                                </div>

                                <!-- Appointments -->
                                <div class="space-y-1">
                                    <div
                                        v-for="appointment in getAppointmentsForDay(
                                            day,
                                        ).slice(0, 3)"
                                        :key="appointment.id"
                                        class="cursor-pointer rounded px-2 py-1 text-xs font-medium text-white hover:opacity-80"
                                        :class="
                                            getStatusColor(appointment.status)
                                        "
                                        :title="`${formatTime(appointment.scheduled_at)} - ${appointment.patient.first_name} ${appointment.patient.last_name} (${appointment.doctor.name})`"
                                        @click="
                                            $inertia.visit(
                                                route(
                                                    'appointments.show',
                                                    appointment.id,
                                                ),
                                            )
                                        "
                                    >
                                        <div class="truncate">
                                            {{
                                                formatTime(
                                                    appointment.scheduled_at,
                                                )
                                            }}
                                            {{ appointment.patient.first_name }}
                                        </div>
                                    </div>

                                    <!-- More appointments indicator -->
                                    <div
                                        v-if="
                                            getAppointmentsForDay(day).length >
                                            3
                                        "
                                        class="text-xs font-medium text-gray-500"
                                    >
                                        +{{
                                            getAppointmentsForDay(day).length -
                                            3
                                        }}
                                        más
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="border-t border-gray-200 bg-gray-50 p-4">
                        <div class="flex flex-wrap gap-6 text-sm">
                            <div>
                                <span class="font-semibold text-gray-700"
                                    >Total de citas:</span
                                >
                                <span class="ml-2 text-gray-900">{{
                                    appointments.length
                                }}</span>
                            </div>
                            <div>
                                <span class="font-semibold text-gray-700"
                                    >Pendientes:</span
                                >
                                <span class="ml-2 text-amber-600">
                                    {{
                                        appointments.filter(
                                            (a) => a.status === "pending",
                                        ).length
                                    }}
                                </span>
                            </div>
                            <div>
                                <span class="font-semibold text-gray-700"
                                    >Confirmadas:</span
                                >
                                <span class="ml-2 text-blue-600">
                                    {{
                                        appointments.filter(
                                            (a) => a.status === "confirmed",
                                        ).length
                                    }}
                                </span>
                            </div>
                            <div>
                                <span class="font-semibold text-gray-700"
                                    >Completadas:</span
                                >
                                <span class="ml-2 text-emerald-600">
                                    {{
                                        appointments.filter(
                                            (a) => a.status === "completed",
                                        ).length
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
