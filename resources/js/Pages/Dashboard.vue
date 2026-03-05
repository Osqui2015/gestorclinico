<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import MiniCalendar from "@/Components/MiniCalendar.vue";
import AppointmentsForDay from "@/Components/AppointmentsForDay.vue";
import DaySummary from "@/Components/DaySummary.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";

interface Appointment {
    id: number;
    scheduled_at: string;
    status: string;
    reason: string;
    notes: string;
    doctor?: { id: number; name: string };
    patient?: {
        id: number;
        first_name?: string;
        last_name?: string;
        full_name: string;
        dni?: string;
        age?: number;
    };
}

const page = usePage();
const props = defineProps({
    appointments: {
        type: Array<Appointment>,
        default: () => [],
    },
    todayDate: {
        type: String,
        default: () => {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, "0");
            const day = String(today.getDate()).padStart(2, "0");
            return `${year}-${month}-${day}`;
        },
    },
});

const selectedDate = ref(props.todayDate);

const userRole = page.props.auth?.user?.role || "doctor";

const getLocalDateKeyFromDateTime = (dateTime: string): string => {
    const date = new Date(dateTime);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
};

// Count appointments for selected date with pending status
const appointmentsCount = computed(() => {
    return props.appointments.filter((apt) => {
        const aptDate = getLocalDateKeyFromDateTime(apt.scheduled_at);
        return aptDate === selectedDate.value && apt.status === "pending";
    }).length;
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        📅 Dashboard - Mi Agenda
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Gestiona tus citas y pacientes para el día de hoy.
                    </p>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Two column layout: Calendar on left, Appointments on right -->
                <div
                    class="grid grid-cols-1 gap-6 md:grid-cols-3 lg:grid-cols-4"
                >
                    <!-- Left Column: Mini Calendar & Summary -->
                    <div class="space-y-6 md:col-span-1">
                        <MiniCalendar
                            :selected-date="selectedDate"
                            @select-date="(date) => (selectedDate = date)"
                        />
                        <DaySummary
                            :appointments-count="appointmentsCount"
                            :selected-date="selectedDate"
                        />
                    </div>

                    <!-- Right Column: Appointments for selected day -->
                    <div class="md:col-span-2 lg:col-span-3">
                        <AppointmentsForDay
                            :appointments="appointments"
                            :selected-date="selectedDate"
                            :user-role="userRole"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
