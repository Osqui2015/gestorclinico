<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { format } from "date-fns";
import { es } from "date-fns/locale";

interface Doctor {
    id: number;
    name: string;
}

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
    health_insurances?: Array<{
        id: number;
        name: string;
        pivot?: {
            member_number?: string | null;
            is_primary?: boolean;
        };
    }>;
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
    appointments: {
        data: Appointment[];
        links: any;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    doctors: Record<number, string>;
    filters: {
        status?: string;
        doctor_id?: number;
        search?: string;
    };
}

const props = defineProps<Props>();
const page = usePage();

const getStatusBadgeColor = (status: string) => {
    return match(status, {
        pending: "gray",
        called: "yellow",
        attending: "blue",
        completed: "green",
        cancelled: "red",
        default: "gray",
    });
};

const getStatusLabel = (status: string) => {
    return match(status, {
        pending: "Pendiente",
        called: "Llamado",
        attending: "Atendiendo",
        completed: "Completado",
        cancelled: "Cancelado",
        default: "Desconocido",
    });
};

const match = (value: string, obj: Record<string, string>) => {
    return obj[value] || obj["default"];
};

const formatDateTime = (date: string) => {
    return format(new Date(date), "dd MMM yyyy - HH:mm", { locale: es });
};

const primaryInsurance = (patient: Patient) => {
    return patient.health_insurances?.[0] || null;
};

const deleteAppointment = (id: number) => {
    if (confirm("¿Estás seguro de que deseas eliminar este turno?")) {
        // TODO: Implement delete with Inertia
        console.log("Delete appointment", id);
    }
};
</script>

<template>
    <Head title="Gestión de Turnos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    📋 Gestión de Turnos
                </h2>
                <Link
                    :href="route('secretary.turns.create')"
                    class="inline-flex items-center rounded-md bg-success-600 px-4 py-2 text-sm font-semibold text-white hover:bg-success-700"
                >
                    ➕ Nuevo Turno
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white shadow-sm">
                    <!-- Filters -->
                    <div class="border-b border-gray-200 p-6">
                        <form
                            :action="route('secretary.turns.index')"
                            method="GET"
                            class="flex flex-wrap gap-4"
                        >
                            <div class="flex-1 min-w-[200px]">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Buscar
                                </label>
                                <input
                                    type="text"
                                    name="search"
                                    placeholder="Nombre o DNI del paciente"
                                    :value="filters.search"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                />
                            </div>

                            <div class="flex-1 min-w-[200px]">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Médico
                                </label>
                                <select
                                    name="doctor_id"
                                    :value="filters.doctor_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
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

                            <div class="flex-1 min-w-[200px]">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Estado
                                </label>
                                <select
                                    name="status"
                                    :value="filters.status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    <option value="">Todos los estados</option>
                                    <option value="pending">Pendiente</option>
                                    <option value="called">Llamado</option>
                                    <option value="attending">
                                        Atendiendo
                                    </option>
                                    <option value="completed">
                                        Completado
                                    </option>
                                    <option value="cancelled">Cancelado</option>
                                </select>
                            </div>

                            <div class="flex items-end gap-2">
                                <button
                                    type="submit"
                                    class="rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700"
                                >
                                    🔍 Filtrar
                                </button>
                                <Link
                                    :href="route('secretary.turns.index')"
                                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                                >
                                    Limpiar
                                </Link>
                            </div>
                        </form>
                    </div>

                    <!-- Turns Table -->
                    <div
                        v-if="appointments.data.length > 0"
                        class="overflow-x-auto"
                    >
                        <table class="w-full text-sm text-gray-900">
                            <thead class="border-b border-gray-200 bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left font-semibold"
                                    >
                                        Paciente
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left font-semibold"
                                    >
                                        Médico
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left font-semibold"
                                    >
                                        Fecha y Hora
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left font-semibold"
                                    >
                                        Motivo
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left font-semibold"
                                    >
                                        Estado
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left font-semibold"
                                    >
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="appointment in appointments.data"
                                    :key="appointment.id"
                                    class="border-b border-gray-200"
                                >
                                    <td class="px-6 py-4">
                                        <div class="font-semibold">
                                            {{ appointment.patient.first_name }}
                                            {{ appointment.patient.last_name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            DNI: {{ appointment.patient.dni }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            OS:
                                            {{
                                                primaryInsurance(
                                                    appointment.patient,
                                                )?.name || "Sin obra social"
                                            }}
                                        </div>
                                        <div
                                            class="text-xs text-gray-500 font-mono"
                                        >
                                            N° afiliado:
                                            {{
                                                primaryInsurance(
                                                    appointment.patient,
                                                )?.pivot?.member_number || "-"
                                            }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ appointment.doctor.name }}
                                    </td>
                                    <td class="px-6 py-4 text-xs">
                                        {{
                                            formatDateTime(
                                                appointment.scheduled_at,
                                            )
                                        }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        {{ appointment.reason || "—" }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="[
                                                'inline-block rounded-full px-3 py-1 text-xs font-semibold',
                                                {
                                                    'bg-gray-100 text-gray-800  ':
                                                        getStatusBadgeColor(
                                                            appointment.status,
                                                        ) === 'gray',
                                                    'bg-warning-100 text-warning-800  ':
                                                        getStatusBadgeColor(
                                                            appointment.status,
                                                        ) === 'yellow',
                                                    'bg-primary-100 text-primary-800  ':
                                                        getStatusBadgeColor(
                                                            appointment.status,
                                                        ) === 'blue',
                                                    'bg-success-100 text-success-800  ':
                                                        getStatusBadgeColor(
                                                            appointment.status,
                                                        ) === 'green',
                                                    'bg-danger-100 text-danger-800  ':
                                                        getStatusBadgeColor(
                                                            appointment.status,
                                                        ) === 'red',
                                                },
                                            ]"
                                        >
                                            {{
                                                getStatusLabel(
                                                    appointment.status,
                                                )
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <Link
                                            :href="
                                                route(
                                                    'secretary.turns.edit',
                                                    appointment.id,
                                                )
                                            "
                                            class="mr-2 text-primary-600 hover:text-primary-900"
                                        >
                                            ✏️ Editar
                                        </Link>
                                        <button
                                            @click="
                                                deleteAppointment(
                                                    appointment.id,
                                                )
                                            "
                                            class="text-danger-600 hover:text-danger-900"
                                        >
                                            🗑️ Eliminar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="p-6 text-center text-gray-500">
                        <p class="mb-2 text-lg">📭 No hay turnos</p>
                        <p class="text-sm">Crea un nuevo turno para comenzar</p>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="appointments.last_page > 1"
                        class="flex items-center justify-between border-t border-gray-200 px-6 py-4"
                    >
                        <div class="text-sm text-gray-600">
                            Página {{ appointments.current_page }} de
                            {{ appointments.last_page }}
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in appointments.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3 py-1 rounded-md text-sm font-semibold',
                                    link.active
                                        ? 'bg-primary-600 text-white'
                                        : 'bg-gray-200 text-gray-900 hover:bg-gray-300   ',
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
