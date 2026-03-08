<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface AgeGroup {
    range: string;
    count: number;
    percentage: number;
}

interface Props {
    notifiable_diseases: Array<{ disease: string; count: number }>;
    top_diagnoses: Array<{ diagnosis: string; count: number }>;
    age_distribution: AgeGroup[];
    gender_distribution: Record<string, number>;
}

const props = defineProps<Props>();

const getGenderLabel = (gender: string) => {
    const labels: Record<string, string> = {
        M: "Masculino",
        F: "Femenino",
        O: "Otro",
    };
    return labels[gender] || gender;
};
</script>

<template>
    <Head title="Reporte Epidemiológico" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Reporte Epidemiológico
                </h1>
                <p class="text-gray-600 mt-2">
                    Análisis de enfermedades y características demográficas
                </p>
            </div>

            <!-- Gender Distribution -->
            <div class="grid grid-cols-3 gap-6 mb-8">
                <div
                    v-for="(count, gender) in props.gender_distribution"
                    :key="gender"
                    class="bg-white rounded-lg shadow-md p-6"
                >
                    <p class="text-gray-600 text-sm">
                        {{ getGenderLabel(gender) }}
                    </p>
                    <p class="text-3xl font-bold text-blue-600">{{ count }}</p>
                </div>
            </div>

            <!-- Age Distribution -->
            <div class="bg-white rounded-lg shadow-md mb-8">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Distribución por Edad
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Rango de Edad
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                >
                                    Cantidad
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                >
                                    Porcentaje
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="(group, idx) in props.age_distribution"
                                :key="idx"
                                class="hover:bg-gray-50"
                            >
                                <td
                                    class="px-6 py-4 text-sm font-medium text-gray-900"
                                >
                                    {{ group.range }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-right font-semibold"
                                >
                                    {{ group.count }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-right text-blue-600 font-semibold"
                                >
                                    {{ group.percentage.toFixed(1) }}%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Diagnoses -->
            <div class="grid grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-md">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Diagnósticos Principales
                        </h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100 border-b border-gray-200">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                    >
                                        Diagnóstico
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                    >
                                        Casos
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr
                                    v-for="(item, idx) in props.top_diagnoses"
                                    :key="idx"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-6 py-3 text-sm text-gray-900">
                                        {{ item.diagnosis }}
                                    </td>
                                    <td
                                        class="px-6 py-3 text-sm text-right font-semibold text-gray-900"
                                    >
                                        {{ item.count }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Notifiable Diseases -->
                <div class="bg-white rounded-lg shadow-md">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Enfermedades Notificables
                        </h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100 border-b border-gray-200">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                    >
                                        Enfermedad
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                    >
                                        Casos
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr
                                    v-for="(
                                        item, idx
                                    ) in props.notifiable_diseases"
                                    :key="idx"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-6 py-3 text-sm text-gray-900">
                                        {{ item.disease }}
                                    </td>
                                    <td
                                        class="px-6 py-3 text-sm text-right font-semibold text-red-600"
                                    >
                                        {{ item.count }}
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
