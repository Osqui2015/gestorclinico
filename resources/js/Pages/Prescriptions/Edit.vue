<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface Props {
    prescription: Prescription;
}

interface Medication {
    name: string;
    dosage: string;
    frequency: string;
    duration: string;
}

interface Instruction {
    description: string;
}

interface PatientSummary {
    first_name: string;
    last_name: string;
}

interface Prescription {
    id: number;
    diagnosis: string;
    medications: Medication[];
    instructions: Instruction[];
    notes: string | null;
    patient: PatientSummary;
}

const props = defineProps<Props>();
const prescription = props.prescription;

const form = useForm({
    diagnosis: prescription.diagnosis || "",
    medications:
        prescription.medications?.length > 0
            ? [...prescription.medications]
            : [
                  {
                      name: "",
                      dosage: "",
                      frequency: "",
                      duration: "",
                  },
              ],
    instructions:
        prescription.instructions?.length > 0
            ? [...prescription.instructions]
            : [
                  {
                      description: "",
                  },
              ],
    notes: prescription.notes || "",
});

const addMedication = () => {
    form.medications.push({
        name: "",
        dosage: "",
        frequency: "",
        duration: "",
    });
};

const removeMedication = (index: number) => {
    form.medications.splice(index, 1);
};

const addInstruction = () => {
    form.instructions.push({
        description: "",
    });
};

const removeInstruction = (index: number) => {
    form.instructions.splice(index, 1);
};

const submit = () => {
    if (form.medications.length === 0 || form.instructions.length === 0) {
        alert("Por favor, agrega al menos un medicamento y una indicación.");
        return;
    }

    form.put(route("prescriptions.update", props.prescription.id), {
        onFinish: () => {
            // Redirect will be handled by the controller
        },
    });
};
</script>

<template>
    <Head title="Editar Receta" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-primary-50 border-b border-primary-200">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Editar Receta
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Paciente:
                        <strong
                            >{{ prescription.patient.first_name }}
                            {{ prescription.patient.last_name }}</strong
                        >
                    </p>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-8">
                    <!-- Diagnóstico Section -->
                    <div>
                        <label
                            class="block text-lg font-semibold text-gray-900 mb-3"
                        >
                            🏥 Diagnóstico
                        </label>
                        <textarea
                            v-model="form.diagnosis"
                            placeholder="Escribe el diagnóstico médico..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            rows="3"
                        ></textarea>
                        <span
                            v-if="form.errors.diagnosis"
                            class="text-danger-600 text-sm mt-1"
                        >
                            {{ form.errors.diagnosis }}
                        </span>
                    </div>

                    <!-- Medicamentos Section -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-semibold text-gray-900">
                                💊 Medicamentos
                            </h2>
                            <button
                                type="button"
                                @click="addMedication"
                                class="px-3 py-1 bg-primary-600 text-white text-sm rounded-md hover:bg-primary-700 transition"
                            >
                                + Agregar Medicamento
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="(med, index) in form.medications"
                                :key="index"
                                class="p-4 border border-gray-300 rounded-lg space-y-3"
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm font-medium text-gray-700"
                                        >Medicamento #{{ index + 1 }}</span
                                    >
                                    <button
                                        v-if="form.medications.length > 1"
                                        type="button"
                                        @click="removeMedication(index)"
                                        class="text-danger-600 hover:text-danger-900 text-sm"
                                    >
                                        Eliminar
                                    </button>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1"
                                        >
                                            Nombre del Medicamento
                                        </label>
                                        <input
                                            v-model="med.name"
                                            type="text"
                                            placeholder="ej: Amoxicilina"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1"
                                        >
                                            Dosis
                                        </label>
                                        <input
                                            v-model="med.dosage"
                                            type="text"
                                            placeholder="ej: 500mg"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1"
                                        >
                                            Frecuencia
                                        </label>
                                        <input
                                            v-model="med.frequency"
                                            type="text"
                                            placeholder="ej: Cada 8 horas"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1"
                                        >
                                            Duración
                                        </label>
                                        <input
                                            v-model="med.duration"
                                            type="text"
                                            placeholder="ej: 7 días"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Indicaciones Section -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-semibold text-gray-900">
                                📋 Indicaciones / Recomendaciones
                            </h2>
                            <button
                                type="button"
                                @click="addInstruction"
                                class="px-3 py-1 bg-success-600 text-white text-sm rounded-md hover:bg-success-700 transition"
                            >
                                + Agregar Indicación
                            </button>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="(inst, index) in form.instructions"
                                :key="'inst-' + index"
                                class="p-4 border border-gray-300 rounded-lg"
                            >
                                <div
                                    class="flex items-start justify-between mb-2"
                                >
                                    <span
                                        class="text-sm font-medium text-gray-700"
                                        >Indicación #{{ index + 1 }}</span
                                    >
                                    <button
                                        v-if="form.instructions.length > 1"
                                        type="button"
                                        @click="removeInstruction(index)"
                                        class="text-danger-600 hover:text-danger-900 text-sm"
                                    >
                                        Eliminar
                                    </button>
                                </div>

                                <textarea
                                    v-model="inst.description"
                                    placeholder="ej: Descanso absoluto, tomar los medicamentos con alimentos..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-success-500 focus:border-success-500"
                                    rows="3"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Notas adicionales -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Notas Adicionales (Opcional)
                        </label>
                        <textarea
                            v-model="form.notes"
                            placeholder="Notas adicionales para el paciente..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                            rows="4"
                        ></textarea>
                    </div>

                    <!-- Submit buttons -->
                    <div class="flex gap-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 disabled:bg-gray-400 transition"
                        >
                            {{
                                form.processing
                                    ? "Guardando..."
                                    : "Actualizar Receta"
                            }}
                        </button>
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
                            class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 text-center transition"
                        >
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
