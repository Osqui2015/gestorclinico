<script setup lang="ts">
import { ref } from "vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface EmergencyAdmission {
    id: number;
    patient: { first_name: string; last_name: string };
}

interface Props {
    admission: EmergencyAdmission;
}

const props = defineProps<Props>();

const form = useForm({
    blood_pressure: "",
    heart_rate: "",
    respiratory_rate: "",
    temperature: "",
    oxygen_saturation: "",
    glucose_level: "",
    clinical_notes: "",
    treatment_notes: "",
    medications_given: "",
    tests_performed: "",
});

const submit = () => {
    form.post(route("emergency.record-evolution", props.admission.id), {
        onSuccess: () => {
            router.get(route("emergency.show", props.admission.id));
        },
    });
};
</script>

<template>
    <Head :title="`Registrar Evolución - Admisión #${admission.id}`" />
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="bg-white rounded-lg shadow-md p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    Registrar Evolución
                </h1>
                <p class="text-gray-600 mb-6">
                    {{ admission.patient.first_name }}
                    {{ admission.patient.last_name }} - Admisión #{{
                        admission.id
                    }}
                </p>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Vital Signs -->
                    <div
                        class="bg-blue-50 rounded-lg p-6 border border-blue-200"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Signos Vitales
                        </h3>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Presión Arterial</label
                                >
                                <input
                                    v-model="form.blood_pressure"
                                    type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    placeholder="Ej: 120/80"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Frecuencia Cardíaca</label
                                >
                                <input
                                    v-model="form.heart_rate"
                                    type="number"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    placeholder="bpm"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Frecuencia Respiratoria</label
                                >
                                <input
                                    v-model="form.respiratory_rate"
                                    type="number"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    placeholder="resp/min"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Temperatura</label
                                >
                                <input
                                    v-model="form.temperature"
                                    type="number"
                                    step="0.1"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    placeholder="°C"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Saturación O₂</label
                                >
                                <input
                                    v-model="form.oxygen_saturation"
                                    type="number"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    placeholder="%"
                                    min="0"
                                    max="100"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Glucosa</label
                                >
                                <input
                                    v-model="form.glucose_level"
                                    type="number"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    placeholder="mg/dL"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Clinical Notes -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Notas Clínicas *</label
                        >
                        <textarea
                            v-model="form.clinical_notes"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            rows="4"
                            placeholder="Descripción del estado actual, hallazgos clínicos, cambios observados"
                        ></textarea>
                    </div>

                    <!-- Treatment -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                                >Notas de Tratamiento</label
                            >
                            <textarea
                                v-model="form.treatment_notes"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                rows="3"
                                placeholder="Intervenciones realizadas"
                            ></textarea>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                                >Medicamentos Administrados</label
                            >
                            <textarea
                                v-model="form.medications_given"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                rows="3"
                                placeholder="Medicinas dadas, dosis y vía"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Tests -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Estudios/Análisis Realizados</label
                        >
                        <textarea
                            v-model="form.tests_performed"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            rows="3"
                            placeholder="Laboratorio, imágenes, u otros estudios"
                        ></textarea>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex gap-4 pt-6 border-t border-gray-200">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition"
                        >
                            {{
                                form.processing
                                    ? "Registrando..."
                                    : "✓ Registrar Evolución"
                            }}
                        </button>
                        <button
                            type="button"
                            @click="
                                router.get(
                                    route('emergency.show', admission.id),
                                )
                            "
                            class="px-6 py-2 bg-gray-300 text-gray-900 rounded-lg hover:bg-gray-400 transition"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
