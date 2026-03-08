<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface RoomOccupancy {
    room_name: string;
    total_beds: number;
    occupied_beds: number;
    occupancy_rate: number;
}

interface Props {
    total_beds: number;
    occupied_beds: number;
    available_beds: number;
    occupancy_rate: number;
    average_length_stay: number;
    turnover_rate: number;
    beds_pending_cleaning: number;
    room_occupancy: RoomOccupancy[];
}

const props = defineProps<Props>();
</script>

<template>
    <Head title="Ocupancia de Camas" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Ocupancia de Camas
                </h1>
                <p class="text-gray-600 mt-2">
                    Utilización de infraestructura hospitalar
                </p>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Total de Camas</p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ props.total_beds }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Camas Ocupadas</p>
                    <p class="text-3xl font-bold text-red-600">
                        {{ props.occupied_beds }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Camas Disponibles</p>
                    <p class="text-3xl font-bold text-green-600">
                        {{ props.available_beds }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Tasa de Ocupancia</p>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ (props.occupancy_rate * 100).toFixed(1) }}%
                    </p>
                </div>
            </div>

            <!-- Operational Metrics -->
            <div class="grid grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Promedio de Estadía</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ Math.round(props.average_length_stay) }} días
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Tasa de Rotación</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ Math.round(props.turnover_rate) }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Pendientes de Limpieza</p>
                    <p class="text-2xl font-bold text-orange-600">
                        {{ props.beds_pending_cleaning }}
                    </p>
                </div>
            </div>

            <!-- Room Breakdown -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Ocupancia por Habitación
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                                >
                                    Habitación
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-sm font-semibold text-gray-900"
                                >
                                    Total de Camas
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-sm font-semibold text-gray-900"
                                >
                                    Camas Ocupadas
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-sm font-semibold text-gray-900"
                                >
                                    % Ocupancia
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-sm font-semibold text-gray-900"
                                >
                                    Indicador
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="(room, idx) in props.room_occupancy"
                                :key="idx"
                                class="hover:bg-gray-50"
                            >
                                <td
                                    class="px-6 py-4 text-sm font-medium text-gray-900"
                                >
                                    {{ room.room_name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    {{ room.total_beds }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-center font-semibold"
                                >
                                    {{ room.occupied_beds }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-right font-semibold text-blue-600"
                                >
                                    {{
                                        (room.occupancy_rate * 100).toFixed(1)
                                    }}%
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        v-if="room.occupancy_rate > 0.85"
                                        class="inline-block w-3 h-3 rounded-full bg-red-500"
                                        title="Alta ocupancia"
                                    ></span>
                                    <span
                                        v-else-if="room.occupancy_rate > 0.65"
                                        class="inline-block w-3 h-3 rounded-full bg-yellow-500"
                                        title="Ocupancia media"
                                    ></span>
                                    <span
                                        v-else
                                        class="inline-block w-3 h-3 rounded-full bg-green-500"
                                        title="Baja ocupancia"
                                    ></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
