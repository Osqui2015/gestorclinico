<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, computed } from "vue";

interface DoctorDashboardProps {
    doctor: any;
    period: string;
    statistics: {
        totalAppointments: number;
        completedAppointments: number;
        cancelledAppointments: number;
        pendingAppointments: number;
        patientsAttended: number;
        totalCoseguro: number;
    };
    topInsurance: {
        name: string;
        count: number;
    };
    coseguroByInsurance: Record<string, number>;
    ageDistribution: Record<string, number>;
    insuranceDistribution: Record<string, number>;
    appointmentsByDayOfWeek: Record<string, number>;
    topPatients: Array<{ patient: any; count: number }>;
}

const props = defineProps<DoctorDashboardProps>();

const selectedPeriod = ref(props.period);

const completionRate = computed(() => {
    if (props.statistics.totalAppointments === 0) return 0;
    return Math.round(
        (props.statistics.completedAppointments /
            props.statistics.totalAppointments) *
            100,
    );
});

const topInsuranceEntries = computed(() => {
    return Object.entries(props.insuranceDistribution).slice(0, 5);
});

const totalInsuranceCount = computed(() => {
    return Object.values(props.insuranceDistribution).reduce(
        (acc, count) => acc + count,
        0,
    );
});

const topCoseguroInsurances = computed(() => {
    return Object.entries(props.coseguroByInsurance).slice(0, 5);
});

const totalCoseguro = computed(() => {
    return Object.values(props.coseguroByInsurance).reduce(
        (acc, amount) => acc + amount,
        0,
    );
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
        minimumFractionDigits: 2,
    }).format(amount);
};

const chartColors = {
    completed: "#10b981",
    cancelled: "#ef4444",
    pending: "#f59e0b",
    primary: "#3b82f6",
};
</script>

