<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref } from "vue";

interface AdminDashboardProps {
    period: string;
    statistics: {
        totalAppointments: number;
        totalPatients: number;
        totalDoctors: number;
        averageAppointmentsPerDoctor: number;
    };
    appointmentsByDoctor: Array<{
        doctor: { id: number; name: string; specialty: string };
        totalAppointments: number;
        completed: number;
        cancelled: number;
        pending: number;
    }>;
    appointmentsBySpecialty: Array<{
        specialty: string;
        totalAppointments: number;
        uniquePatients: number;
    }>;
    ageDistribution: Record<string, number>;
    insuranceDistribution: Record<string, number>;
    appointmentsTrend: Record<string, number>;
    doctorPerformance: Array<{
        id: number;
        name: string;
        specialty: string;
        totalAppointments: number;
        completedRate: number;
    }>;
}

const props = defineProps<AdminDashboardProps>();

const selectedPeriod = ref(props.period);
</script>

<template>
    <Head title="Reportes Administrativos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2
                    class="text-xl font-semibold leading-tight text-gray-800 "
                >
                    📊 Reportes Administrativos
                </h2>
                <div class="flex gap-2">
                    <select
                        v-model="selectedPeriod"
                        @change="
                            $inertia.get(
                                route('admin.reports', {
                                    period: selectedPeriod,
                                }),
                            )
                        "
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50   "
                    >
                        <option value="week">📅 Esta Semana</option>
                        <option value="month">📆 Este Mes</option>
                        <option value="quarter">📊 Este Trimestre</option>
                        <option value="year">📈 Este Año</option>
                    </select>
                </div>
            </div>
        </template>

        <div class="space-y-6 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Main KPI Cards -->
                <div
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4"
                >
                    <!-- Total Appointments -->
                    <div
                        class="rounded-lg bg-white p-6 shadow-md "
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-600 "
                                >
                                    Total Atenciones
                                </p>
                                <p
                                    class="mt-2 text-3xl font-bold text-gray-900 "
                                >
                                    {{ statistics.totalAppointments }}
                                </p>
                            </div>
                            <div
                                class="rounded-full bg-primary-100 p-3 "
                            >
                                <svg
                                    class="h-6 w-6 text-primary-600 "
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Patients -->
                    <div
                        class="rounded-lg bg-white p-6 shadow-md "
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-600 "
                                >
                                    Pacientes Únicos
                                </p>
                                <p
                                    class="mt-2 text-3xl font-bold text-success-600"
                                >
                                    {{ statistics.totalPatients }}
                                </p>
                            </div>
                            <div
                                class="rounded-full bg-success-100 p-3 "
                            >
                                <svg
                                    class="h-6 w-6 text-success-600 "
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0H9m6 0h-2m2 0l-2-2m2 2l2-2"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Doctors -->
                    <div
                        class="rounded-lg bg-white p-6 shadow-md "
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-600 "
                                >
                                    Doctores Activos
                                </p>
                                <p
                                    class="mt-2 text-3xl font-bold text-primary-600"
                                >
                                    {{ statistics.totalDoctors }}
                                </p>
                            </div>
                            <div
                                class="rounded-full bg-primary-100 p-3 "
                            >
                                <svg
                                    class="h-6 w-6 text-primary-600 "
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Avg per Doctor -->
                    <div
                        class="rounded-lg bg-white p-6 shadow-md "
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-600 "
                                >
                                    Promedio por Doctor
                                </p>
                                <p
                                    class="mt-2 text-3xl font-bold text-warning-600"
                                >
                                    {{
                                        statistics.averageAppointmentsPerDoctor
                                    }}
                                </p>
                            </div>
                            <div
                                class="rounded-full bg-warning-100 p-3 "
                            >
                                <svg
                                    class="h-6 w-6 text-warning-600 "
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Doctor Performance Table -->
                <div
                    class="mt-8 rounded-lg bg-white shadow-md "
                >
                    <div
                        class="border-b border-gray-200 px-6 py-4 "
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 "
                        >
                            👨‍⚕️ Rendimiento de Doctores
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="border-b border-gray-200 bg-gray-50  "
                            >
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left font-semibold text-gray-900 "
                                    >
                                        Doctor
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left font-semibold text-gray-900 "
                                    >
                                        Especialidad
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center font-semibold text-gray-900 "
                                    >
                                        Total Atenciones
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center font-semibold text-gray-900 "
                                    >
                                        Tasa Completitud
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-200 "
                            >
                                <tr
                                    v-for="doctor in doctorPerformance"
                                    :key="doctor.id"
                                    class="hover:bg-gray-50 "
                                >
                                    <td
                                        class="px-6 py-4 font-semibold text-gray-900 "
                                    >
                                        {{ doctor.name }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-gray-600 "
                                    >
                                        {{ doctor.specialty }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="inline-block rounded-full bg-primary-100 px-3 py-1 font-semibold text-primary-800  "
                                        >
                                            {{ doctor.totalAppointments }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div
                                            class="flex items-center justify-center"
                                        >
                                            <div
                                                class="w-16 rounded-full bg-gray-200 "
                                            >
                                                <div
                                                    :style="{
                                                        width:
                                                            doctor.completedRate +
                                                            '%',
                                                    }"
                                                    class="rounded-full bg-success-500 py-1 text-center text-xs font-semibold text-white"
                                                >
                                                    {{ doctor.completedRate }}%
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Appointments by Specialty -->
                <div
                    class="mt-8 rounded-lg bg-white shadow-md "
                >
                    <div
                        class="border-b border-gray-200 px-6 py-4 "
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 "
                        >
                            🏥 Atenciones por Especialidad
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="border-b border-gray-200 bg-gray-50  "
                            >
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left font-semibold text-gray-900 "
                                    >
                                        Especialidad
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center font-semibold text-gray-900 "
                                    >
                                        Total Atenciones
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center font-semibold text-gray-900 "
                                    >
                                        Pacientes Únicos
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-200 "
                            >
                                <tr
                                    v-for="specialty in appointmentsBySpecialty"
                                    :key="specialty.specialty"
                                    class="hover:bg-gray-50 "
                                >
                                    <td
                                        class="px-6 py-4 font-semibold text-gray-900 "
                                    >
                                        {{ specialty.specialty }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="inline-block rounded-full bg-primary-100 px-3 py-1 font-semibold text-primary-800  "
                                        >
                                            {{ specialty.totalAppointments }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="inline-block rounded-full bg-success-100 px-3 py-1 font-semibold text-success-800  "
                                        >
                                            {{ specialty.uniquePatients }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Grid of Charts -->
                <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Patient Age Distribution -->
                    <div
                        class="rounded-lg bg-white p-6 shadow-md "
                    >
                        <h3
                            class="mb-4 text-lg font-semibold text-gray-900 "
                        >
                            👥 Distribución de Edades
                        </h3>
                        <div class="space-y-3">
                            <div
                                v-for="(count, range) in ageDistribution"
                                :key="range"
                            >
                                <div class="flex justify-between text-sm">
                                    <span
                                        class="text-gray-700 "
                                        >{{ range }} años</span
                                    >
                                    <span
                                        class="font-semibold text-gray-900 "
                                    >
                                        {{ count }}
                                    </span>
                                </div>
                                <div
                                    class="mt-1 h-2 overflow-hidden rounded-full bg-gray-200 "
                                >
                                    <div
                                        :style="{
                                            width:
                                                Object.values(
                                                    ageDistribution,
                                                ).reduce((a, b) => a + b, 0) > 0
                                                    ? (count /
                                                          Object.values(
                                                              ageDistribution,
                                                          ).reduce(
                                                              (a, b) => a + b,
                                                              0,
                                                          )) *
                                                          100 +
                                                      '%'
                                                    : '0%',
                                        }"
                                        class="h-full bg-primary-500"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Insurance Distribution -->
                    <div
                        class="rounded-lg bg-white p-6 shadow-md "
                    >
                        <h3
                            class="mb-4 text-lg font-semibold text-gray-900 "
                        >
                            🏥 Obras Sociales Principales
                        </h3>
                        <div class="space-y-2">
                            <div
                                v-for="(insuranceEntry, idx) in Object.entries(
                                    insuranceDistribution,
                                ).slice(0, 5)"
                                :key="idx"
                            >
                                <div class="flex justify-between text-sm">
                                    <span
                                        class="text-gray-700 "
                                    >
                                        {{
                                            (
                                                insuranceEntry as [
                                                    string,
                                                    number,
                                                ]
                                            )[0]
                                        }}
                                    </span>
                                    <span
                                        class="font-semibold text-gray-900 "
                                    >
                                        {{
                                            (
                                                insuranceEntry as [
                                                    string,
                                                    number,
                                                ]
                                            )[1]
                                        }}
                                    </span>
                                </div>
                                <div
                                    class="mt-1 h-2 overflow-hidden rounded-full bg-gray-200 "
                                >
                                    <div
                                        :style="{
                                            width:
                                                Object.values(
                                                    insuranceDistribution,
                                                ).reduce((a, b) => a + b, 0) > 0
                                                    ? ((
                                                          insuranceEntry as [
                                                              string,
                                                              number,
                                                          ]
                                                      )[1] /
                                                          Object.values(
                                                              insuranceDistribution,
                                                          ).reduce(
                                                              (a, b) => a + b,
                                                              0,
                                                          )) *
                                                          100 +
                                                      '%'
                                                    : '0%',
                                        }"
                                        class="h-full bg-primary-500"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appointments Trend -->
                <div
                    class="mt-8 rounded-lg bg-white p-6 shadow-md "
                >
                    <h3
                        class="mb-4 text-lg font-semibold text-gray-900 "
                    >
                        📈 Tendencia de Atenciones
                    </h3>
                    <div class="flex items-end gap-2">
                        <div
                            v-for="(count, date) in appointmentsTrend"
                            :key="date"
                            class="flex-1 flex flex-col items-center"
                        >
                            <div
                                :style="{
                                    height:
                                        Math.max(
                                            ...Object.values(appointmentsTrend),
                                        ) > 0
                                            ? (count /
                                                  Math.max(
                                                      ...Object.values(
                                                          appointmentsTrend,
                                                      ),
                                                  )) *
                                                  200 +
                                              'px'
                                            : '10px',
                                }"
                                class="w-full rounded-t bg-primary-500 transition-all hover:bg-primary-600"
                            ></div>
                            <p
                                class="mt-2 text-xs text-gray-600 "
                            >
                                {{ date }}
                            </p>
                            <p
                                class="text-xs font-semibold text-gray-900 "
                            >
                                {{ count }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
