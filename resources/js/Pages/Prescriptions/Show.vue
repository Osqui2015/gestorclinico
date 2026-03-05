<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface Props {
    prescription: {
        id: number;
        diagnosis: string | null;
        medications: any[];
        instructions: any[];
        notes: string | null;
        created_at: string;
    };
    patient: {
        id: number;
        first_name: string;
        last_name: string;
        dni: string;
    };
    doctor: {
        id: number;
        name: string;
    };
}

const props = defineProps<Props>();

const downloadPrescriptionPDF = () => {
    window.location.href = route(
        "prescriptions.generate-prescription-pdf",
        props.prescription.id,
    );
};

const downloadInstructionsPDF = () => {
    window.location.href = route(
        "prescriptions.generate-instructions-pdf",
        props.prescription.id,
    );
};
</script>

<template>
    <Head title="Ver Receta" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-4 bg-primary-50 border-b border-primary-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">
                                Receta
                            </h1>
                            <p class="mt-1 text-sm text-gray-600">
                                Paciente:
                                <strong
                                    >{{ patient.first_name }}
                                    {{ patient.last_name }}</strong
                                >
                            </p>
                            <p class="text-sm text-gray-600">
                                Doctor: <strong>{{ doctor.name }}</strong>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">
                                Fecha:
                                {{
                                    new Date(
                                        prescription.created_at,
                                    ).toLocaleDateString("es-ES")
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-8">
                    <!-- Diagnóstico -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            🏥 Diagnóstico
                        </h2>
                        <div
                            class="p-4 border border-warning-300 rounded-lg bg-warning-50"
                        >
                            <p class="text-gray-800 whitespace-pre-wrap">
                                {{ prescription.diagnosis }}
                            </p>
                        </div>
                    </div>

                    <!-- Medicamentos -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            💊 Medicamentos
                        </h2>
                        <div class="space-y-3">
                            <div
                                v-for="(med, index) in prescription.medications"
                                :key="index"
                                class="p-4 border border-gray-300 rounded-lg"
                            >
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-gray-900">
                                            {{ med.name }}
                                        </h3>
                                        <p class="text-sm text-gray-600 mt-1">
                                            <strong>Dosis:</strong>
                                            {{ med.dosage }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <strong>Frecuencia:</strong>
                                            {{ med.frequency }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <strong>Duración:</strong>
                                            {{ med.duration }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Indicaciones -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            📋 Indicaciones / Recomendaciones
                        </h2>
                        <div class="space-y-3">
                            <div
                                v-for="(
                                    inst, index
                                ) in prescription.instructions"
                                :key="'inst-' + index"
                                class="p-4 border border-success-300 rounded-lg bg-success-50"
                            >
                                <p class="text-gray-800 whitespace-pre-wrap">
                                    {{ inst.description }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div v-if="prescription.notes">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            📝 Notas Adicionales
                        </h2>
                        <div
                            class="p-4 border border-warning-300 rounded-lg bg-warning-50"
                        >
                            <p class="text-gray-800 whitespace-pre-wrap">
                                {{ prescription.notes }}
                            </p>
                        </div>
                    </div>

                    <!-- Download Buttons -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Descargar Documentos
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <button
                                @click="downloadPrescriptionPDF"
                                class="px-4 py-2 bg-danger-600 text-white rounded-md hover:bg-danger-700 transition flex items-center justify-center gap-2"
                            >
                                📄 Descargar Receta (PDF)
                            </button>
                            <button
                                @click="downloadInstructionsPDF"
                                class="px-4 py-2 bg-success-600 text-white rounded-md hover:bg-success-700 transition flex items-center justify-center gap-2"
                            >
                                📋 Descargar Indicaciones (PDF)
                            </button>
                        </div>
                    </div>

                    <!-- Back Button -->
                    <div class="border-t border-gray-200 pt-6">
                        <a
                            href="#"
                            @click.prevent="
                                $inertia.visit(route('prescriptions.index'))
                            "
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition"
                        >
                            ← Volver a mis Recetas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
