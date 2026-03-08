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

interface Props {
    patients: Patient[];
    doctors: Doctor[];
}

const props = defineProps<Props>();

const form = useForm({
    patient_id: "",
    attending_doctor_id: "",
    triage_level: 3,
    chief_complaint: "",
    blood_pressure: "",
    heart_rate: "",
    respiratory_rate: "",
    temperature: "",
    oxygen_saturation: "",
    glucose_level: "",
    consciousness_level: "alert",
    diagnosis: "",
    treatment: "",
    observations: "",
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
    form.post(route("emergency.store"), {
        onSuccess: () => {
            router.get(route("emergency.board"));
        },
    });
};
</script>

<template>
    <Head title="Nueva Admisión de Emergencia" />
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="bg-white rounded-lg shadow-md p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-6">
                    Nueva Admisión de Emergencia
                </h1>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Patient Selection -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                                >Paciente *</label
                            >
                            <select
                                v-model="form.patient_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                :class="{
                                    'border-red-500': form.errors.patient_id,
                                }"
                            >
                                <option value="">
                                    Seleccionar paciente...
                                </option>
                                <option
                                    v-for="patient in patients"
                                    :key="patient.id"
                                    :value="patient.id"
                                >
                                    {{ patient.first_name }}
                                    {{ patient.last_name }} ({{ patient.dni }})
                                </option>
                            </select>
                            <p
                                v-if="form.errors.patient_id"
                                class="text-red-600 text-sm mt-1"
                            >
                                {{ form.errors.patient_id }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                                >Médico Asignado *</label
                            >
                            <select
                                v-model="form.attending_doctor_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                :class="{
                                    'border-red-500':
                                        form.errors.attending_doctor_id,
                                }"
                            >
                                <option value="">Seleccionar médico...</option>
                                <option
                                    v-for="doctor in doctors"
                                    :key="doctor.id"
                                    :value="doctor.id"
                                >
                                    {{ doctor.name }}
                                </option>
                            </select>
                            <p
                                v-if="form.errors.attending_doctor_id"
                                class="text-red-600 text-sm mt-1"
                            >
                                {{ form.errors.attending_doctor_id }}
                            </p>
                        </div>
                    </div>

                    <!-- Triage Level -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-3"
                            >Nivel de Triage *</label
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
                            >Motivo de Consulta *</label
                        >
                        <input
                            v-model="form.chief_complaint"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            :class="{
                                'border-red-500': form.errors.chief_complaint,
                            }"
                            placeholder="Ej: Dolor abdominal severo"
                        />
                        <p
                            v-if="form.errors.chief_complaint"
                            class="text-red-600 text-sm mt-1"
                        >
                            {{ form.errors.chief_complaint }}
                        </p>
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
                                    >Presión Arterial *</label
                                >
                                <input
                                    v-model="form.blood_pressure"
                                    type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="Ej: 120/80"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Frecuencia Cardíaca *</label
                                >
                                <input
                                    v-model="form.heart_rate"
                                    type="number"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="bpm"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Frecuencia Respiratoria *</label
                                >
                                <input
                                    v-model="form.respiratory_rate"
                                    type="number"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="resp/min"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Temperatura *</label
                                >
                                <input
                                    v-model="form.temperature"
                                    type="number"
                                    step="0.1"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="°C"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Saturación O₂ *</label
                                >
                                <input
                                    v-model="form.oxygen_saturation"
                                    type="number"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="%"
                                    min="0"
                                    max="100"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Glucosa *</label
                                >
                                <input
                                    v-model="form.glucose_level"
                                    type="number"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="mg/dL"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Consciousness Level -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Nivel de Conciencia *</label
                        >
                        <select
                            v-model="form.consciousness_level"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
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
                                >Diagnóstico Inicial</label
                            >
                            <textarea
                                v-model="form.diagnosis"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                rows="3"
                                placeholder="Impresión diagnóstica preliminar"
                            ></textarea>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                                >Tratamiento Inicial</label
                            >
                            <textarea
                                v-model="form.treatment"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                rows="3"
                                placeholder="Intervenciones realizadas"
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            rows="3"
                            placeholder="Notas adicionales"
                        ></textarea>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex gap-4 pt-6 border-t border-gray-200">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:bg-gray-400 transition"
                        >
                            {{
                                form.processing
                                    ? "Procesando..."
                                    : "Crear Admisión"
                            }}
                        </button>
                        <button
                            type="button"
                            @click="router.get(route('emergency.board'))"
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
