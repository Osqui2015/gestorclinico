<script setup lang="ts">
import { ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface ReportStats {
    total_patients_attended: number;
    total_revenue: number;
    pending_payment: number;
    average_appointment_time: number;
    hospitalization_rate: number;
}

interface MonthlyStats {
    month: string;
    patients: number;
    revenue: number;
    appointments: number;
}

interface Props {
    stats: ReportStats;
    monthly_stats: MonthlyStats[];
}

const props = defineProps<Props>();

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
    }).format(value);
};

const formatNumber = (value: number) => {
    return new Intl.NumberFormat("es-AR").format(value);
};
</script>

<template>
    <Head title="Reportes Avanzados" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Reportes Avanzados
                </h1>
                <p class="text-gray-600 mt-2">
                    Panel de análisis e indicadores clave
                </p>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-5 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm mb-1">
                        Pacientes Atendidos
                    </p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ formatNumber(stats.total_patients_attended) }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm mb-1">Ingresos Totales</p>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ formatCurrency(stats.total_revenue) }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm mb-1">En Cobranza</p>
                    <p class="text-2xl font-bold text-orange-600">
                        {{ formatCurrency(stats.pending_payment) }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm mb-1">Tiempo Prom. Cita</p>
                    <p class="text-2xl font-bold text-purple-600">
                        {{ Math.round(stats.average_appointment_time) }} min
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm mb-1">
                        Tasa Hospitalización
                    </p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ (stats.hospitalization_rate * 100).toFixed(1) }}%
                    </p>
                </div>
            </div>

            <!-- Reports Grid -->
            <div class="grid grid-cols-2 gap-6 mb-8">
                <!-- C2 Report -->
                <Link
                    :href="route('advanced-reports.c2')"
                    class="bg-white rounded-lg shadow-md p-8 hover:shadow-lg transition border-l-4 border-blue-600"
                >
                    <div class="flex items-start gap-4">
                        <div class="text-4xl">📋</div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Reporte C2
                            </h3>
                            <p class="text-gray-600 text-sm mt-1">
                                Consultaciones, derivaciones y procedimientos
                                según REFES
                            </p>
                        </div>
                    </div>
                </Link>

                <!-- Epidemiology Report -->
                <Link
                    :href="route('advanced-reports.epidemiology')"
                    class="bg-white rounded-lg shadow-md p-8 hover:shadow-lg transition border-l-4 border-green-600"
                >
                    <div class="flex items-start gap-4">
                        <div class="text-4xl">🦠</div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Reporte Epidemiológico
                            </h3>
                            <p class="text-gray-600 text-sm mt-1">
                                Enfermedades notificables, distribución por edad
                                y género
                            </p>
                        </div>
                    </div>
                </Link>

                <!-- Quality Indicators -->
                <Link
                    :href="route('advanced-reports.quality-indicators')"
                    class="bg-white rounded-lg shadow-md p-8 hover:shadow-lg transition border-l-4 border-purple-600"
                >
                    <div class="flex items-start gap-4">
                        <div class="text-4xl">⭐</div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Indicadores de Calidad
                            </h3>
                            <p class="text-gray-600 text-sm mt-1">
                                Tiempos de espera, satisfacción, productividad
                                del equipo
                            </p>
                        </div>
                    </div>
                </Link>

                <!-- Insurance Report -->
                <Link
                    :href="route('advanced-reports.insurance')"
                    class="bg-white rounded-lg shadow-md p-8 hover:shadow-lg transition border-l-4 border-orange-600"
                >
                    <div class="flex items-start gap-4">
                        <div class="text-4xl">🏥</div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Reporte por Obra Social
                            </h3>
                            <p class="text-gray-600 text-sm mt-1">
                                Facturación y atenciones por compañía de seguros
                            </p>
                        </div>
                    </div>
                </Link>

                <!-- Billing Analysis -->
                <Link
                    :href="route('advanced-reports.billing')"
                    class="bg-white rounded-lg shadow-md p-8 hover:shadow-lg transition border-l-4 border-green-500"
                >
                    <div class="flex items-start gap-4">
                        <div class="text-4xl">💰</div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Análisis de Facturación
                            </h3>
                            <p class="text-gray-600 text-sm mt-1">
                                Ingresos, cobranzas, análisis de envejecimiento
                            </p>
                        </div>
                    </div>
                </Link>

                <!-- Bed Occupancy -->
                <Link
                    :href="route('advanced-reports.bed-occupancy')"
                    class="bg-white rounded-lg shadow-md p-8 hover:shadow-lg transition border-l-4 border-red-600"
                >
                    <div class="flex items-start gap-4">
                        <div class="text-4xl">🛏️</div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Ocupancia de Camas
                            </h3>
                            <p class="text-gray-600 text-sm mt-1">
                                Utilización de camas, promedio de estadía,
                                rotación
                            </p>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Monthly Trend -->
            <div
                v-if="monthly_stats.length > 0"
                class="bg-white rounded-lg shadow-md p-6"
            >
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    Tendencia Mensual (Últimos 12 meses)
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-gray-900 font-semibold"
                                >
                                    Mes
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-gray-900 font-semibold"
                                >
                                    Pacientes
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-gray-900 font-semibold"
                                >
                                    Citas
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-gray-900 font-semibold"
                                >
                                    Ingresos
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="month in monthly_stats"
                                :key="month.month"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ month.month }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ formatNumber(month.patients) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ formatNumber(month.appointments) }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-medium text-blue-600"
                                >
                                    {{ formatCurrency(month.revenue) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
