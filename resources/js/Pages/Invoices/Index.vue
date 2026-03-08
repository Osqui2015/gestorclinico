<script setup lang="ts">
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface Invoice {
    id: number;
    invoice_number: string;
    invoice_date: string;
    total: number;
    status: string;
    patient?: {
        id: number;
        first_name: string;
        last_name: string;
        dni: string;
    };
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    invoices: {
        data: Invoice[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginationLink[];
    };
    filters: {
        search?: string;
        status?: string;
        from_date?: string;
        to_date?: string;
    };
}

const props = defineProps<Props>();

const filters = ref({
    search: props.filters.search || "",
    status: props.filters.status || "",
    from_date: props.filters.from_date || "",
    to_date: props.filters.to_date || "",
});

const applyFilters = () => {
    router.get(route("invoices.index"), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = {
        search: "",
        status: "",
        from_date: "",
        to_date: "",
    };
    applyFilters();
};

const formatMoney = (value: number) => {
    return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
    }).format(value);
};

const statusLabel = (status: string) => {
    const map: Record<string, string> = {
        pending: "Pendiente",
        paid: "Pagada",
        partially_paid: "Parcial",
        cancelled: "Cancelada",
    };
    return map[status] || status;
};

const statusClass = (status: string) => {
    const map: Record<string, string> = {
        pending: "bg-yellow-100 text-yellow-800",
        paid: "bg-green-100 text-green-800",
        partially_paid: "bg-blue-100 text-blue-800",
        cancelled: "bg-red-100 text-red-800",
    };
    return map[status] || "bg-gray-100 text-gray-800";
};
</script>

<template>
    <Head title="Facturacion" />
    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Facturas</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Listado y seguimiento de facturacion
                    </p>
                </div>
                <Link
                    :href="route('invoices.create')"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                >
                    Nueva factura
                </Link>
            </div>

            <div class="mb-6 rounded-lg bg-white p-4 shadow">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Buscar</label
                        >
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Nro factura o paciente"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Estado</label
                        >
                        <select
                            v-model="filters.status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            @change="applyFilters"
                        >
                            <option value="">Todos</option>
                            <option value="pending">Pendiente</option>
                            <option value="partially_paid">Parcial</option>
                            <option value="paid">Pagada</option>
                            <option value="cancelled">Cancelada</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Desde</label
                        >
                        <input
                            v-model="filters.from_date"
                            type="date"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            @change="applyFilters"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Hasta</label
                        >
                        <input
                            v-model="filters.to_date"
                            type="date"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            @change="applyFilters"
                        />
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
                                Nro
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Paciente
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Fecha
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Total
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
                            v-for="invoice in invoices.data"
                            :key="invoice.id"
                            class="hover:bg-gray-50"
                        >
                            <td
                                class="px-4 py-3 text-sm font-medium text-gray-900"
                            >
                                {{ invoice.invoice_number }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <p>
                                    {{ invoice.patient?.first_name }}
                                    {{ invoice.patient?.last_name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ invoice.patient?.dni || "-" }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{
                                    new Date(
                                        invoice.invoice_date,
                                    ).toLocaleDateString("es-AR")
                                }}
                            </td>
                            <td
                                class="px-4 py-3 text-sm font-semibold text-gray-900"
                            >
                                {{ formatMoney(Number(invoice.total)) }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span
                                    :class="statusClass(invoice.status)"
                                    class="rounded-full px-2 py-1 text-xs font-semibold"
                                >
                                    {{ statusLabel(invoice.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <div class="flex justify-end gap-3">
                                    <Link
                                        :href="
                                            route('invoices.show', invoice.id)
                                        "
                                        class="text-indigo-600 hover:text-indigo-800"
                                        >Ver</Link
                                    >
                                    <Link
                                        :href="
                                            route('invoices.edit', invoice.id)
                                        "
                                        class="text-blue-600 hover:text-blue-800"
                                        >Editar</Link
                                    >
                                </div>
                            </td>
                        </tr>
                        <tr v-if="invoices.data.length === 0">
                            <td
                                colspan="6"
                                class="px-4 py-10 text-center text-sm text-gray-500"
                            >
                                No hay facturas para mostrar.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div
                    class="flex flex-wrap gap-2 border-t border-gray-200 px-4 py-3"
                >
                    <a
                        v-for="link in invoices.links"
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