<template>
    <Head title="Mis Reportes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    📊 Mis Reportes y Estadísticas
                </h2>
                <div class="flex gap-2">
                    <select
                        v-model="selectedPeriod"
                        @change="
                            $inertia.get(
                                route('doctor.reports', {
                                    period: selectedPeriod,
                                }),
                            )
                        "
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
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
                <!-- KPI Cards -->
                <div
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-6"
                >
                    <!-- Total Appointments -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Total Atenciones
                                </p>
                                <p
                                    class="mt-2 text-3xl font-bold text-gray-900"
                                >
                                    {{ statistics.totalAppointments }}
                                </p>
                            </div>
                            <div class="rounded-full bg-primary-100 p-3">
                                <svg
                                    class="h-6 w-6 text-primary-600"
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
                            </div>
                        </div>
                    </div>

                    <!-- Completed Appointments -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Completadas
                                </p>
                                <p
                                    class="mt-2 text-3xl font-bold text-success-600"
                                >
                                    {{ statistics.completedAppointments }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ completionRate }}% de cumplimiento
                                </p>
                            </div>
                            <div class="rounded-full bg-success-100 p-3">
                                <svg
                                    class="h-6 w-6 text-success-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Cancelled Appointments -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Canceladas
                                </p>
                                <p
                                    class="mt-2 text-3xl font-bold text-danger-600"
                                >
                                    {{ statistics.cancelledAppointments }}
                                </p>
                            </div>
                            <div class="rounded-full bg-danger-100 p-3">
                                <svg
                                    class="h-6 w-6 text-danger-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Appointments -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Pendientes
                                </p>
                                <p
                                    class="mt-2 text-3xl font-bold text-amber-600"
                                >
                                    {{ statistics.pendingAppointments }}
                                </p>
                            </div>
                            <div class="rounded-full bg-amber-100 p-3">
                                <svg
                                    class="h-6 w-6 text-amber-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Patients Attended -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Pacientes Atendidos
                                </p>
                                <p
                                    class="mt-2 text-3xl font-bold text-primary-700"
                                >
                                    {{ statistics.patientsAttended }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    únicos en el período
                                </p>
                            </div>
                            <div class="rounded-full bg-primary-100 p-3">
                                <svg
                                    class="h-6 w-6 text-primary-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 20h5V4H2v16h5m10 0v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2m12 0H7m10-11a4 4 0 11-8 0 4 4 0 018 0z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Coseguro -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Total Coseguros
                                </p>
                                <p
                                    class="mt-2 text-3xl font-bold text-emerald-700"
                                >
                                    {{
                                        formatCurrency(statistics.totalCoseguro)
                                    }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    recaudado en el período
                                </p>
                            </div>
                            <div class="rounded-full bg-emerald-100 p-3">
                                <svg
                                    class="h-6 w-6 text-emerald-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Highlight -->
                <div
                    class="mt-6 rounded-lg border border-primary-200 bg-primary-50 p-4"
                >
                    <p class="text-sm text-primary-900">
                        <span class="font-semibold"
                            >Obra social más atendida:</span
                        >
                        {{ topInsurance.name }}
                        <span class="ml-1 font-semibold"
                            >({{ topInsurance.count }})</span
                        >
                    </p>
                </div>

                <!-- Top 5 Obras Sociales por Coseguro -->
                <div
                    v-if="topCoseguroInsurances.length > 0"
                    class="mt-6 rounded-lg border-2 border-emerald-300 bg-emerald-50 p-6 shadow-md"
                >
                    <h3
                        class="mb-4 flex items-center text-lg font-bold text-emerald-900"
                    >
                        <span class="mr-2 text-2xl">💰</span>
                        Top 5 Obras Sociales por Coseguro Recaudado
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="(
                                [insurance, amount], index
                            ) in topCoseguroInsurances"
                            :key="insurance"
                            class="flex items-center justify-between rounded-lg bg-white p-4 shadow-sm"
                        >
                            <div class="flex items-center space-x-3">
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-full font-bold text-white"
                                    :class="{
                                        'bg-yellow-500': index === 0,
                                        'bg-gray-400': index === 1,
                                        'bg-orange-600': index === 2,
                                        'bg-emerald-600': index >= 3,
                                    }"
                                >
                                    {{ index + 1 }}
                                </span>
                                <div>
                                    <p class="font-semibold text-gray-900">
                                        {{ insurance }}
                                    </p>
                                    <p class="text-xs text-gray-600">
                                        {{
                                            (
                                                (amount / totalCoseguro) *
                                                100
                                            ).toFixed(1)
                                        }}% del total
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-emerald-700">
                                    {{ formatCurrency(amount) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 border-t border-emerald-200 pt-3">
                        <div class="flex justify-between text-sm font-bold">
                            <span class="text-emerald-900"
                                >Total Recaudado:</span
                            >
                            <span class="text-emerald-700">
                                {{ formatCurrency(statistics.totalCoseguro) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Charts Grid -->
                <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Patient Age Distribution -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">
                            👥 Distribución de Edades de Pacientes
                        </h3>
                        <div class="space-y-3">
                            <div
                                v-for="(count, range) in ageDistribution"
                                :key="range"
                            >
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700"
                                        >{{ range }} años</span
                                    >
                                    <span class="font-semibold text-gray-900">
                                        {{ count }}
                                    </span>
                                </div>
                                <div
                                    class="mt-1 h-2 overflow-hidden rounded-full bg-gray-200"
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

                    <!-- Appointments by Day of Week -->
                    <div class="rounded-lg bg-white p-6 shadow-md">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">
                            📅 Atenciones por Día
                        </h3>
                        <div class="space-y-3">
                            <div
                                v-for="(count, day) in appointmentsByDayOfWeek"
                                :key="day"
                            >
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700">{{ day }}</span>
                                    <span class="font-semibold text-gray-900">
                                        {{ count }}
                                    </span>
                                </div>
                                <div
                                    class="mt-1 h-2 overflow-hidden rounded-full bg-gray-200"
                                >
                                    <div
                                        :style="{
                                            width:
                                                Math.max(
                                                    ...Object.values(
                                                        appointmentsByDayOfWeek,
                                                    ),
                                                ) > 0
                                                    ? (count /
                                                          Math.max(
                                                              ...Object.values(
                                                                  appointmentsByDayOfWeek,
                                                              ),
                                                          )) *
                                                          100 +
                                                      '%'
                                                    : '0%',
                                        }"
                                        class="h-full bg-success-500"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Health Insurance Distribution -->
                <div class="mt-6 rounded-lg bg-white p-6 shadow-md">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">
                        🏥 Distribución de Obras Sociales
                    </h3>
                    <div
                        class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
                    >
                        <div
                            v-for="(count, insurance) in insuranceDistribution"
                            :key="insurance"
                            class="rounded-lg border border-gray-200 bg-gray-50 p-4"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-600"
                                    >
                                        {{ insurance }}
                                    </p>
                                    <p
                                        class="mt-1 text-2xl font-bold text-gray-900"
                                    >
                                        {{ count }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">
                                        {{
                                            Object.values(
                                                insuranceDistribution,
                                            ).reduce((a, b) => a + b, 0) > 0
                                                ? (
                                                      (count /
                                                          Object.values(
                                                              insuranceDistribution,
                                                          ).reduce(
                                                              (a, b) => a + b,
                                                              0,
                                                          )) *
                                                      100
                                                  ).toFixed(1)
                                                : 0
                                        }}%
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top 5 Health Insurances -->
                <div class="mt-6 rounded-lg bg-white p-6 shadow-md">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">
                        🥇 Top 5 Obras Sociales Más Atendidas
                    </h3>

                    <div
                        v-if="topInsuranceEntries.length === 0"
                        class="py-6 text-sm text-gray-500"
                    >
                        Sin datos de obras sociales para este período.
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="(
                                [insurance, count], index
                            ) in topInsuranceEntries"
                            :key="insurance"
                            class="rounded-lg border border-gray-200 bg-gray-50 p-4"
                        >
                            <div class="mb-2 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700"
                                    >
                                        {{ index + 1 }}
                                    </span>
                                    <p
                                        class="text-sm font-semibold text-gray-900"
                                    >
                                        {{ insurance }}
                                    </p>
                                </div>
                                <p class="text-sm font-bold text-gray-900">
                                    {{ count }}
                                </p>
                            </div>

                            <div
                                class="h-2 overflow-hidden rounded-full bg-gray-200"
                            >
                                <div
                                    class="h-full bg-primary-500"
                                    :style="{
                                        width:
                                            totalInsuranceCount > 0
                                                ? (
                                                      (count /
                                                          totalInsuranceCount) *
                                                      100
                                                  ).toFixed(1) + '%'
                                                : '0%',
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Patients -->
                <div class="mt-6 rounded-lg bg-white shadow-md">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            ⭐ Pacientes con Más Atenciones
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-gray-200 bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left font-semibold text-gray-900"
                                    >
                                        Paciente
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left font-semibold text-gray-900"
                                    >
                                        DNI
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center font-semibold text-gray-900"
                                    >
                                        Atenciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr
                                    v-for="item in topPatients"
                                    :key="item.patient.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-6 py-4 text-gray-900">
                                        {{ item.patient.first_name }}
                                        {{ item.patient.last_name }}
                                    </td>
                                    <td
                                        class="px-6 py-4 font-mono text-gray-600"
                                    >
                                        {{ item.patient.dni }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="inline-block rounded-full bg-primary-100 px-3 py-1 text-sm font-semibold text-primary-800"
                                        >
                                            {{ item.count }}
                                        </span>
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
