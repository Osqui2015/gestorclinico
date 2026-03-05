<script setup lang="ts">
import { computed, ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import Modal from "@/Components/Modal.vue";
import { format } from "date-fns";
import { es } from "date-fns/locale";

interface User {
    id: number;
    name: string;
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
    status: "pending" | "confirmed" | "completed" | "cancelled";
    reason: string | null;
    notes: string | null;
    doctor: User;
    patient: Patient;
    created_at: string;
}

interface Pagination {
    current_page: number;
    data: Appointment[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: any[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

interface Props {
    appointments: Pagination;
    doctors: Record<number, string>;
    filters: {
        status?: string;
        doctor_id?: string;
        search?: string;
    };
}

const props = defineProps<Props>();
const search = ref(props.filters.search || "");
const filterStatus = ref(props.filters.status || "");
const filterDoctor = ref(props.filters.doctor_id || "");
const showDeleteModal = ref(false);
const appointmentToDelete = ref<Appointment | null>(null);

const formatDateTime = (date: string) => {
    return format(new Date(date), "dd/MM/yyyy HH:mm", { locale: es });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: "gray",
        confirmed: "blue",
        completed: "green",
        cancelled: "red",
    };
    return colors[status] || "gray";
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        pending: "Pendiente",
        confirmed: "Confirmada",
        completed: "Completada",
        cancelled: "Cancelada",
    };
    return labels[status] || "Desconocido";
};

const handleFilter = () => {
    router.get(
        route("appointments.index"),
        {
            search: search.value,
            status: filterStatus.value,
            doctor_id: filterDoctor.value,
        },
        { preserveState: true },
    );
};

const confirmDelete = (appointment: Appointment) => {
    appointmentToDelete.value = appointment;
    showDeleteModal.value = true;
};

const deleteAppointment = () => {
    if (appointmentToDelete.value) {
        router.delete(
            route("appointments.destroy", appointmentToDelete.value.id),
            {
                onFinish: () => {
                    showDeleteModal.value = false;
                    appointmentToDelete.value = null;
                },
            },
        );
    }
};

const statusOptions = [
    { value: "", label: "Todos los estados" },
    { value: "pending", label: "Pendiente" },
    { value: "confirmed", label: "Confirmada" },
    { value: "completed", label: "Completada" },
    { value: "cancelled", label: "Cancelada" },
];
</script>

<template>
    <Head title="Citas Médicas" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 "
            >
                Gestión de Citas
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="rounded-lg bg-white p-6 shadow-sm ">
                    <h3
                        class="mb-4 text-lg font-semibold text-gray-900 "
                    >
                        Filtros
                    </h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 "
                            >
                                Buscar paciente
                            </label>
                            <TextInput
                                v-model="search"
                                type="text"
                                placeholder="Nombre o DNI..."
                                class="mt-1 w-full"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 "
                            >
                                Estado
                            </label>
                            <select
                                v-model="filterStatus"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500   "
                            >
                                <option
                                    v-for="option in statusOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 "
                            >
                                Médico
                            </label>
                            <select
                                v-model="filterDoctor"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500   "
                            >
                                <option value="">Todos los médicos</option>
                                <option
                                    v-for="(name, id) in doctors"
                                    :key="id"
                                    :value="id"
                                >
                                    {{ name }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button
                                @click="handleFilter"
                                class="w-full rounded-lg bg-primary-600 px-4 py-2 font-semibold text-white hover:bg-primary-700"
                            >
                                Filtrar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Header with new appointment button -->
                <div class="flex justify-end">
                    <Link
                        :href="route('appointments.create')"
                        class="inline-flex items-center rounded-lg bg-success-600 px-4 py-2 font-semibold text-white hover:bg-success-700"
                    >
                        + Nueva Cita
                    </Link>
                </div>

                <!-- Appointments table -->
                <div
                    class="overflow-x-auto rounded-lg bg-white shadow-sm "
                >
                    <table class="w-full">
                        <thead>
                            <tr
                                class="border-b border-gray-200 bg-gray-50  "
                            >
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-700 "
                                >
                                    Fecha y Hora
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-700 "
                                >
                                    Médico
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-700 "
                                >
                                    Paciente
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-700 "
                                >
                                    Estado
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-700 "
                                >
                                    Motivo
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-sm font-semibold text-gray-700 "
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="appointment in appointments.data"
                                :key="appointment.id"
                                class="border-b border-gray-200 hover:bg-gray-50  "
                            >
                                <td
                                    class="px-6 py-4 text-sm text-gray-900 "
                                >
                                    {{
                                        formatDateTime(appointment.scheduled_at)
                                    }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-gray-900 "
                                >
                                    {{ appointment.doctor.name }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-gray-900 "
                                >
                                    {{ appointment.patient.first_name }}
                                    {{ appointment.patient.last_name }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="{
                                            'rounded-full px-3 py-1 text-xs font-semibold': true,
                                            'bg-gray-100 text-gray-800  ':
                                                getStatusColor(
                                                    appointment.status,
                                                ) === 'gray',
                                            'bg-primary-100 text-primary-800  ':
                                                getStatusColor(
                                                    appointment.status,
                                                ) === 'blue',
                                            'bg-success-100 text-success-800  ':
                                                getStatusColor(
                                                    appointment.status,
                                                ) === 'green',
                                            'bg-danger-100 text-danger-800  ':
                                                getStatusColor(
                                                    appointment.status,
                                                ) === 'red',
                                        }"
                                    >
                                        {{ getStatusLabel(appointment.status) }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-gray-700 "
                                >
                                    {{ appointment.reason || "-" }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link
                                            :href="
                                                route(
                                                    'appointments.show',
                                                    appointment.id,
                                                )
                                            "
                                            class="rounded bg-primary-100 px-3 py-1 text-xs font-semibold text-primary-700 hover:bg-primary-200   "
                                        >
                                            Ver
                                        </Link>
                                        <Link
                                            :href="
                                                route(
                                                    'appointments.edit',
                                                    appointment.id,
                                                )
                                            "
                                            class="rounded bg-warning-100 px-3 py-1 text-xs font-semibold text-warning-700 hover:bg-warning-200   "
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            @click="confirmDelete(appointment)"
                                            class="rounded bg-danger-100 px-3 py-1 text-xs font-semibold text-danger-700 hover:bg-danger-200   "
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="appointments.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-6 py-8 text-center text-sm text-gray-500 "
                                >
                                    No hay citas registradas
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="appointments.last_page > 1"
                    class="flex items-center justify-between rounded-lg bg-white p-4 "
                >
                    <span class="text-sm text-gray-600 ">
                        Mostrando {{ appointments.from }} a
                        {{ appointments.to }} de
                        {{ appointments.total }} resultados
                    </span>
                    <div class="flex gap-1">
                        <template
                            v-for="link in appointments.links"
                            :key="link.label"
                        >
                            <component
                                v-if="!link.url"
                                :is="'span'"
                                class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-500"
                            >
                                {{ link.label }}
                            </component>
                            <Link
                                v-else
                                :href="link.url"
                                class="rounded border border-gray-300 px-3 py-2 text-sm hover:bg-gray-100  "
                                :class="{
                                    'bg-primary-50 text-primary-600 ':
                                        link.active,
                                }"
                            >
                                {{ link.label }}
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete confirmation modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 ">
                    Confirmar eliminación de cita
                </h2>
                <p
                    v-if="appointmentToDelete"
                    class="mt-4 text-gray-600 "
                >
                    ¿Estás seguro de que deseas eliminar la cita de
                    <strong
                        >{{ appointmentToDelete.patient.first_name }}
                        {{ appointmentToDelete.patient.last_name }}</strong
                    >
                    con
                    <strong>{{ appointmentToDelete.doctor.name }}</strong> el
                    {{ formatDateTime(appointmentToDelete.scheduled_at) }}? Esta
                    acción no se puede deshacer.
                </p>
                <div class="mt-6 flex gap-4">
                    <button
                        @click="showDeleteModal = false"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50    "
                    >
                        Cancelar
                    </button>
                    <button
                        @click="deleteAppointment"
                        class="flex-1 rounded-lg bg-danger-600 px-4 py-2 font-semibold text-white hover:bg-danger-700"
                    >
                        Eliminar
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
