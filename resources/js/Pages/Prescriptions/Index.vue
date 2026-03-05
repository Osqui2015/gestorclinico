<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface Props {
    prescriptions: {
        data: any[];
        links: any;
    };
}

const props = defineProps<Props>();

const confirmDelete = (prescriptionId: number) => {
    if (confirm("¿Estás seguro de que deseas eliminar esta receta?")) {
        (window as any).$inertia.delete(
            route("prescriptions.destroy", prescriptionId),
        );
    }
};
</script>

<template>
    <Head title="Mis Recetas" />

    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <!-- Header -->
                <div
                    class="px-6 py-4 bg-primary-50 border-b border-primary-200 flex justify-between items-center"
                >
                    <h1 class="text-2xl font-bold text-gray-900">
                        Mis Recetas
                    </h1>
                    <a
                        href="#"
                        @click.prevent="$inertia.visit(route('dashboard'))"
                        class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition"
                    >
                        + Nueva Receta
                    </a>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b border-gray-300">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"
                                >
                                    Paciente
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"
                                >
                                    DNI
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"
                                >
                                    Medicamentos
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"
                                >
                                    Fecha
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="prescription in props.prescriptions.data"
                                :key="prescription.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ prescription.patient.first_name }}
                                        {{ prescription.patient.last_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600">
                                        {{ prescription.patient.dni }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600">
                                        {{
                                            prescription.medications
                                                .map((m: any) => m.name)
                                                .join(", ")
                                                .substring(0, 50)
                                        }}...
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600">
                                        {{
                                            new Date(
                                                prescription.created_at,
                                            ).toLocaleDateString("es-ES")
                                        }}
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                >
                                    <a
                                        href="#"
                                        @click.prevent="
                                            $inertia.visit(
                                                route(
                                                    'prescriptions.show',
                                                    prescription.id,
                                                ),
                                            )
                                        "
                                        class="text-primary-600 hover:text-primary-900 mr-4"
                                    >
                                        Ver
                                    </a>
                                    <a
                                        href="#"
                                        @click.prevent="
                                            confirmDelete(prescription.id)
                                        "
                                        class="text-danger-600 hover:text-danger-900"
                                    >
                                        Eliminar
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div
                        v-if="props.prescriptions.data.length === 0"
                        class="px-6 py-8 text-center text-gray-600"
                    >
                        No tienes recetas generadas aún.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
