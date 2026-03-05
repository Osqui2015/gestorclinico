<script setup lang="ts">
import { Head, useForm, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { ref, computed } from "vue";

interface Doctor {
    id: number;
    name: string;
    specialty: string;
}

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
}

interface Appointment {
    id: number;
    doctor_id: number;
    patient_id: number;
    scheduled_at: string;
    status: string;
    reason: string | null;
    notes: string | null;
    doctor: Doctor;
    patient: Patient;
}

interface Props {
    appointment: Appointment;
    patients: Record<number, string>;
    doctors: Record<number, string>;
}

const props = defineProps<Props>();

const patientSearch = ref("");

const filteredPatients = computed(() => {
    if (!patientSearch.value) {
        return props.patients;
    }

    return Object.fromEntries(
        Object.entries(props.patients).filter(([_, patientName]) =>
            patientName
                .toLowerCase()
                .includes(patientSearch.value.toLowerCase()),
        ),
    );
});

const scheduledAtFormatted = new Date(props.appointment.scheduled_at)
    .toISOString()
    .slice(0, 16);

const form = useForm({
    doctor_id: String(props.appointment.doctor_id),
    patient_id: String(props.appointment.patient_id),
    scheduled_at: scheduledAtFormatted,
    reason: props.appointment.reason || "",
    notes: props.appointment.notes || "",
});

const submit = () => {
    form.patch(route("secretary.turns.update", props.appointment.id), {
        onFinish: () => {},
    });
};
</script>

<template>
    <Head title="Editar Turno" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 "
            >
                ✏️ Editar Turno
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
                                ✅ Actualizar Turno
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
