<script setup lang="ts">
import { ref } from "vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
}

interface Doctor {
    id: number;
    name: string;
}

interface EmergencyAdmission {
    id: number;
    patient_id: number;
    attending_doctor_id: number;
    triage_level: number;
    chief_complaint: string;
    blood_pressure: string;
    heart_rate: number;
    respiratory_rate: number;
    temperature: number;
    oxygen_saturation: number;
    glucose_level: number;
    consciousness_level: string;
    diagnosis: string | null;
    treatment: string | null;
    observations: string | null;
}

interface Props {
    admission: EmergencyAdmission;
    patients: Patient[];
    doctors: Doctor[];
}

const props = defineProps<Props>();

const form = useForm({
    triage_level: props.admission.triage_level,
    chief_complaint: props.admission.chief_complaint,
    blood_pressure: props.admission.blood_pressure,
    heart_rate: props.admission.heart_rate,
    respiratory_rate: props.admission.respiratory_rate,
    temperature: props.admission.temperature,
    oxygen_saturation: props.admission.oxygen_saturation,
    glucose_level: props.admission.glucose_level,
    consciousness_level: props.admission.consciousness_level,
    diagnosis: props.admission.diagnosis || "",
    treatment: props.admission.treatment || "",
    observations: props.admission.observations || "",
});

const triageLevels = [
    {
        value: 1,
        label: "Nivel 1 - Resucitación (Inmediato)",
        color: "text-red-600",
    },
    {
        value: 2,
        label: "Nivel 2 - Emergencia (< 10 min)",
        color: "text-red-500",
    },
    {
        value: 3,
        label: "Nivel 3 - Urgencia (30 min)",
        color: "text-yellow-500",
    },
    { value: 4, label: "Nivel 4 - Menor (1-3 horas)", color: "text-blue-500" },
    {
        value: 5,
        label: "Nivel 5 - Sin urgencia (2-4 horas)",
        color: "text-green-500",
    },
];

const consciousnessLevels = [
    { value: "alert", label: "Alerta" },
    { value: "confused", label: "Confundido" },
    { value: "voices", label: "Responde a voz" },
    { value: "pain", label: "Responde a dolor" },
    { value: "unresponsive", label: "Sin respuesta" },
];

const submit = () => {
    form.put(route("emergency.update", props.admission.id), {
        onSuccess: () => {
            router.get(route("emergency.show", props.admission.id));
        },
    });
};
</script>

<template>
    <Head :title="`Editar Admisión #${admission.id}`" />
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="bg-white rounded-lg shadow-md p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-6">
                    Editar Admisión de Emergencia #{{ admission.id }}
                </h1>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Triage Level -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-3"
                            >Nivel de Triage</label
                        >
                        <div class="space-y-2">
                            <label
                                v-for="level in triageLevels"
                                :key="level.value"
                                class="flex items-center"
                            >
                                <input
                                    type="radio"
                                    :value="level.value"
                                    v-model="form.triage_level"
                                    class="w-4 h-4 text-red-600"
                                />
                                <span
                                    :class="[
                                        'ml-2 text-sm font-medium',
                                        level.color,
                                    ]"
                                    >{{ level.label }}</span
                                >
                            </label>
                        </div>
                    </div>

                    <!-- Chief Complaint -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Motivo de Consulta</label
                        >
                        <input
                            v-model="form.chief_complaint"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                        />
                    </div>

                    <!-- Vital Signs -->
                    <div class="bg-gray-50 rounded-lg p-4">
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
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
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
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
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
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
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
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
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
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
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
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Consciousness Level -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Nivel de Conciencia</label
                        >
                        <select
                            v-model="form.consciousness_level"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                        >
                            <option
                                v-for="level in consciousnessLevels"
                                :key="level.value"
                                :value="level.value"
                            >
                                {{ level.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Diagnosis and Treatment -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                                >Diagnóstico</label
                            >
                            <textarea
                                v-model="form.diagnosis"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                rows="3"
                            ></textarea>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                                >Tratamiento</label
                            >
                            <textarea
                                v-model="form.treatment"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                rows="3"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Observations -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Observaciones</label
                        >
                        <textarea
                            v-model="form.observations"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                            rows="3"
                        ></textarea>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex gap-4 pt-6 border-t border-gray-200">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400"
                        >
                            {{
                                form.processing
                                    ? "Procesando..."
                                    : "Guardar Cambios"
                            }}
                        </button>
                        <button
                            type="button"
                            @click="
                                router.get(
                                    route('emergency.show', admission.id),
                                )
                            "
                            class="px-6 py-2 bg-gray-300 text-gray-900 rounded-lg hover:bg-gray-400"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
