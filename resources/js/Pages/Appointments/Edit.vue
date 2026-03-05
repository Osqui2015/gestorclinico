<script setup lang="ts">
import { ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

interface User {
    id: number;
    name: string;
}

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
}

interface Appointment {
    id: number;
    doctor_id: number;
    patient_id: number;
    health_insurance_id: number | null;
    coseguro: number | null;
    scheduled_at: string;
    status: "pending" | "confirmed" | "completed" | "cancelled";
    reason: string | null;
    notes: string | null;
    doctor: User;
    patient: Patient;
    health_insurance?: {
        id: number;
        name: string;
    } | null;
}

interface Props {
    appointment: Appointment;
    patients: Record<number, string>;
    doctors: Record<number, string>;
}

const props = defineProps<Props>();

// Convert datetime string to datetime-local format
const formatDateTimeForInput = (datetime: string) => {
    const date = new Date(datetime);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    const hours = String(date.getHours()).padStart(2, "0");
    const minutes = String(date.getMinutes()).padStart(2, "0");
    return `${year}-${month}-${day}T${hours}:${minutes}`;
};

const form = useForm({
    doctor_id: props.appointment.doctor_id,
    patient_id: props.appointment.patient_id,
    health_insurance_id: props.appointment.health_insurance_id || "",
    coseguro: props.appointment.coseguro?.toString() || "",
    scheduled_at: formatDateTimeForInput(props.appointment.scheduled_at),
    status: props.appointment.status,
    reason: props.appointment.reason || "",
    notes: props.appointment.notes || "",
});

const submit = () => {
    form.patch(route("appointments.update", props.appointment.id), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head
        :title="`Editar Cita - ${appointment.patient.first_name} ${appointment.patient.last_name}`"
    />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar cita: {{ appointment.patient.first_name }}
                {{ appointment.patient.last_name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Doctor -->
                        <div>
                            <InputLabel for="doctor_id" value="Médico *" />
                            <select
                                id="doctor_id"
                                v-model="form.doctor_id"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
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

                        <!-- Patient -->
                        <div>
                            <InputLabel for="patient_id" value="Paciente *" />
                            <select
                                id="patient_id"
                                v-model="form.patient_id"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                :class="{
                                    'border-danger-500': form.errors.patient_id,
                                }"
                            >
                                <option
                                    v-for="(name, id) in patients"
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

                        <!-- Información de Cobertura -->
                        <div
                            class="rounded-lg border border-primary-200 bg-primary-50 p-4"
                        >
                            <h3
                                class="mb-3 text-sm font-semibold text-primary-900"
                            >
                                💳 Información de Cobertura
                            </h3>

                            <!-- Current Insurance Display -->
                            <div
                                v-if="appointment.health_insurance"
                                class="mb-3 rounded-lg border border-success-300 bg-success-50 p-3"
                            >
                                <p
                                    class="text-sm font-semibold text-success-900"
                                >
                                    ✓ Obra Social Actual:
                                    {{ appointment.health_insurance.name }}
                                </p>
                            </div>
                            <div
                                v-else
                                class="mb-3 rounded-lg border border-gray-300 bg-gray-50 p-3"
                            >
                                <p class="text-sm text-gray-600">
                                    Sin obra social asignada
                                </p>
                            </div>

                            <!-- Coseguro Amount -->
                            <div>
                                <InputLabel
                                    for="coseguro"
                                    value="Coseguro (Monto a cobrar) $"
                                />
                                <TextInput
                                    id="coseguro"
                                    v-model="form.coseguro"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="Ej: 5000.00"
                                    class="mt-1 block w-full"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.coseguro"
                                />
                                <p class="mt-1 text-xs text-primary-700">
                                    💰 Monto que el paciente debe abonar por la
                                    consulta
                                </p>
                            </div>
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
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                :class="{
                                    'border-danger-500':
                                        form.errors.scheduled_at,
                                }"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.scheduled_at"
                            />
                        </div>

                        <!-- Status -->
                        <div>
                            <InputLabel for="status" value="Estado *" />
                            <select
                                id="status"
                                v-model="form.status"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                :class="{
                                    'border-danger-500': form.errors.status,
                                }"
                            >
                                <option value="pending">Pendiente</option>
                                <option value="confirmed">Confirmada</option>
                                <option value="completed">Completada</option>
                                <option value="cancelled">Cancelada</option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="form.errors.status"
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
                                placeholder="Dolor de cabeza, revisión general, etc."
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
                                placeholder="Notas adicionales sobre la cita..."
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                :class="{
                                    'border-danger-500': form.errors.notes,
                                }"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.notes"
                            />
                        </div>

                        <!-- Submit buttons -->
                        <div class="flex gap-3 pt-4">
                            <PrimaryButton :disabled="form.processing">
                                Guardar cambios
                            </PrimaryButton>
                            <button
                                type="button"
                                @click="
                                    $inertia.visit(route('appointments.index'))
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
