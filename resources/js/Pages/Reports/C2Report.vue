<script setup lang="ts">
import { ref, computed } from "vue";
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface Consultation {
    specialty: string;
    doctor: string;
    count: number;
    referrals: number;
}

interface Props {
    consultations: Consultation[];
    total_consultations: number;
    total_referrals: number;
    referral_rate: number;
}

const props = defineProps<Props>();
</script>

<template>
    <Head title="Reporte C2" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Reporte C2 - REFES
                </h1>
                <p class="text-gray-600 mt-2">
                    Consultaciones, derivaciones y análisis por especialidad
                </p>
            </div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Total de Consultaciones</p>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ props.total_consultations }}
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Total de Derivaciones</p>
                    <p class="text-3xl font-bold text-orange-600">
                        {{ props.total_referrals }}
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Tasa de Derivación</p>
                    <p class="text-3xl font-bold text-purple-600">
                        {{ (props.referral_rate * 100).toFixed(1) }}%
                    </p>
                </div>
            </div>

            <!-- Consultations by Specialty -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Consultaciones por Especialidad
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Especialidad
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Médico
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                >
                                    Consultaciones
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                >
                                    Derivaciones
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                >
                                    % Derivación
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="(
                                    consultation, idx
                                ) in props.consultations"
                                :key="idx"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4">
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ consultation.specialty }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600">
                                        {{ consultation.doctor }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p
                                        class="text-sm font-semibold text-right text-gray-900"
                                    >
                                        {{ consultation.count }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p
                                        class="text-sm font-semibold text-right text-orange-600"
                                    >
                                        {{ consultation.referrals }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p
                                        class="text-sm font-semibold text-right text-purple-600"
                                    >
                                        {{
                                            consultation.count > 0
                                                ? (
                                                      (consultation.referrals /
                                                          consultation.count) *
                                                      100
                                                  ).toFixed(1)
                                                : 0
                                        }}%
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
