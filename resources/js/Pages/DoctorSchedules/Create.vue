<script setup lang="ts">
import { Head, useForm, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

interface Doctor {
    id: number;
    name: string;
    specialty?: string;
}

interface Props {
    doctors: Doctor[];
    defaultDoctorId?: number;
}

const props = defineProps<Props>();

const form = useForm({
    doctor_id: props.defaultDoctorId || "",
    days_of_week: [] as string[],
    start_time: "08:00",
    end_time: "17:00",
    slot_duration: 30,
});

const days = [
    { value: "monday", label: "Lunes" },
    { value: "tuesday", label: "Martes" },
    { value: "wednesday", label: "Miércoles" },
    { value: "thursday", label: "Jueves" },
    { value: "friday", label: "Viernes" },
    { value: "saturday", label: "Sábado" },
    { value: "sunday", label: "Domingo" },
];

const durations = [
    { value: 15, label: "15 minutos" },
    { value: 20, label: "20 minutos" },
    { value: 30, label: "30 minutos" },
    { value: 45, label: "45 minutos" },
    { value: 60, label: "1 hora" },
];

const submit = () => {
    form.post(route("doctor-schedules.store"));
};
</script>

<template>
    <Head title="Nuevo Horario de Atención" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Agregar Horario de Atención
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Doctor Selection (if admin) -->
                        <div v-if="!defaultDoctorId">
                            <InputLabel for="doctor_id" value="Médico *" />
                            <select
                                id="doctor_id"
                                v-model="form.doctor_id"
                                required
                                class="mt-1 block w-full rounded-md border border-gray-300 px-4 py-2 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                :class="{
                                    'border-red-500': form.errors.doctor_id,
                                }"
                            >
                                <option value="">Seleccione un médico</option>
                                <option
                                    v-for="doctor in doctors"
                                    :key="doctor.id"
                                    :value="doctor.id"
                                >
                                    {{ doctor.name }}
                                    <span v-if="doctor.specialty">
                                        - {{ doctor.specialty }}
                                    </span>
                                </option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="form.errors.doctor_id"
                            />
                        </div>

                        <!-- Days of Week (Multiple Selection) -->
                        <div>
                            <InputLabel value="Días de Atención *" />
                            <p class="mt-1 text-sm text-gray-600">
                                ✨ Selecciona todos los días que atiendes con el
                                mismo horario
                            </p>
                            <div
                                class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4"
                            >
                                <label
                                    v-for="day in days"
                                    :key="day.value"
                                    class="flex cursor-pointer items-center rounded-lg border border-gray-300 bg-white px-4 py-3 hover:bg-gray-50"
                                    :class="{
                                        'border-primary-500 bg-primary-50 ring-2 ring-primary-500':
                                            form.days_of_week.includes(
                                                day.value,
                                            ),
                                    }"
                                >
                                    <input
                                        type="checkbox"
                                        :value="day.value"
                                        v-model="form.days_of_week"
                                        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                    />
                                    <span
                                        class="ml-2 text-sm font-medium text-gray-900"
                                    >
                                        {{ day.label }}
                                    </span>
                                </label>
                            </div>
                            <InputError
                                class="mt-2"
                                :message="form.errors.days_of_week"
                            />
                            <p
                                v-if="form.days_of_week.length > 0"
                                class="mt-2 text-sm font-medium text-primary-700"
                            >
                                ✓ {{ form.days_of_week.length }} día(s)
                                seleccionado(s)
                            </p>
                        </div>

                        <!-- Time Range -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel
                                    for="start_time"
                                    value="Hora de Inicio *"
                                />
                                <input
                                    id="start_time"
                                    v-model="form.start_time"
                                    type="time"
                                    required
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-4 py-2 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    :class="{
                                        'border-red-500':
                                            form.errors.start_time,
                                    }"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.start_time"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    for="end_time"
                                    value="Hora de Fin *"
                                />
                                <input
                                    id="end_time"
                                    v-model="form.end_time"
                                    type="time"
                                    required
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-4 py-2 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    :class="{
                                        'border-red-500': form.errors.end_time,
                                    }"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.end_time"
                                />
                            </div>
                        </div>

                        <!-- Slot Duration -->
                        <div>
                            <InputLabel
                                for="slot_duration"
                                value="Duración de cada Cita *"
                            />
                            <select
                                id="slot_duration"
                                v-model="form.slot_duration"
                                required
                                class="mt-1 block w-full rounded-md border border-gray-300 px-4 py-2 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                :class="{
                                    'border-red-500': form.errors.slot_duration,
                                }"
                            >
                                <option
                                    v-for="duration in durations"
                                    :key="duration.value"
                                    :value="duration.value"
                                >
                                    {{ duration.label }}
                                </option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="form.errors.slot_duration"
                            />
                            <p class="mt-1 text-sm text-gray-500">
                                Define cada cuántos minutos se puede agendar una
                                cita
                            </p>
                        </div>

                        <!-- Info Box -->
                        <div
                            class="rounded-lg border border-blue-200 bg-blue-50 p-4"
                        >
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg
                                        class="h-5 w-5 text-blue-400"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        💡 <strong>Consejo:</strong> Puedes
                                        seleccionar múltiples días para aplicar
                                        el mismo horario a todos de una vez. Los
                                        horarios se aplicarán cada semana. Si
                                        necesita cancelar un día específico, use
                                        la sección de "Excepciones".
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex gap-3 pt-4">
                            <PrimaryButton
                                :disabled="
                                    form.processing ||
                                    form.days_of_week.length === 0
                                "
                            >
                                <span v-if="form.days_of_week.length === 0">
                                    Selecciona al menos un día
                                </span>
                                <span
                                    v-else-if="form.days_of_week.length === 1"
                                >
                                    Guardar Horario
                                </span>
                                <span v-else>
                                    Guardar Horarios ({{
                                        form.days_of_week.length
                                    }}
                                    días)
                                </span>
                            </PrimaryButton>
                            <Link
                                :href="route('doctor-schedules.index')"
                                class="rounded-lg border border-gray-300 bg-white px-6 py-2 font-semibold text-gray-700 hover:bg-gray-50"
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
