<script setup lang="ts">
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface HealthInsurance {
    id: number;
    name: string;
    code: string | null;
    phone: string | null;
    email: string | null;
    copay_amount: number | null;
    copay_percentage: number | null;
    requires_authorization: boolean;
    is_active: boolean;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    insurances: {
        data: HealthInsurance[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginationLink[];
    };
    filters: {
        search?: string;
        is_active?: string | number | boolean;
    };
}

const props = defineProps<Props>();

const filters = ref({
    search: props.filters.search ?? "",
    is_active:
        props.filters.is_active === undefined ||
        props.filters.is_active === null
            ? ""
            : String(props.filters.is_active),
});

const applyFilters = () => {
    router.get(route("health-insurances.index"), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = { search: "", is_active: "" };
    applyFilters();
};

const destroyInsurance = (id: number) => {
    if (!confirm("Eliminar obra social?")) {
        return;
    }

    router.delete(route("health-insurances.destroy", id), {
        preserveScroll: true,
    });
};

const formatMoney = (value: number | null) => {
    if (value === null || value === undefined) {
        return "-";
    }

    return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
    }).format(value);
};
</script>

<template>
    <Head title="Obras Sociales" />
    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Obras Sociales
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Gestion de convenios y copagos
                    </p>
                </div>
                <Link
                    :href="route('health-insurances.create')"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                >
                    Nueva obra social
                </Link>
            </div>

            <div class="mb-6 rounded-lg bg-white p-4 shadow">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Buscar</label
                        >
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Nombre o codigo"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Estado</label
                        >
                        <select
                            v-model="filters.is_active"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            @change="applyFilters"
                        >
                            <option value="">Todos</option>
                            <option value="1">Activa</option>
                            <option value="0">Inactiva</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button
                            type="button"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                            @click="applyFilters"
                        >
                            Aplicar
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300"
                            @click="clearFilters"
                        >
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Nombre
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Codigo
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Contacto
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Copago
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Estado
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr
                            v-for="insurance in insurances.data"
                            :key="insurance.id"
                            class="hover:bg-gray-50"
                        >
                            <td
                                class="px-4 py-3 text-sm font-medium text-gray-900"
                            >
                                {{ insurance.name }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ insurance.code || "-" }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <p>{{ insurance.phone || "-" }}</p>
                                <p>{{ insurance.email || "-" }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <p>
                                    Monto:
                                    {{ formatMoney(insurance.copay_amount) }}
                                </p>
                                <p>
                                    Porcentaje:
                                    {{ insurance.copay_percentage ?? "-" }}%
                                </p>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span
                                    :class="
                                        insurance.is_active
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-gray-100 text-gray-700'
                                    "
                                    class="rounded-full px-2 py-1 text-xs font-semibold"
                                >
                                    {{
                                        insurance.is_active
                                            ? "Activa"
                                            : "Inactiva"
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <div class="flex justify-end gap-3">
                                    <Link
                                        :href="
                                            route(
                                                'health-insurances.show',
                                                insurance.id,
                                            )
                                        "
                                        class="text-indigo-600 hover:text-indigo-800"
                                        >Ver</Link
                                    >
                                    <Link
                                        :href="
                                            route(
                                                'health-insurances.edit',
                                                insurance.id,
                                            )
                                        "
                                        class="text-blue-600 hover:text-blue-800"
                                        >Editar</Link
                                    >
                                    <button
                                        type="button"
                                        class="text-red-600 hover:text-red-800"
                                        @click="destroyInsurance(insurance.id)"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="insurances.data.length === 0">
                            <td
                                colspan="6"
                                class="px-4 py-10 text-center text-sm text-gray-500"
                            >
                                No hay obras sociales registradas.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div
                    class="flex flex-wrap gap-2 border-t border-gray-200 px-4 py-3"
                >
                    <a
                        v-for="link in insurances.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="[
                            'rounded px-3 py-1 text-sm',
                            link.active
                                ? 'bg-indigo-600 text-white'
                                : 'bg-gray-200 text-gray-800',
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
