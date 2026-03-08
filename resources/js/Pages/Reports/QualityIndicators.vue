<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface Props {
    metrics: {
        average_wait_time: number;
        patient_satisfaction: number;
        employee_productivity: number;
        appointment_utilization: number;
        revenue_per_doctor: number;
        emergency_response_time: number;
        hospitalization_metrics: {
            average_length_stay: number;
            occupancy_rate: number;
        };
    };
}

const props = defineProps<Props>();

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
    }).format(value);
};
</script>

<template>
    <Head title="Indicadores de Calidad" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Indicadores de Calidad
                </h1>
                <p class="text-gray-600 mt-2">
                    KPIs de desempeño operacional y calidad asistencial
                </p>
            </div>

            <!-- Metric Cards -->
            <div class="grid grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm mb-2">
                        Tiempo Promedio de Espera
                    </p>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ Math.round(props.metrics.average_wait_time) }} min
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm mb-2">
                        Satisfacción del Paciente
                    </p>
                    <p class="text-3xl font-bold text-green-600">
                        {{
                            (props.metrics.patient_satisfaction * 100).toFixed(
                                1,
                            )
                        }}%
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm mb-2">
                        Productividad del Equipo
                    </p>
                    <p class="text-3xl font-bold text-purple-600">
                        {{
                            (props.metrics.employee_productivity * 100).toFixed(
                                1,
                            )
                        }}%
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm mb-2">
                        Utilización de Citas
                    </p>
                    <p class="text-3xl font-bold text-orange-600">
                        {{
                            (
                                props.metrics.appointment_utilization * 100
                            ).toFixed(1)
                        }}%
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm mb-2">
                        Ingresos por Médico
                    </p>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ formatCurrency(props.metrics.revenue_per_doctor) }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm mb-2">
                        Tiempo Respuesta Emergencia
                    </p>
                    <p class="text-3xl font-bold text-red-600">
                        {{
                            Math.round(props.metrics.emergency_response_time)
                        }}
                        min
                    </p>
                </div>
            </div>

            <!-- Hospitalization Metrics -->
            <div class="grid grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">
                        Métricas de Hospitalización
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">
                                Promedio de Estadía
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{
                                    Math.round(
                                        props.metrics.hospitalization_metrics
                                            .average_length_stay,
                                    )
                                }}
                                días
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">
                                Tasa de Ocupancia
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{
                                    (
                                        props.metrics.hospitalization_metrics
                                            .occupancy_rate * 100
                                    ).toFixed(1)
                                }}%
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-br from-green-50 to-blue-50 rounded-lg p-6"
                >
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Interpretación de Indicadores
                    </h3>
                    <ul class="space-y-3 text-sm text-gray-700">
                        <li class="flex gap-2">
                            <span>✓</span>
                            <span
                                ><strong>Tiempo de espera:</strong> Inferior a
                                20 min es excelente</span
                            >
                        </li>
                        <li class="flex gap-2">
                            <span>✓</span>
                            <span
                                ><strong>Satisfacción:</strong> Objetivo es
                                mantener > 85%</span
                            >
                        </li>
                        <li class="flex gap-2">
                            <span>✓</span>
                            <span
                                ><strong>Productividad:</strong> Mínimo 70% es
                                aceptable</span
                            >
                        </li>
                        <li class="flex gap-2">
                            <span>✓</span>
                            <span
                                ><strong>Ocupancia:</strong> Rango ideal
                                75-85%</span
                            >
                        </li>
                        <li class="flex gap-2">
                            <span>✓</span>
                            <span
                                ><strong>Emergencia:</strong> Meta: respuesta en
                                < 10 minutos</span
                            >
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
