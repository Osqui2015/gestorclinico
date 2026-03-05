<script setup lang="ts">
import { computed, ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Modal from "@/Components/Modal.vue";
import { format } from "date-fns";
import { es } from "date-fns/locale";

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
    birth_date: string;
    phone: string | null;
    email: string | null;
    health_insurances?: Array<{
        id: number;
        name: string;
        pivot?: {
            member_number?: string | null;
            is_primary?: boolean;
        };
    }>;
    created_at: string;
    updated_at: string;
}

interface Pagination {
    current_page: number;
    data: Patient[];
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
    patients: Pagination;
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();
const search = ref(props.filters.search || "");
const showDeleteModal = ref(false);
const patientToDelete = ref<Patient | null>(null);

const fullName = (patient: Patient) =>
    `${patient.first_name} ${patient.last_name}`;

const formatDate = (date: string) => {
    return format(new Date(date), "dd/MM/yyyy", { locale: es });
};

const primaryInsurance = (patient: Patient) => {
    return patient.health_insurances?.[0] || null;
};

const handleSearch = () => {
    router.get(
        route("patients.index"),
        { search: search.value },
        { preserveState: true },
    );
};

const confirmDelete = (patient: Patient) => {
    patientToDelete.value = patient;
    showDeleteModal.value = true;
};

const deletePatient = () => {
    if (patientToDelete.value) {
        router.delete(route("patients.destroy", patientToDelete.value.id), {
            onFinish: () => {
                showDeleteModal.value = false;
                patientToDelete.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Pacientes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Gestión de Pacientes
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Header with search and add button -->
                <div
                    class="flex flex-col gap-4 rounded-lg bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex flex-1 gap-2">
                        <TextInput
                            v-model="search"
                            type="text"
                            placeholder="Buscar por nombre o DNI..."
                            class="w-full"
                            @keyup.enter="handleSearch"
                        />
                        <button
                            @click="handleSearch"
                            class="rounded-lg bg-primary-600 px-4 py-2 font-semibold text-white hover:bg-primary-700 focus:outline-none"
                        >
                            Buscar
                        </button>
                    </div>
                    <Link
                        :href="route('patients.create')"
                        class="inline-flex items-center rounded-lg bg-success-600 px-4 py-2 font-semibold text-white hover:bg-success-700 focus:outline-none"
                    >
                        + Nuevo Paciente
                    </Link>
                </div>

                <!-- Patients table -->
                <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-700"
                                >
                                    Nombre
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-700"
                                >
                                    DNI
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-700"
                                >
                                    Edad
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-700"
                                >
                                    Teléfono
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-700"
                                >
                                    Email
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-700"
                                >
                                    Obra social
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold text-gray-700"
                                >
                                    N° afiliado
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-sm font-semibold text-gray-700"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="patient in patients.data"
                                :key="patient.id"
                                class="border-b border-gray-200 hover:bg-gray-50"
                            >
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ fullName(patient) }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm font-mono text-gray-700"
                                >
                                    {{ patient.dni }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{
                                        new Date().getFullYear() -
                                        new Date(
                                            patient.birth_date,
                                        ).getFullYear()
                                    }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ patient.phone || "-" }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ patient.email || "-" }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{
                                        primaryInsurance(patient)?.name ||
                                        "Sin obra social"
                                    }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm font-mono text-gray-700"
                                >
                                    {{
                                        primaryInsurance(patient)?.pivot
                                            ?.member_number || "-"
                                    }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link
                                            :href="
                                                route(
                                                    'patients.show',
                                                    patient.id,
                                                )
                                            "
                                            class="rounded bg-primary-100 px-3 py-1 text-xs font-semibold text-primary-700 hover:bg-primary-200"
                                        >
                                            Ver
                                        </Link>
                                        <Link
                                            :href="
                                                route(
                                                    'patients.edit',
                                                    patient.id,
                                                )
                                            "
                                            class="rounded bg-warning-100 px-3 py-1 text-xs font-semibold text-warning-700 hover:bg-warning-200"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            @click="confirmDelete(patient)"
                                            class="rounded bg-danger-100 px-3 py-1 text-xs font-semibold text-danger-700 hover:bg-danger-200"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="patients.data.length === 0">
                                <td
                                    colspan="8"
                                    class="px-6 py-8 text-center text-sm text-gray-500"
                                >
                                    No hay pacientes registrados
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="patients.last_page > 1"
                    class="flex items-center justify-between rounded-lg bg-white p-4"
                >
                    <span class="text-sm text-gray-600">
                        Mostrando {{ patients.from }} a {{ patients.to }} de
                        {{ patients.total }} resultados
                    </span>
                    <div class="flex gap-1">
                        <template
                            v-for="link in patients.links"
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
                                class="rounded border border-gray-300 px-3 py-2 text-sm hover:bg-gray-100"
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
                <h2 class="text-lg font-bold text-gray-900">
                    Confirmar eliminación
                </h2>
                <p class="mt-4 text-gray-600">
                    ¿Estás seguro de que deseas eliminar a
                    <strong>{{
                        patientToDelete ? fullName(patientToDelete) : ""
                    }}</strong
                    >? Esta acción no se puede deshacer.
                </p>
                <div class="mt-6 flex gap-4">
                    <button
                        @click="showDeleteModal = false"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="deletePatient"
                        class="flex-1 rounded-lg bg-danger-600 px-4 py-2 font-semibold text-white hover:bg-danger-700"
                    >
                        Eliminar
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
