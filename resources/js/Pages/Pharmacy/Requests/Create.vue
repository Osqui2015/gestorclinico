<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";

interface PharmacyItem {
    id: number;
    name: string;
    code: string;
    type: string;
    current_stock: number;
    unit_measurement: string;
    laboratory?: string;
}

const props = defineProps<{
    items: PharmacyItem[];
    patientId?: number;
    appointmentId?: number;
}>();

interface RequestItem {
    pharmacy_item_id: number;
    quantity_requested: number;
    notes: string;
}

const form = useForm({
    patient_id: props.patientId || null,
    appointment_id: props.appointmentId || null,
    priority: "normal",
    notes: "",
    items: [] as RequestItem[],
});

const searchTerm = ref("");
const selectedItems = ref<Set<number>>(new Set());

const filteredItems = computed(() => {
    if (!searchTerm.value) return props.items;

    const term = searchTerm.value.toLowerCase();
    return props.items.filter(
        (item) =>
            item.name.toLowerCase().includes(term) ||
            item.code.toLowerCase().includes(term) ||
            item.laboratory?.toLowerCase().includes(term),
    );
});

const addItem = (item: PharmacyItem) => {
    if (!selectedItems.value.has(item.id)) {
        selectedItems.value.add(item.id);
        form.items.push({
            pharmacy_item_id: item.id,
            quantity_requested: 1,
            notes: "",
        });
    }
};

const removeItem = (itemId: number) => {
    selectedItems.value.delete(itemId);
    const index = form.items.findIndex(
        (item) => item.pharmacy_item_id === itemId,
    );
    if (index !== -1) {
        form.items.splice(index, 1);
    }
};

const getItemDetails = (itemId: number) => {
    return props.items.find((item) => item.id === itemId);
};

const submit = () => {
    form.post(route("pharmacy-requests.store"));
};

const getTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        medication: "Medicamento",
        instrument: "Instrumento",
        supply: "Insumo",
    };
    return labels[type] || type;
};
</script>

<template>
    <Head title="Nueva Solicitud de Farmacia" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        ➕ Nueva Solicitud de Farmacia
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Solicita medicamentos, instrumentos o insumos
                    </p>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Request Details -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">
                            Detalles de la Solicitud
                        </h3>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label
                                    for="priority"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Prioridad
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="priority"
                                    v-model="form.priority"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="low">Baja</option>
                                    <option value="normal">Normal</option>
                                    <option value="high">Alta</option>
                                    <option value="urgent">Urgente</option>
                                </select>
                                <p
                                    v-if="form.errors.priority"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.priority }}
                                </p>
                            </div>

                            <div class="sm:col-span-2">
                                <label
                                    for="notes"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Notas (opcional)
                                </label>
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                    placeholder="Información adicional sobre la solicitud..."
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Add Items -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">
                            Agregar Items
                        </h3>

                        <!-- Search -->
                        <div class="mb-4">
                            <label
                                for="search"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Buscar items
                            </label>
                            <input
                                id="search"
                                v-model="searchTerm"
                                type="text"
                                placeholder="Nombre, código o laboratorio..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                        </div>

                        <!-- Available Items -->
                        <div
                            class="max-h-96 overflow-y-auto rounded-lg border border-gray-200"
                        >
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="sticky top-0 bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Nombre
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Tipo
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Código
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Stock
                                        </th>
                                        <th class="relative px-6 py-3">
                                            <span class="sr-only">Añadir</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-gray-200 bg-white"
                                >
                                    <tr
                                        v-for="item in filteredItems"
                                        :key="item.id"
                                        :class="{
                                            'bg-gray-50': selectedItems.has(
                                                item.id,
                                            ),
                                        }"
                                    >
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900"
                                        >
                                            {{ item.name }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm text-gray-500"
                                        >
                                            {{ getTypeLabel(item.type) }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm text-gray-500"
                                        >
                                            {{ item.code }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm"
                                            :class="[
                                                item.current_stock > 0
                                                    ? 'text-green-600'
                                                    : 'text-red-600',
                                            ]"
                                        >
                                            {{ item.current_stock }}
                                            {{ item.unit_measurement }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium"
                                        >
                                            <button
                                                v-if="
                                                    !selectedItems.has(item.id)
                                                "
                                                type="button"
                                                @click="addItem(item)"
                                                :disabled="
                                                    item.current_stock === 0
                                                "
                                                class="text-indigo-600 hover:text-indigo-900 disabled:cursor-not-allowed disabled:opacity-50"
                                            >
                                                ➕ Añadir
                                            </button>
                                            <span v-else class="text-green-600">
                                                ✓ Añadido
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div
                                v-if="filteredItems.length === 0"
                                class="px-6 py-12 text-center"
                            >
                                <p class="text-sm text-gray-500">
                                    No se encontraron items
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Items -->
                    <div
                        v-if="form.items.length > 0"
                        class="rounded-lg bg-white p-6 shadow"
                    >
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">
                            Items Seleccionados ({{ form.items.length }})
                        </h3>

                        <div class="space-y-4">
                            <div
                                v-for="(formItem, index) in form.items"
                                :key="formItem.pharmacy_item_id"
                                class="rounded-lg border border-gray-200 p-4"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">
                                            {{
                                                getItemDetails(
                                                    formItem.pharmacy_item_id,
                                                )?.name
                                            }}
                                        </h4>
                                        <p class="text-sm text-gray-500">
                                            Código:
                                            {{
                                                getItemDetails(
                                                    formItem.pharmacy_item_id,
                                                )?.code
                                            }}
                                            | Stock disponible:
                                            {{
                                                getItemDetails(
                                                    formItem.pharmacy_item_id,
                                                )?.current_stock
                                            }}
                                            {{
                                                getItemDetails(
                                                    formItem.pharmacy_item_id,
                                                )?.unit_measurement
                                            }}
                                        </p>

                                        <div
                                            class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2"
                                        >
                                            <div>
                                                <label
                                                    :for="`quantity-${index}`"
                                                    class="block text-sm font-medium text-gray-700"
                                                >
                                                    Cantidad solicitada
                                                    <span class="text-red-500"
                                                        >*</span
                                                    >
                                                </label>
                                                <input
                                                    :id="`quantity-${index}`"
                                                    v-model.number="
                                                        formItem.quantity_requested
                                                    "
                                                    type="number"
                                                    min="1"
                                                    required
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                />
                                            </div>

                                            <div>
                                                <label
                                                    :for="`notes-${index}`"
                                                    class="block text-sm font-medium text-gray-700"
                                                >
                                                    Notas (opcional)
                                                </label>
                                                <input
                                                    :id="`notes-${index}`"
                                                    v-model="formItem.notes"
                                                    type="text"
                                                    placeholder="Información adicional..."
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        @click="
                                            removeItem(
                                                formItem.pharmacy_item_id,
                                            )
                                        "
                                        class="ml-4 text-red-600 hover:text-red-900"
                                    >
                                        ❌
                                    </button>
                                </div>
                            </div>
                        </div>

                        <p
                            v-if="form.errors.items"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.items }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <Link
                            :href="route('pharmacy-requests.index')"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="
                                form.processing || form.items.length === 0
                            "
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? "Enviando..."
                                    : "Enviar Solicitud"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
