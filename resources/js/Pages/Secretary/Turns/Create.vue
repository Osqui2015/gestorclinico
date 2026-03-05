<script setup lang="ts">
import { ref, computed } from "vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

interface Props {
    patients: Record<number, string>;
    doctors: Record<number, string>;
}

const props = defineProps<Props>();

const form = useForm({
    doctor_id: "",
    patient_id: "",
    scheduled_at: "",
    reason: "",
    notes: "",
});

const patientSearch = ref("");

// Allow searching by DNI (digits) or name
const filteredPatients = computed(() => {
    if (!patientSearch.value) return props.patients;
    const q = patientSearch.value.toLowerCase().trim();
    const isDigits = /^\d+$/.test(q);

    return Object.fromEntries(
        Object.entries(props.patients).filter(([_, patientLabel]) => {
            const label = patientLabel.toLowerCase();
            if (isDigits) return label.includes(q);
            return label.includes(q);
        }),
    );
});

const submit = () => {
    form.post(route("secretary.turns.store"), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Crear Turno" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 "
            >
                ➕ Crear Nuevo Turno
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white p-6 shadow-sm ">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Doctor Selection -->
                        <div>
                            <InputLabel for="doctor_id" value="Médico *" />
                            <select
                                id="doctor_id"
                                v-model="form.doctor_id"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500   "
                                :class="{
                                    'border-danger-500': form.errors.doctor_id,
                                }"
                            >
                                <option value="">Selecciona un médico</option>
                                <option
                                    v-for="(name, id) in doctors"
                                    :key="id"
                                    :value="id"
                                >
                                    {{ name }}
                                </option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="form.errors.doctor_id"
                            />
                        </div>

                        <!-- Patient Search & Selection -->
                        <div>
                            <InputLabel
                                for="patient_search"
                                value="Buscar Paciente por DNI o Nombre"
                            />
                            <TextInput
                                id="patient_search"
                                v-model="patientSearch"
                                placeholder="Ej: 12345678 o Buscar por nombre"
                                class="mt-1 block w-full"
                            />
                            <p
                                class="mt-1 text-xs text-gray-500 "
                            >
                                💡 Escribe el DNI o nombre del paciente para
                                filtrar
                            </p>
                            <Link
                                :href="route('secretary.patients.create')"
                                class="mt-2 inline-flex items-center text-sm text-primary-600 hover:text-primary-900  "
                            >
                                ➕ Crear nuevo paciente
                            </Link>
                        </div>

                        <!-- Patient Selection -->
                        <div>
                            <InputLabel for="patient_id" value="Paciente *" />
                            <select
                                id="patient_id"
                                v-model="form.patient_id"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500   "
                                :class="{
                                    'border-danger-500': form.errors.patient_id,
                                }"
                            >
                                <option value="">Selecciona un paciente</option>
                                <option
                                    v-for="(name, id) in filteredPatients"
                                    :key="id"
                                    :value="id"
                                >
                                    {{ name }}
                                </option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="form.errors.patient_id"
                            />
                            <p
                                v-if="
                                    Object.keys(filteredPatients).length === 0
                                "
                                class="mt-2 text-sm text-warning-600 "
                            >
                                ⚠️ No se encontraron pacientes con esa búsqueda
                            </p>
                        </div>

                        <!-- Date and Time -->
                        <div>
                            <InputLabel
                                for="scheduled_at"
                                value="Fecha y Hora *"
                            />
                            <input
                                id="scheduled_at"
                                v-model="form.scheduled_at"
                                type="datetime-local"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500   "
                                :class="{
                                    'border-danger-500': form.errors.scheduled_at,
                                }"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.scheduled_at"
                            />
                            <p
                                class="mt-1 text-xs text-gray-500 "
                            >
                                ⏰ Selecciona una fecha y hora. El médico no
                                puede tener 2 turnos a la misma hora.
                            </p>
                        </div>

                        <!-- Reason -->
                        <div>
                            <InputLabel
                                for="reason"
                                value="Motivo de la consulta (Opcional)"
                            />
                            <TextInput
                                id="reason"
                                v-model="form.reason"
                                placeholder="Ej: Revisión general, dolor de cabeza, etc."
                                class="mt-1 block w-full"
                                :class="{
                                    'border-danger-500': form.errors.reason,
                                }"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.reason"
                            />
                        </div>

                        <!-- Notes -->
                        <div>
                            <InputLabel for="notes" value="Notas (Opcional)" />
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="4"
                                placeholder="Notas adicionales sobre el turno..."
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500   "
                                :class="{ 'border-danger-500': form.errors.notes }"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.notes"
                            />
                        </div>

                        <!-- Submit buttons -->
                        <div class="flex gap-3 pt-4">
                            <PrimaryButton :disabled="form.processing">
                                ✅ Crear Turno
                            </PrimaryButton>
                            <Link
                                :href="route('secretary.turns.index')"
                                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-6 py-2 font-semibold text-gray-700 hover:bg-gray-50    "
                            >
                                Cancelar
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
