<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { reactive, ref } from "vue";

interface Bed {
    id: number;
    bed_number: number;
    bed_type: string;
    status: string;
    is_active: boolean;
}

interface Room {
    id: number;
    name: string;
    code: string;
    room_type: string;
    floor: number;
    wing: string | null;
    max_beds: number;
    description: string | null;
    is_active: boolean;
    total_beds: number;
    active_beds: number;
    available_beds: number;
    occupied_beds: number;
    beds: Bed[];
}

interface Stats {
    total_rooms: number;
    active_rooms: number;
    total_beds: number;
    active_beds: number;
    available_beds: number;
    occupied_beds: number;
}

const props = defineProps<{
    rooms: Room[];
    stats: Stats;
    roomTypes: Record<string, string>;
    bedTypes: Record<string, string>;
    permissions: {
        canManage: boolean;
    };
}>();

const showCreateModal = ref(false);
const editingRoom = ref<Room | null>(null);

const createForm = useForm({
    name: "",
    code: "",
    room_type: "standard",
    floor: 1,
    wing: "",
    max_beds: 2,
    description: "",
});

const roomForms = reactive<
    Record<
        number,
        {
            name: string;
            code: string;
            room_type: string;
            floor: number;
            wing: string;
            max_beds: number;
            description: string;
            is_active: boolean;
        }
    >
>(
    props.rooms.reduce(
        (carry, room) => {
            carry[room.id] = {
                name: room.name,
                code: room.code || "",
                room_type: room.room_type,
                floor: room.floor,
                wing: room.wing || "",
                max_beds: room.max_beds,
                description: room.description || "",
                is_active: room.is_active,
            };
            return carry;
        },
        {} as Record<
            number,
            {
                name: string;
                code: string;
                room_type: string;
                floor: number;
                wing: string;
                max_beds: number;
                description: string;
                is_active: boolean;
            }
        >,
    ),
);

const submitCreate = () => {
    createForm.post(route("rooms.store"), {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset();
            showCreateModal.value = false;
        },
    });
};

const updateRoom = (roomId: number) => {
    const payload = roomForms[roomId];

    router.patch(route("rooms.update", roomId), payload, {
        preserveScroll: true,
    });
};

const deleteRoom = (roomId: number) => {
    if (
        confirm(
            "¿Está seguro de desactivar esta habitación? Se desactivarán todas las camas disponibles.",
        )
    ) {
        router.delete(route("rooms.destroy", roomId), {
            preserveScroll: true,
        });
    }
};

const roomTypeLabel = (type: string) => {
    return props.roomTypes[type] || type;
};

const statusBadgeClass = (room: Room) => {
    if (!room.is_active) {
        return "bg-gray-100 text-gray-700";
    }
    if (room.occupied_beds > 0) {
        return "bg-green-100 text-green-800";
    }
    return "bg-blue-100 text-blue-700";
};

const statusBadgeText = (room: Room) => {
    if (!room.is_active) return "Inactiva";
    if (room.occupied_beds > 0) return "En uso";
    return "Disponible";
};
</script>

