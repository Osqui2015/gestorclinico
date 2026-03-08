<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { reactive } from "vue";

interface OperationRoom {
    id: number;
    name: string;
    code: string;
    display_order: number;
    status: "active" | "maintenance" | "inactive";
    notes?: string;
    upcoming_operations_count: number;
}

const props = defineProps<{
    rooms: OperationRoom[];
    activeRoomsCount: number;
    totalRoomsCount: number;
    permissions: {
        canUpdateCapacity: boolean;
    };
}>();

const capacityForm = useForm({
    room_count: props.activeRoomsCount,
});

const roomForms = reactive<
    Record<number, { name: string; status: string; notes: string }>
>(
    props.rooms.reduce(
        (carry, room) => {
            carry[room.id] = {
                name: room.name,
                status: room.status,
                notes: room.notes || "",
            };
            return carry;
        },
        {} as Record<number, { name: string; status: string; notes: string }>,
    ),
);

const submitCapacity = () => {
    capacityForm.post(route("operations.rooms.capacity"), {
        preserveScroll: true,
    });
};

const updateRoom = (roomId: number) => {
    const payload = roomForms[roomId];

    router.patch(route("operations.rooms.update", roomId), payload, {
        preserveScroll: true,
    });
};

const statusLabel = (status: string) => {
    const map: Record<string, string> = {
        active: "Habilitada",
        maintenance: "Mantenimiento",
        inactive: "Inactiva",
    };
    return map[status] || status;
};

const statusClass = (status: string) => {
    const map: Record<string, string> = {
        active: "bg-green-100 text-green-800",
        maintenance: "bg-yellow-100 text-yellow-800",
        inactive: "bg-gray-100 text-gray-700",
    };
    return map[status] || "bg-gray-100 text-gray-700";
};
</script>

<template>
    <Head title="Configuración de Quirófanos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        ⚙️ Configuración de Quirófanos
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Definición de cantidad de salas y estado operativo
                    </p>
                </div>
                <Link
                    :href="route('operations.index')"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    ← Volver a agenda
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-lg bg-white p-5 shadow">
                        <p class="text-sm text-gray-500">Salas habilitadas</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">
                            {{ activeRoomsCount }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <p class="text-sm text-gray-500">Salas totales</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">
                            {{ totalRoomsCount }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <p class="text-sm text-gray-500">
                            Rango horario agenda
                        </p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">
                            07:00 - 22:00
                        </p>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-5 shadow">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Cantidad de salas habilitadas
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Las salas excedentes se marcan como inactivas si no
                        tienen operaciones futuras.
                    </p>
                    <form
                        class="mt-4 flex flex-wrap items-end gap-3"
                        @submit.prevent="submitCapacity"
                    >
                        <div class="w-40">
                            <label
                                for="room_count"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Cantidad
                            </label>
                            <input
                                id="room_count"
                                v-model.number="capacityForm.room_count"
                                type="number"
                                min="1"
                                max="30"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                :disabled="!permissions.canUpdateCapacity"
                            />
                        </div>
                        <button
                            type="submit"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="
                                !permissions.canUpdateCapacity ||
                                capacityForm.processing
                            "
                        >
                            Actualizar capacidad
                        </button>
                        <p
                            v-if="!permissions.canUpdateCapacity"
                            class="text-sm text-gray-500"
                        >
                            Solo administrador puede cambiar la cantidad total.
                        </p>
                    </form>
                </div>

                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Salas
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                    >
                                        Sala
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                    >
                                        Estado
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                    >
                                        Operaciones futuras
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                    >
                                        Notas
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500"
                                    >
                                        Acción
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="room in rooms"
                                    :key="room.id"
                                    class="align-top"
                                >
                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        <input
                                            v-model="roomForms[room.id].name"
                                            type="text"
                                            class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ room.code }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        <select
                                            v-model="roomForms[room.id].status"
                                            class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option value="active">
                                                Habilitada
                                            </option>
                                            <option value="maintenance">
                                                Mantenimiento
                                            </option>
                                            <option value="inactive">
                                                Inactiva
                                            </option>
                                        </select>
                                        <span
                                            class="mt-2 inline-block rounded-full px-2 py-1 text-xs font-semibold"
                                            :class="
                                                statusClass(
                                                    roomForms[room.id].status,
                                                )
                                            "
                                        >
                                            {{
                                                statusLabel(
                                                    roomForms[room.id].status,
                                                )
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        {{ room.upcoming_operations_count }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-700">
                                        <textarea
                                            v-model="roomForms[room.id].notes"
                                            rows="2"
                                            class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm">
                                        <button
                                            type="button"
                                            class="rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500"
                                            @click="updateRoom(room.id)"
                                        >
                                            Guardar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
