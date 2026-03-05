<script setup lang="ts">
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { ref } from "vue";

interface Doctor {
    id: number;
    name: string;
    specialty?: string;
}

interface Exception {
    id: number;
    doctor_id: number;
    exception_date: string;
    type: string;
    reason?: string;
    is_all_day: boolean;
    start_time?: string;
    end_time?: string;
    type_label: string;
}

interface PaginatedExceptions {
    data: Exception[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    exceptions: PaginatedExceptions;
    doctors: Doctor[];
    selectedDoctorId?: number;
}

const props = defineProps<Props>();

const showModal = ref(false);

const form = useForm({
    doctor_id: props.selectedDoctorId || "",
    exception_date: "",
    type: "vacation",
    reason: "",
    is_all_day: true,
    start_time: "08:00",
    end_time: "17:00",
});

const types = [
    { value: "vacation", label: "Vacaciones" },
    { value: "sick_leave", label: "Licencia médica" },
    { value: "holiday", label: "Feriado" },
    { value: "conference", label: "Congreso/Conferencia" },
    { value: "other", label: "Otro" },
];

const submit = () => {
    form.post(route("doctor-exceptions.store"), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    });
};

const deleteException = (id: number) => {
    if (confirm("¿Está seguro de eliminar esta excepción?")) {
        router.delete(route("doctor-exceptions.destroy", id));
    }
};

const openModal = () => {
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};
</script>

<template>
    <Head title="Excepciones de Horarios" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Excepciones de Horarios
                </h2>
                <div class="flex gap-3">
                    <Link
                        :href="route('doctor-schedules.index')"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Ver Horarios
                    </Link>
                    <button
                        @click="openModal"
                        class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700"
                    >
                        + Nueva Excepción
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Info Box -->
                <div
                    class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4"
                >
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg
                                class="h-5 w-5 text-amber-400"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-amber-800">
                                Días No Laborables
                            </h3>
                            <p class="mt-1 text-sm text-amber-700">
                                Registre días específicos en los que no podrá
                                atender (vacaciones, feriados, etc.). Estos días
                                no aparecerán disponibles para agendar citas.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Exceptions List -->
                <div class="rounded-lg bg-white shadow-sm">
                    <div
                        v-if="exceptions.data.length === 0"
                        class="p-12 text-center"
                    >
                        <svg
                            class="mx-auto h-12 w-12 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                            No hay excepciones registradas
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Agregue días en los que no podrá atender.
                        </p>
                        <div class="mt-6">
                            <button
                                @click="openModal"
                                class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700"
                            >
                                + Agregar Excepción
                            </button>
                        </div>
                    </div>

                    <div v-else class="overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Fecha
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Tipo
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Horario
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Motivo
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                    >
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="exception in exceptions.data"
                                    :key="exception.id"
                                >
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900"
                                    >
                                        {{ exception.exception_date }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm"
                                    >
                                        <span
                                            class="inline-flex rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800"
                                        >
                                            {{ exception.type_label }}
                                        </span>
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-sm text-gray-700"
                                    >
                                        <span v-if="exception.is_all_day">
                                            Todo el día
                                        </span>
                                        <span v-else>
                                            {{ exception.start_time }} -
                                            {{ exception.end_time }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{
                                            exception.reason ||
                                            "Sin especificar"
                                        }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium"
                                    >
                                        <button
                                            @click="
                                                deleteException(exception.id)
                                            "
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0"
            >
                <div
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    @click="closeModal"
                ></div>

                <span
                    class="hidden sm:inline-block sm:h-screen sm:align-middle"
                    aria-hidden="true"
                    >&#8203;</span
                >

                <div
                    class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle"
                >
                    <form @submit.prevent="submit">
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <h3
                                class="mb-4 text-lg font-medium leading-6 text-gray-900"
                            >
                                Agregar Excepción
                            </h3>

                            <div class="space-y-4">
                                <!-- Date -->
                                <div>
                                    <InputLabel
                                        for="exception_date"
                                        value="Fecha *"
                                    />
                                    <input
                                        id="exception_date"
                                        v-model="form.exception_date"
                                        type="date"
                                        required
                                        class="mt-1 block w-full rounded-md border border-gray-300 px-4 py-2 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        :class="{
                                            'border-red-500':
                                                form.errors.exception_date,
                                        }"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.exception_date"
                                    />
                                </div>

                                <!-- Type -->
                                <div>
                                    <InputLabel for="type" value="Tipo *" />
                                    <select
                                        id="type"
                                        v-model="form.type"
                                        required
                                        class="mt-1 block w-full rounded-md border border-gray-300 px-4 py-2 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    >
                                        <option
                                            v-for="type in types"
                                            :key="type.value"
                                            :value="type.value"
                                        >
                                            {{ type.label }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Reason -->
                                <div>
                                    <InputLabel
                                        for="reason"
                                        value="Motivo (Opcional)"
                                    />
                                    <input
                                        id="reason"
                                        v-model="form.reason"
                                        type="text"
                                        placeholder="Ej: Congreso médico en Buenos Aires"
                                        class="mt-1 block w-full rounded-md border border-gray-300 px-4 py-2 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                </div>

                                <!-- All Day -->
                                <div>
                                    <label class="flex items-center">
                                        <input
                                            v-model="form.is_all_day"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        />
                                        <span
                                            class="ml-2 text-sm text-gray-700"
                                        >
                                            Todo el día
                                        </span>
                                    </label>
                                </div>

                                <!-- Time Range (if not all day) -->
                                <div
                                    v-if="!form.is_all_day"
                                    class="grid grid-cols-2 gap-4"
                                >
                                    <div>
                                        <InputLabel
                                            for="start_time"
                                            value="Desde"
                                        />
                                        <input
                                            id="start_time"
                                            v-model="form.start_time"
                                            type="time"
                                            class="mt-1 block w-full rounded-md border border-gray-300 px-4 py-2 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        />
                                    </div>
                                    <div>
                                        <InputLabel
                                            for="end_time"
                                            value="Hasta"
                                        />
                                        <input
                                            id="end_time"
                                            v-model="form.end_time"
                                            type="time"
                                            class="mt-1 block w-full rounded-md border border-gray-300 px-4 py-2 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6"
                        >
                            <PrimaryButton
                                :disabled="form.processing"
                                class="w-full sm:ml-3 sm:w-auto"
                            >
                                Guardar
                            </PrimaryButton>
                            <button
                                type="button"
                                @click="closeModal"
                                class="mt-3 w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto"
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
