<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface InsuranceData {
    insurance_name: string;
    total_billings: number;
    total_consultations: number;
    total_revenue: number;
    collection_rate: number;
}

interface Props {
    insurances: InsuranceData[];
    total_billings: number;
    total_consultations: number;
    total_revenue: number;
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
    <Head title="Reporte por Obra Social" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Reporte por Obra Social
                </h1>
                <p class="text-gray-600 mt-2">
                    Facturación y atenciones por compañía de seguros
                </p>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Total Facturaciones</p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ props.total_billings }}
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Total Consultaciones</p>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ props.total_consultations }}
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Ingresos Totales</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ formatCurrency(props.total_revenue) }}
                    </p>
                </div>
            </div>

            <!-- Insurance Breakdown -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Desglose por Obra Social
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Obra Social
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                >
                                    Facturaciones
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                >
                                    Consultaciones
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                >
                                    Ingresos
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                >
                                    % Cobranza
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="(insurance, idx) in props.insurances"
                                :key="idx"
                                class="hover:bg-gray-50"
                            >
                                <td
                                    class="px-6 py-4 text-sm font-medium text-gray-900"
                                >
                                    {{ insurance.insurance_name }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-right font-semibold"
                                >
                                    {{ insurance.total_billings }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-right font-semibold"
                                >
                                    {{ insurance.total_consultations }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-right font-semibold text-blue-600"
                                >
                                    {{
                                        formatCurrency(insurance.total_revenue)
                                    }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-right font-semibold text-green-600"
                                >
                                    {{
                                        (
                                            insurance.collection_rate * 100
                                        ).toFixed(1)
                                    }}%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
