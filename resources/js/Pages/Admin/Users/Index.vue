<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref } from "vue";

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    specialty: string;
    license_number?: string;
    professional_id?: string;
    phone?: string;
    dni?: string;
    created_at: string;
}

interface Props {
    users: {
        data: User[];
        links: any;
    };
    specialties: string[];
}

const props = defineProps<Props>();

const search = ref("");

const filteredUsers = ref(props.users.data);

const getRoleLabel = (role: string) => {
    return role === "admin" ? "Administrador" : "Doctor";
};

const getRoleColor = (role: string) => {
    return role === "admin"
        ? "bg-danger-100 text-danger-800  "
        : "bg-primary-100 text-primary-800  ";
};

const confirmDelete = (userId: number) => {
    if (confirm("¿Estás seguro? Esta acción no se puede deshacer.")) {
        (window as any).location.href = route("admin.users.destroy", userId);
    }
};
</script>

<template>
    <Head title="Gestión de Doctores" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2
                    class="text-xl font-semibold leading-tight text-gray-800 "
                >
                    Gestión de Doctores y Usuarios
                </h2>
                <Link
                    :href="route('admin.users.create')"
                    class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700"
                >
                    + Crear Doctor
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white shadow-sm ">
                    <div
                        class="border-b border-gray-200 p-6 "
                    >
                        <h1
                            class="mb-4 text-2xl font-bold text-gray-900 "
                        >
                            👨‍⚕️ Lista de Doctores ({{ users.data.length }})
                        </h1>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="border-b border-gray-200 bg-gray-50  "
                            >
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left font-semibold text-gray-900 "
                                    >
                                        Nombre
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left font-semibold text-gray-900 "
                                    >
                                        Email
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left font-semibold text-gray-900 "
                                    >
                                        Especialidad
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left font-semibold text-gray-900 "
                                    >
                                        Matrícula (M.N.)
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left font-semibold text-gray-900 "
                                    >
                                        Teléfono
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left font-semibold text-gray-900 "
                                    >
                                        Rol
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center font-semibold text-gray-900 "
                                    >
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-200 "
                            >
                                <tr
                                    v-for="user in users.data"
                                    :key="user.id"
                                    class="hover:bg-gray-50 "
                                >
                                    <td
                                        class="px-6 py-4 text-gray-900 "
                                    >
                                        <div class="font-semibold">
                                            {{ user.name }}
                                        </div>
                                        <div
                                            class="text-xs text-gray-500 "
                                        >
                                            DNI: {{ user.dni || "—" }}
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-gray-900 "
                                    >
                                        {{ user.email }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-gray-900 "
                                    >
                                        {{ user.specialty || "—" }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-gray-900 "
                                    >
                                        <span
                                            v-if="user.license_number"
                                            class="font-mono text-sm"
                                        >
                                            {{ user.license_number }}
                                        </span>
                                        <span v-else class="text-gray-400"
                                            >—</span
                                        >
                                    </td>
                                    <td
                                        class="px-6 py-4 text-gray-900 "
                                    >
                                        {{ user.phone || "—" }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="[
                                                'inline-block rounded-full px-3 py-1 text-xs font-semibold',
                                                getRoleColor(user.role),
                                            ]"
                                        >
                                            {{ getRoleLabel(user.role) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center gap-2">
                                            <Link
                                                :href="
                                                    route(
                                                        'admin.users.edit',
                                                        user.id,
                                                    )
                                                "
                                                class="inline-flex items-center rounded px-2 py-1 text-xs font-semibold text-primary-600 hover:bg-primary-50  "
                                            >
                                                ✏️ Editar
                                            </Link>
                                            <Link
                                                :href="
                                                    route(
                                                        'admin.users.destroy',
                                                        user.id,
                                                    )
                                                "
                                                method="delete"
                                                as="button"
                                                class="inline-flex items-center rounded px-2 py-1 text-xs font-semibold text-danger-600 hover:bg-danger-50  "
                                                @click.prevent="
                                                    confirmDelete(user.id)
                                                "
                                            >
                                                🗑️ Eliminar
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div
                            v-if="users.data.length === 0"
                            class="p-6 text-center text-gray-500 "
                        >
                            No hay doctores registrados
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
