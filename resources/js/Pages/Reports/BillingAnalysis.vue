<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface AgingAnalysis {
    range: string;
    amount: number;
    percentage: number;
}

interface Props {
    total_billed: number;
    total_collected: number;
    pending_collection: number;
    collection_rate: number;
    billing_trend: Array<{ month: string; amount: number }>;
    aging_analysis: AgingAnalysis[];
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
    <Head title="Análisis de Facturación" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Análisis de Facturación
                </h1>
                <p class="text-gray-600 mt-2">
                    Ingresos, cobranzas y análisis de envejecimiento de cartera
                </p>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Total Facturado</p>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ formatCurrency(props.total_billed) }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Total Cobrado</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ formatCurrency(props.total_collected) }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Pendiente de Cobranza</p>
                    <p class="text-2xl font-bold text-orange-600">
                        {{ formatCurrency(props.pending_collection) }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Tasa de Cobranza</p>
                    <p class="text-2xl font-bold text-purple-600">
                        {{ (props.collection_rate * 100).toFixed(1) }}%
                    </p>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="grid grid-cols-2 gap-6">
                <!-- Aging Analysis -->
                <div class="bg-white rounded-lg shadow-md">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Análisis de Envejecimiento
                        </h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100 border-b border-gray-200">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                    >
                                        Rango de Antigüedad
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                    >
                                        Monto
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                    >
                                        %
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr
                                    v-for="(item, idx) in props.aging_analysis"
                                    :key="idx"
                                    class="hover:bg-gray-50"
                                >
                                    <td
                                        class="px-6 py-3 text-sm font-medium text-gray-900"
                                    >
                                        {{ item.range }}
                                    </td>
                                    <td
                                        class="px-6 py-3 text-sm text-right font-semibold"
                                    >
                                        {{ formatCurrency(item.amount) }}
                                    </td>
                                    <td
                                        class="px-6 py-3 text-sm text-right font-semibold text-gray-600"
                                    >
                                        {{ item.percentage.toFixed(1) }}%
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Status Indicator -->
                <div
                    class="bg-gradient-to-br from-blue-50 to-green-50 rounded-lg p-6 shadow-md"
                >
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">
                        Resumen Ejecutivo
                    </h2>

                    <div class="space-y-4 text-sm">
                        <div
                            class="flex justify-between items-center pb-4 border-b border-gray-200"
                        >
                            <span class="text-gray-700"
                                >Facturado del Período:</span
                            >
                            <span class="font-semibold text-blue-600">{{
                                formatCurrency(props.total_billed)
                            }}</span>
                        </div>

                        <div
                            class="flex justify-between items-center pb-4 border-b border-gray-200"
                        >
                            <span class="text-gray-700">Efectivo Cobrado:</span>
                            <span class="font-semibold text-green-600">{{
                                formatCurrency(props.total_collected)
                            }}</span>
                        </div>

                        <div
                            class="flex justify-between items-center pb-4 border-b border-gray-200"
                        >
                            <span class="text-gray-700">En Cobranza:</span>
                            <span class="font-semibold text-orange-600">{{
                                formatCurrency(props.pending_collection)
                            }}</span>
                        </div>

                        <div
                            class="mt-4 pt-4 border-t-2 border-gray-300 flex justify-between items-center font-bold"
                        >
                            <span class="text-gray-900">Tasa de Cobranza:</span>
                            <span
                                :class="
                                    props.collection_rate >= 0.8
                                        ? 'text-green-600'
                                        : props.collection_rate >= 0.6
                                          ? 'text-yellow-600'
                                          : 'text-red-600'
                                "
                            >
                                {{ (props.collection_rate * 100).toFixed(1) }}%
                            </span>
                        </div>

                        <p
                            v-if="props.collection_rate < 0.6"
                            class="text-red-700 mt-4 text-xs font-semibold"
                        >
                            ⚠️ Alerta: Tasa de cobranza inferior al objetivo
                            (mínimo 60%)
                        </p>
                        <p
                            v-else-if="props.collection_rate < 0.8"
                            class="text-yellow-700 mt-4 text-xs font-semibold"
                        >
                            ℹ️ Olor: Tasa de cobranza por debajo del óptimo
                            (objetivo 80%+)
                        </p>
                        <p
                            v-else
                            class="text-green-700 mt-4 text-xs font-semibold"
                        >
                            ✓ Excelente: Tasa de cobranza en rango óptimo
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
