<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps<{
    item?: {
        id: number;
        name: string;
        type: string;
        description?: string;
        code: string;
        laboratory?: string;
        unit_price: number;
        current_stock: number;
        minimum_stock: number;
        reorder_point: number;
        unit_measurement: string;
        expiration_date?: string;
        batch_number?: string;
        requires_sterilization: boolean;
        last_sterilization_date?: string;
        next_sterilization_date?: string;
        status: string;
        notes?: string;
    };
}>();

const isEditing = !!props.item;

const form = useForm({
    name: props.item?.name || "",
    type: props.item?.type || "medication",
    description: props.item?.description || "",
    code: props.item?.code || "",
    laboratory: props.item?.laboratory || "",
    unit_price: props.item?.unit_price || 0,
    current_stock: props.item?.current_stock || 0,
    minimum_stock: props.item?.minimum_stock || 10,
    reorder_point: props.item?.reorder_point || 20,
    unit_measurement: props.item?.unit_measurement || "unidad",
    expiration_date: props.item?.expiration_date || "",
    batch_number: props.item?.batch_number || "",
    requires_sterilization: props.item?.requires_sterilization || false,
    last_sterilization_date: props.item?.last_sterilization_date || "",
    next_sterilization_date: props.item?.next_sterilization_date || "",
    status: props.item?.status || "active",
    notes: props.item?.notes || "",
});

const submit = () => {
    if (isEditing && props.item) {
        form.put(route("pharmacy.items.update", props.item.id));
    } else {
        form.post(route("pharmacy.items.store"));
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Editar Item' : 'Nuevo Item'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ isEditing ? "✏️ Editar Item" : "➕ Nuevo Item" }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        {{
                            isEditing
                                ? "Actualiza la información del item"
                                : "Agrega un nuevo item al inventario"
                        }}
                    </p>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">
                            Información Básica
                        </h3>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label
                                    for="name"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Nombre <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{
                                        'border-red-300': form.errors.name,
                                    }"
                                />
                                <p
                                    v-if="form.errors.name"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="type"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Tipo <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="type"
                                    v-model="form.type"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="medication">
                                        Medicamento
                                    </option>
                                    <option value="instrument">
                                        Instrumento
                                    </option>
                                    <option value="supply">Insumo</option>
                                </select>
                                <p
                                    v-if="form.errors.type"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.type }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="code"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Código <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="code"
                                    v-model="form.code"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{
                                        'border-red-300': form.errors.code,
                                    }"
                                />
                                <p
                                    v-if="form.errors.code"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.code }}
                                </p>
                            </div>

                            <div>
                                <label
                                    for="laboratory"
                                    class="block text-sm font-medium text-gray-700"
                                    >Laboratorio</label
                                >
                                <input
                                    id="laboratory"
                                    v-model="form.laboratory"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>

                            <div>
                                <label
                                    for="status"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Estado <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="active">Activo</option>
                                    <option value="inactive">Inactivo</option>
                                    <option value="discontinued">
                                        Discontinuado
                                    </option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label
                                    for="description"
                                    class="block text-sm font-medium text-gray-700"
                                    >Descripción</label
                                >
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Stock Information -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">
                            Información de Stock
                        </h3>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label
                                    for="current_stock"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Stock Actual
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="current_stock"
                                    v-model.number="form.current_stock"
                                    type="number"
                                    min="0"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>

                            <div>
                                <label
                                    for="unit_measurement"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Unidad de Medida
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="unit_measurement"
                                    v-model="form.unit_measurement"
                                    type="text"
                                    required
                                    placeholder="unidad, caja, frasco, etc."
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>

                            <div>
                                <label
                                    for="minimum_stock"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Stock Mínimo
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="minimum_stock"
                                    v-model.number="form.minimum_stock"
                                    type="number"
                                    min="0"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>

                            <div>
                                <label
                                    for="reorder_point"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Punto de Reorden
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="reorder_point"
                                    v-model.number="form.reorder_point"
                                    type="number"
                                    min="0"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>

                            <div>
                                <label
                                    for="unit_price"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Precio Unitario
                                    <span class="text-red-500">*</span>
                                </label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div
                                        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                                    >
                                        <span class="text-gray-500 sm:text-sm"
                                            >$</span
                                        >
                                    </div>
                                    <input
                                        id="unit_price"
                                        v-model.number="form.unit_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="block w-full rounded-md border-gray-300 pl-7 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                            </div>

                            <div>
                                <label
                                    for="batch_number"
                                    class="block text-sm font-medium text-gray-700"
                                    >Número de Lote</label
                                >
                                <input
                                    id="batch_number"
                                    v-model="form.batch_number"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>

                            <div>
                                <label
                                    for="expiration_date"
                                    class="block text-sm font-medium text-gray-700"
                                    >Fecha de Vencimiento</label
                                >
                                <input
                                    id="expiration_date"
                                    v-model="form.expiration_date"
                                    type="date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Sterilization Information (for instruments) -->
                    <div
                        v-if="form.type === 'instrument'"
                        class="rounded-lg bg-white p-6 shadow"
                    >
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">
                            Información de Esterilización
                        </h3>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input
                                    v-model="form.requires_sterilization"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <span class="ml-2 text-sm text-gray-700"
                                    >Requiere esterilización</span
                                >
                            </label>
                        </div>

                        <div
                            v-if="form.requires_sterilization"
                            class="grid grid-cols-1 gap-6 sm:grid-cols-2"
                        >
                            <div>
                                <label
                                    for="last_sterilization_date"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Última Esterilización
                                </label>
                                <input
                                    id="last_sterilization_date"
                                    v-model="form.last_sterilization_date"
                                    type="date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>

                            <div>
                                <label
                                    for="next_sterilization_date"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Próxima Esterilización
                                </label>
                                <input
                                    id="next_sterilization_date"
                                    v-model="form.next_sterilization_date"
                                    type="date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">
                            Notas Adicionales
                        </h3>
                        <textarea
                            v-model="form.notes"
                            rows="4"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="Notas o información adicional sobre el item..."
                        />
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3">
                        <Link
                            :href="route('pharmacy.items.index')"
                            class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? "Guardando..."
                                    : isEditing
                                      ? "Actualizar"
                                      : "Crear"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
