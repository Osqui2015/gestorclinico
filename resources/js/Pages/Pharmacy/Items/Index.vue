<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";

interface PharmacyItem {
    id: number;
    name: string;
    type: string;
    code: string;
    laboratory?: string;
    unit_price: number;
    current_stock: number;
    minimum_stock: number;
    unit_measurement: string;
    expiration_date?: string;
    status: string;
}

interface PaginatedData {
    data: PharmacyItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: any[];
}

const props = defineProps<{
    items: PaginatedData;
    filters: {
        search?: string;
        type?: string;
        status?: string;
        alert?: string;
    };
}>();

const searchTerm = ref(props.filters.search || "");
const selectedType = ref(props.filters.type || "");
const selectedStatus = ref(props.filters.status || "active");
const selectedAlert = ref(props.filters.alert || "");

const search = () => {
    router.get(
        route("pharmacy.items.index"),
        {
            search: searchTerm.value,
            type: selectedType.value,
            status: selectedStatus.value,
            alert: selectedAlert.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const clearFilters = () => {
    searchTerm.value = "";
    selectedType.value = "";
    selectedStatus.value = "active";
    selectedAlert.value = "";
    search();
};

const getTypeLabel = (type: string) => {
    switch (type) {
        case "medication":
            return "Medicamento";
        case "instrument":
            return "Instrumento";
        case "supply":
            return "Insumo";
        default:
            return type;
    }
};

const getTypeColor = (type: string) => {
    switch (type) {
        case "medication":
            return "bg-blue-100 text-blue-800";
        case "instrument":
            return "bg-purple-100 text-purple-800";
        case "supply":
            return "bg-green-100 text-green-800";
        default:
            return "bg-gray-100 text-gray-800";
    }
};

const getStockStatus = (item: PharmacyItem) => {
    if (item.current_stock <= 0) {
        return { label: "Sin stock", color: "text-red-600 font-semibold" };
    } else if (item.current_stock <= item.minimum_stock) {
        return { label: "Stock bajo", color: "text-orange-600 font-semibold" };
    } else {
        return { label: "Stock OK", color: "text-green-600" };
    }
};

const formatDate = (dateString?: string) => {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return date.toLocaleDateString("es-AR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
};
</script>

<template>
    <Head title="Inventario - Farmacia" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        📦 Inventario de Farmacia
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Gestión de medicamentos, instrumentos e insumos
                    </p>
                </div>
                <Link
                    :href="route('pharmacy.items.create')"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                >
                    <svg
                        class="-ml-0.5 mr-1.5 h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                    Nuevo Item
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 rounded-lg bg-white p-4 shadow">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-5">
                        <div class="sm:col-span-2">
                            <label
                                for="search"
                                class="block text-sm font-medium text-gray-700"
                                >Buscar</label
                            >
                            <input
                                id="search"
                                v-model="searchTerm"
                                type="text"
                                placeholder="Nombre, código o laboratorio..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @keyup.enter="search"
                            />
                        </div>

                        <div>
                            <label
                                for="type"
                                class="block text-sm font-medium text-gray-700"
                                >Tipo</label
                            >
                            <select
                                id="type"
                                v-model="selectedType"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @change="search"
                            >
                                <option value="">Todos</option>
                                <option value="medication">Medicamentos</option>
                                <option value="instrument">Instrumentos</option>
                                <option value="supply">Insumos</option>
                            </select>
                        </div>

                        <div>
                            <label
                                for="status"
                                class="block text-sm font-medium text-gray-700"
                                >Estado</label
                            >
                            <select
                                id="status"
                                v-model="selectedStatus"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @change="search"
                            >
                                <option value="">Todos</option>
                                <option value="active">Activos</option>
                                <option value="inactive">Inactivos</option>
                                <option value="discontinued">
                                    Discontinuados
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                for="alert"
                                class="block text-sm font-medium text-gray-700"
                                >Alerta</label
                            >
                            <select
                                id="alert"
                                v-model="selectedAlert"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @change="search"
                            >
                                <option value="">Ninguna</option>
                                <option value="low_stock">Stock Bajo</option>
                                <option value="expiring_soon">
                                    Por Vencer
                                </option>
                                <option value="expired">Vencidos</option>
                                <option value="sterilization_due">
                                    Esterilización Pendiente
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex gap-2">
                        <button
                            type="button"
                            @click="search"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                        >
                            Buscar
                        </button>
                        <button
                            type="button"
                            @click="clearFilters"
                            class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                        >
                            Limpiar
                        </button>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Item
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Tipo
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Código
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Stock
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Vencimiento
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr
                                v-for="item in items.data"
                                :key="item.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">
                                        {{ item.name }}
                                    </div>
                                    <div
                                        v-if="item.laboratory"
                                        class="text-sm text-gray-500"
                                    >
                                        {{ item.laboratory }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                            getTypeColor(item.type),
                                        ]"
                                    >
                                        {{ getTypeLabel(item.type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ item.code }}
                                </td>
                                <td class="px-6 py-4">
                                    <div :class="getStockStatus(item).color">
                                        {{ item.current_stock }}
                                        {{ item.unit_measurement }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Mín: {{ item.minimum_stock }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ formatDate(item.expiration_date) }}
                                </td>
                                <td
                                    class="px-6 py-4 text-right text-sm font-medium"
                                >
                                    <Link
                                        :href="
                                            route(
                                                'pharmacy.items.show',
                                                item.id,
                                            )
                                        "
                                        class="text-indigo-600 hover:text-indigo-900 mr-3"
                                    >
                                        Ver
                                    </Link>
                                    <Link
                                        :href="
                                            route(
                                                'pharmacy.items.edit',
                                                item.id,
                                            )
                                        "
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        Editar
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="items.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-6 py-10 text-center text-sm text-gray-500"
                                >
                                    No se encontraron items
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div
                        v-if="items.last_page > 1"
                        class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6"
                    >
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                v-if="items.current_page > 1"
                                :href="items.links[items.current_page - 1].url"
                                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Anterior
                            </Link>
                            <Link
                                v-if="items.current_page < items.last_page"
                                :href="items.links[items.current_page + 1].url"
                                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Siguiente
                            </Link>
                        </div>
                        <div
                            class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between"
                        >
                            <div>
                                <p class="text-sm text-gray-700">
                                    Mostrando
                                    <span class="font-medium">{{
                                        (items.current_page - 1) *
                                            items.per_page +
                                        1
                                    }}</span>
                                    a
                                    <span class="font-medium">{{
                                        Math.min(
                                            items.current_page * items.per_page,
                                            items.total,
                                        )
                                    }}</span>
                                    de
                                    <span class="font-medium">{{
                                        items.total
                                    }}</span>
                                    resultados
                                </p>
                            </div>
                            <div>
                                <nav
                                    class="isolate inline-flex -space-x-px rounded-md shadow-sm"
                                >
                                    <Link
                                        v-for="(link, index) in items.links"
                                        :key="index"
                                        :href="link.url"
                                        :class="[
                                            'relative inline-flex items-center px-4 py-2 text-sm font-semibold',
                                            link.active
                                                ? 'z-10 bg-indigo-600 text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600'
                                                : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0',
                                            index === 0 ? 'rounded-l-md' : '',
                                            index === items.links.length - 1
                                                ? 'rounded-r-md'
                                                : '',
                                        ]"
                                        v-html="link.label"
                                    />
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