<template>
    <Head title="Configuración de Habitaciones y Camas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        🛏️ Configuración de Habitaciones y Camas
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Gestión de habitaciones y capacidad de internación
                    </p>
                </div>
                <div class="flex gap-2">
                    <button
                        v-if="permissions.canManage"
                        type="button"
                        @click="showCreateModal = true"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                    >
                        + Nueva Habitación
                    </button>
                    <Link
                        :href="route('hospitalizations.index')"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        ← Volver a camas
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Estadísticas -->
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div class="rounded-lg bg-white p-5 shadow">
                        <p class="text-sm text-gray-500">
                            Habitaciones activas
                        </p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">
                            {{ stats.active_rooms }} / {{ stats.total_rooms }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <p class="text-sm text-gray-500">Camas activas</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">
                            {{ stats.active_beds }} / {{ stats.total_beds }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <p class="text-sm text-gray-500">Camas disponibles</p>
                        <p class="mt-1 text-2xl font-bold text-green-600">
                            {{ stats.available_beds }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <p class="text-sm text-gray-500">Camas ocupadas</p>
                        <p class="mt-1 text-2xl font-bold text-blue-600">
                            {{ stats.occupied_beds }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <p class="text-sm text-gray-500">Tasa de ocupación</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">
                            {{
                                stats.active_beds > 0
                                    ? Math.round(
                                          (stats.occupied_beds /
                                              stats.active_beds) *
                                              100,
                                      )
                                    : 0
                            }}%
                        </p>
                    </div>
                </div>

                <!-- Lista de habitaciones -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Habitaciones
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                    >
                                        Habitación
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                    >
                                        Tipo
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                    >
                                        Piso / Ala
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                    >
                                        Camas
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                    >
                                        Estado
                                    </th>
                                    <th
                                        v-if="permissions.canManage"
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                    >
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="room in rooms"
                                    :key="room.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ room.name }}
                                        </div>
                                        <div
                                            v-if="room.code"
                                            class="text-xs text-gray-500"
                                        >
                                            {{ room.code }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        {{ roomTypeLabel(room.room_type) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        Piso {{ room.floor }}
                                        <span v-if="room.wing">
                                            / {{ room.wing }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm text-gray-900">
                                            {{ room.available_beds }}
                                            disponibles
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ room.occupied_beds }} ocupadas /
                                            {{ room.active_beds }} activas
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            :class="statusBadgeClass(room)"
                                            class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                        >
                                            {{ statusBadgeText(room) }}
                                        </span>
                                    </td>
                                    <td
                                        v-if="permissions.canManage"
                                        class="px-4 py-3"
                                    >
                                        <button
                                            type="button"
                                            @click="
                                                editingRoom =
                                                    editingRoom?.id === room.id
                                                        ? null
                                                        : room
                                            "
                                            class="text-sm font-medium text-indigo-600 hover:text-indigo-900"
                                        >
                                            {{
                                                editingRoom?.id === room.id
                                                    ? "Cancelar"
                                                    : "Editar"
                                            }}
                                        </button>
                                    </td>
                                </tr>
                                <!-- Formulario de edición expandible -->
                                <tr
                                    v-if="
                                        editingRoom &&
                                        permissions.canManage &&
                                        roomForms[editingRoom.id]
                                    "
                                    class="bg-gray-50"
                                >
                                    <td colspan="6" class="px-4 py-4">
                                        <form
                                            @submit.prevent="
                                                updateRoom(editingRoom.id)
                                            "
                                            class="space-y-4"
                                        >
                                            <div
                                                class="grid grid-cols-1 gap-4 md:grid-cols-3"
                                            >
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700"
                                                    >
                                                        Nombre
                                                    </label>
                                                    <input
                                                        v-model="
                                                            roomForms[
                                                                editingRoom.id
                                                            ].name
                                                        "
                                                        type="text"
                                                        required
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    />
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700"
                                                    >
                                                        Código
                                                    </label>
                                                    <input
                                                        v-model="
                                                            roomForms[
                                                                editingRoom.id
                                                            ].code
                                                        "
                                                        type="text"
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    />
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700"
                                                    >
                                                        Tipo
                                                    </label>
                                                    <select
                                                        v-model="
                                                            roomForms[
                                                                editingRoom.id
                                                            ].room_type
                                                        "
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    >
                                                        <option
                                                            v-for="(
                                                                label, value
                                                            ) in roomTypes"
                                                            :key="value"
                                                            :value="value"
                                                        >
                                                            {{ label }}
                                                        </option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700"
                                                    >
                                                        Piso
                                                    </label>
                                                    <input
                                                        v-model.number="
                                                            roomForms[
                                                                editingRoom.id
                                                            ].floor
                                                        "
                                                        type="number"
                                                        min="0"
                                                        required
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    />
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700"
                                                    >
                                                        Ala/Sector
                                                    </label>
                                                    <input
                                                        v-model="
                                                            roomForms[
                                                                editingRoom.id
                                                            ].wing
                                                        "
                                                        type="text"
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    />
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700"
                                                    >
                                                        Máximo de camas
                                                    </label>
                                                    <input
                                                        v-model.number="
                                                            roomForms[
                                                                editingRoom.id
                                                            ].max_beds
                                                        "
                                                        type="number"
                                                        min="1"
                                                        max="20"
                                                        required
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    />
                                                </div>
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-gray-700"
                                                >
                                                    Descripción
                                                </label>
                                                <textarea
                                                    v-model="
                                                        roomForms[
                                                            editingRoom.id
                                                        ].description
                                                    "
                                                    rows="2"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                ></textarea>
                                            </div>
                                            <div class="flex items-center">
                                                <input
                                                    v-model="
                                                        roomForms[
                                                            editingRoom.id
                                                        ].is_active
                                                    "
                                                    type="checkbox"
                                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                />
                                                <label
                                                    class="ml-2 block text-sm text-gray-700"
                                                >
                                                    Habitación activa
                                                </label>
                                            </div>
                                            <div class="flex gap-2">
                                                <button
                                                    type="submit"
                                                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                                                >
                                                    Guardar cambios
                                                </button>
                                                <button
                                                    v-if="
                                                        editingRoom.occupied_beds ===
                                                        0
                                                    "
                                                    type="button"
                                                    @click="
                                                        deleteRoom(
                                                            editingRoom.id,
                                                        )
                                                    "
                                                    class="rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50"
                                                >
                                                    Desactivar habitación
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal crear habitación -->
        <div
            v-if="showCreateModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
            @click.self="showCreateModal = false"
        >
            <div
                class="w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl"
                @click.stop
            >
                <h3 class="mb-4 text-xl font-bold text-gray-900">
                    Nueva Habitación
                </h3>
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                            >
                                Nombre *
                            </label>
                            <input
                                v-model="createForm.name"
                                type="text"
                                required
                                placeholder="Ej: Habitación 101"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                            >
                                Código
                            </label>
                            <input
                                v-model="createForm.code"
                                type="text"
                                placeholder="Ej: H101"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                            >
                                Tipo *
                            </label>
                            <select
                                v-model="createForm.room_type"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option
                                    v-for="(label, value) in roomTypes"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                            >
                                Piso *
                            </label>
                            <input
                                v-model.number="createForm.floor"
                                type="number"
                                min="0"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                            >
                                Ala/Sector
                            </label>
                            <input
                                v-model="createForm.wing"
                                type="text"
                                placeholder="Ej: Norte, Sur..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                            >
                                Cantidad de camas *
                            </label>
                            <input
                                v-model.number="createForm.max_beds"
                                type="number"
                                min="1"
                                max="20"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Descripción
                        </label>
                        <textarea
                            v-model="createForm.description"
                            rows="3"
                            placeholder="Características, equipamiento especial, etc."
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        ></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-4">
                        <button
                            type="button"
                            @click="showCreateModal = false"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="createForm.processing"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Crear habitación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
