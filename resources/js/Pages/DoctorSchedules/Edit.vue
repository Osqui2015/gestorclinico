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

interface Schedule {
    id: number;
    doctor_id: number;
    day_of_week: string;
    start_time: string;
    end_time: string;
    slot_duration: number;
    is_active: boolean;
}

interface Props {
    schedule: Schedule;
    doctors: Doctor[];
}

const props = defineProps<Props>();

const form = useForm({
    doctor_id: props.schedule.doctor_id,
    day_of_week: props.schedule.day_of_week,
    start_time: props.schedule.start_time,
    end_time: props.schedule.end_time,
    slot_duration: props.schedule.slot_duration,
    is_active: props.schedule.is_active,
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
    form.patch(route("doctor-schedules.update", props.schedule.id));
};
</script>

<template>
    <Head title="Editar Horario de Atención" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar Horario de Atención
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Day of Week -->
                        <div>
                            <InputLabel
                                for="day_of_week"
                                value="Día de la Semana *"
                            />
                            <select
                                id="day_of_week"
                                v-model="form.day_of_week"
                                required
                                class="mt-1 block w-full rounded-md border border-gray-300 px-4 py-2 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                :class="{
                                    'border-red-500': form.errors.day_of_week,
                                }"
                            >
                                <option value="">Seleccione un día</option>
                                <option
                                    v-for="day in days"
                                    :key="day.value"
                                    :value="day.value"
                                >
                                    {{ day.label }}
                                </option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="form.errors.day_of_week"
                            />
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
                        </div>

                        <!-- Is Active -->
                        <div>
                            <label class="flex items-center">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                <span class="ml-2 text-sm text-gray-700">
                                    Horario activo
                                </span>
                            </label>
                            <p class="mt-1 text-sm text-gray-500">
                                Si desactiva este horario, no se podrán agendar
                                citas en este día
                            </p>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex gap-3 pt-4">
                            <PrimaryButton :disabled="form.processing">
                                Actualizar Horario
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
