<script setup lang="ts">
import { ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
    birth_date: string;
}

interface Props {
    patient: Patient;
    isFirstConsultation: boolean;
}

const props = defineProps<Props>();

const form = useForm({
    reason: "",
    diagnosis: "",
    treatment: "",
    private_notes: "",
    is_first_consultation: props.isFirstConsultation,
    health_background: "",
    create_prescription: false,
});

const submit = () => {
    form.create_prescription = false;
    form.post(route("patients.medical-records.store", props.patient.id), {
        onFinish: () => form.reset(),
    });
};

const submitAndCreatePrescription = () => {
    form.create_prescription = true;
    form.post(route("patients.medical-records.store", props.patient.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head
        :title="`Nueva Evolución - ${patient.first_name} ${patient.last_name}`"
    />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Nueva evolución clínica: {{ patient.first_name }}
                {{ patient.last_name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Patient info (read-only) -->
                        <div
                            class="rounded-lg border border-gray-200 bg-gray-50 p-4"
                        >
                            <h3
                                class="mb-3 text-sm font-semibold text-gray-900"
                            >
                                Información del paciente
                            </h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-600"
                                        >Paciente:</span
                                    >
                                    <p class="text-gray-900">
                                        {{ patient.first_name }}
                                        {{ patient.last_name }}
                                    </p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600"
                                        >DNI:</span
                                    >
                                    <p class="font-mono text-gray-900">
                                        {{ patient.dni }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- First Consultation / Health Background -->
                        <div
                            class="rounded-lg border border-primary-200 bg-primary-50 p-4"
                        >
                            <div class="mb-3 flex items-center justify-between">
                                <h3
                                    class="text-sm font-semibold text-primary-900"
                                >
                                    Antecedentes de Salud
                                </h3>
                                <label
                                    class="inline-flex items-center gap-2 text-sm text-primary-900"
                                >
                                    <input
                                        v-model="form.is_first_consultation"
                                        type="checkbox"
                                        class="rounded border-primary-300 text-primary-600 focus:ring-primary-500"
                                    />
                                    Primera consulta con este médico
                                </label>
                            </div>

                            <p
                                v-if="isFirstConsultation"
                                class="mb-3 text-xs text-primary-800"
                            >
                                Este paciente no tiene consultas previas
                                contigo. Puedes cargar antecedentes relevantes.
                            </p>

                            <div v-if="form.is_first_consultation">
                                <InputLabel
                                    for="health_background"
                                    value="Formulario de antecedentes de salud"
                                />
                                <textarea
                                    id="health_background"
                                    v-model="form.health_background"
                                    rows="4"
                                    placeholder="Ej: enfermedades previas, cirugías, alergias relevantes, medicación habitual, antecedentes familiares, internaciones, hábitos, etc."
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    :class="{
                                        'border-danger-500':
                                            form.errors.health_background,
                                    }"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.health_background"
                                />
                            </div>
                        </div>

                        <!-- Reason -->
                        <div>
                            <InputLabel
                                for="reason"
                                value="Motivo de consulta"
                            />
                            <textarea
                                id="reason"
                                v-model="form.reason"
                                rows="3"
                                placeholder="Describe el motivo principal de la consulta..."
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                :class="{
                                    'border-danger-500': form.errors.reason,
                                }"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.reason"
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                Máximo 1000 caracteres
                            </p>
                        </div>

                        <!-- Diagnosis -->
                        <div>
                            <InputLabel for="diagnosis" value="Diagnóstico" />
                            <textarea
                                id="diagnosis"
                                v-model="form.diagnosis"
                                rows="4"
                                placeholder="Diagnóstico clínico basado en la evaluación del paciente..."
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                :class="{
                                    'border-danger-500': form.errors.diagnosis,
                                }"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.diagnosis"
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                Máximo 2000 caracteres
                            </p>
                        </div>

                        <!-- Treatment -->
                        <div>
                            <InputLabel for="treatment" value="Tratamiento" />
                            <textarea
                                id="treatment"
                                v-model="form.treatment"
                                rows="4"
                                placeholder="Plan de tratamiento ordenado por el médico..."
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                :class="{
                                    'border-danger-500': form.errors.treatment,
                                }"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.treatment"
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                Incluye medicamentos, dosis, duración. Máximo
                                2000 caracteres
                            </p>
                        </div>

                        <!-- Private Notes -->
                        <div>
                            <InputLabel
                                for="private_notes"
                                value="Notas Privadas (Solo para ti)"
                            />
                            <textarea
                                id="private_notes"
                                v-model="form.private_notes"
                                rows="3"
                                placeholder="Notas personales que solo tú puedes ver. Observaciones, recordatorios, etc."
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                :class="{
                                    'border-danger-500':
                                        form.errors.private_notes,
                                }"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.private_notes"
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                Máximo 2000 caracteres
                            </p>
                        </div>

                        <!-- Submit buttons -->
                        <div class="flex gap-3 border-t border-gray-200 pt-6">
                            <PrimaryButton :disabled="form.processing">
                                💾 Guardar Registro
                            </PrimaryButton>
                            <button
                                type="button"
                                @click="submitAndCreatePrescription"
                                :disabled="form.processing"
                                class="rounded-lg bg-success-600 px-6 py-2 font-semibold text-white hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-success-500 focus:ring-offset-2 disabled:opacity-50"
                            >
                                💾📝 Guardar y Crear Receta
                            </button>
                            <button
                                type="button"
                                @click="
                                    $inertia.visit(
                                        route('patients.show', patient.id),
                                    )
                                "
                                class="rounded-lg border border-gray-300 bg-white px-6 py-2 font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
