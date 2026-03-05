<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface Doctor {
    id: number;
    name: string;
    specialty?: string;
}

interface Schedule {
    id: number;
    doctor_id: number;
    day_of_week: string;
    start_time: string;
    end_time: string;
    slot_duration: number;
    is_active: boolean;
    day_name: string;
    doctor?: Doctor;
}

interface Props {
    schedules: Schedule[];
    doctors: Doctor[];
    selectedDoctorId?: number;
}

const props = defineProps<Props>();

const daysOrder = {
    monday: 1,
    tuesday: 2,
    wednesday: 3,
    thursday: 4,
    friday: 5,
    saturday: 6,
    sunday: 7,
};

const sortedSchedules = props.schedules.sort((a, b) => {
    return (
        daysOrder[a.day_of_week as keyof typeof daysOrder] -
        daysOrder[b.day_of_week as keyof typeof daysOrder]
    );
});

const deleteSchedule = (id: number) => {
    if (confirm("¿Está seguro de eliminar este horario?")) {
        router.delete(route("doctor-schedules.destroy", id));
    }
};

const toggleActive = (schedule: Schedule) => {
    router.patch(route("doctor-schedules.update", schedule.id), {
        doctor_id: schedule.doctor_id,
        day_of_week: schedule.day_of_week,
        start_time: schedule.start_time,
        end_time: schedule.end_time,
        slot_duration: schedule.slot_duration,
        is_active: !schedule.is_active,
    });
};
</script>

<template>
    <Head title="Horarios de Atención" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Horarios de Atención
                </h2>
                <div class="flex gap-3">
                    <Link
                        :href="route('doctor-exceptions.index')"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Ver Excepciones
                    </Link>
                    <Link
                        :href="route('doctor-schedules.create')"
                        class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700"
                    >
                        + Nuevo Horario
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Info Box -->
                <div
                    class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4"
                >
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg
                                class="h-5 w-5 text-blue-400"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">
                                Gestión de Horarios
                            </h3>
                            <p class="mt-1 text-sm text-blue-700">
                                Configure sus horarios de atención por día de la
                                semana. Los pacientes solo podrán agendar citas
                                en los horarios configurados aquí.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Schedules List -->
                <div class="rounded-lg bg-white shadow-sm">
                    <div
                        v-if="sortedSchedules.length === 0"
                        class="p-12 text-center"
                    >
                        <svg
                            class="mx-auto h-12 w-12 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                            No hay horarios configurados
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Comience agregando sus horarios de atención.
                        </p>
                        <div class="mt-6">
                            <Link
                                :href="route('doctor-schedules.create')"
                                class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700"
                            >
                                + Agregar Horario
                            </Link>
                        </div>
                    </div>

                    <div v-else class="overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Día
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Horario
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Duración de Cita
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Estado
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="schedule in sortedSchedules"
                                    :key="schedule.id"
                                    :class="{
                                        'bg-gray-50': !schedule.is_active,
                                    }"
                                >
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900"
                                    >
                                        {{ schedule.day_name }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-700"
                                    >
                                        {{ schedule.start_time }} -
                                        {{ schedule.end_time }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-700"
                                    >
                                        {{ schedule.slot_duration }} minutos
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <button
                                            @click="toggleActive(schedule)"
                                            :class="
                                                schedule.is_active
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-gray-100 text-gray-800'
                                            "
                                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold transition-colors hover:opacity-75"
                                        >
                                            {{
                                                schedule.is_active
                                                    ? "Activo"
                                                    : "Inactivo"
                                            }}
                                        </button>
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'doctor-schedules.edit',
                                                    schedule.id,
                                                )
                                            "
                                            class="mr-3 text-primary-600 hover:text-primary-900"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            @click="deleteSchedule(schedule.id)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
