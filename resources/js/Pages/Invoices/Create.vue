<script setup lang="ts">
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
}

interface HealthInsurance {
    id: number;
    name: string;
}

interface InvoiceItem {
    description: string;
    quantity: number;
    unit_price: number;
}

interface ExistingInvoice {
    id: number;
    invoice_number: string;
    patient_id: number;
    appointment_id: number | null;
    health_insurance_id: number | null;
    invoice_date: string;
    discount: number;
    insurance_coverage: number;
    notes: string | null;
    payment_method: string | null;
    items: Array<{
        description: string;
        quantity: number;
        unit_price: number;
    }>;
}

const props = defineProps<{
    patients: Patient[];
    healthInsurances: HealthInsurance[];
    appointment?: { id: number; patient_id: number } | null;
    nextInvoiceNumber: string;
    invoice?: ExistingInvoice;
}>();

const isEditing = computed(() => !!props.invoice);

const form = useForm({
    patient_id:
        props.invoice?.patient_id ?? props.appointment?.patient_id ?? "",
    appointment_id:
        props.invoice?.appointment_id ?? props.appointment?.id ?? "",
    health_insurance_id: props.invoice?.health_insurance_id ?? "",
    invoice_date:
        props.invoice?.invoice_date?.slice(0, 10) ||
        new Date().toISOString().slice(0, 10),
    items: props.invoice?.items?.map((item) => ({
        description: item.description,
        quantity: Number(item.quantity),
        unit_price: Number(item.unit_price),
    })) || [{ description: "", quantity: 1, unit_price: 0 }],
    discount: props.invoice?.discount ?? 0,
    insurance_coverage: props.invoice?.insurance_coverage ?? 0,
    notes: props.invoice?.notes ?? "",
    payment_method: props.invoice?.payment_method ?? "",
});

const addItem = () => {
    form.items.push({ description: "", quantity: 1, unit_price: 0 });
};

const removeItem = (index: number) => {
    if (form.items.length === 1) {
        return;
    }
    form.items.splice(index, 1);
};

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => {
        return sum + Number(item.quantity || 0) * Number(item.unit_price || 0);
    }, 0);
});

const total = computed(() => {
    return (
        subtotal.value -
        Number(form.discount || 0) -
        Number(form.insurance_coverage || 0)
    );
});

const invoiceNumber = computed(() => {
    return isEditing.value && props.invoice
        ? props.invoice.invoice_number
        : props.nextInvoiceNumber;
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        patient_id: data.patient_id || null,
        appointment_id: data.appointment_id || null,
        health_insurance_id: data.health_insurance_id || null,
        discount: Number(data.discount || 0),
        insurance_coverage: Number(data.insurance_coverage || 0),
        payment_method: data.payment_method || null,
        items: data.items.map((item) => ({
            description: item.description,
            quantity: Number(item.quantity),
            unit_price: Number(item.unit_price),
        })),
    }));

    if (isEditing.value && props.invoice) {
        form.patch(route("invoices.update", props.invoice.id));
        return;
    }

    form.post(route("invoices.store"));
};

const formatMoney = (value: number) => {
    return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
    }).format(value);
};
</script>

<template>
    <Head :title="isEditing ? 'Editar factura' : 'Nueva factura'" />
    <AuthenticatedLayout>
        <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ isEditing ? "Editar factura" : "Nueva factura" }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Numero: {{ invoiceNumber }}
                    </p>
                </div>
                <Link
                    :href="route('invoices.index')"
                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-800"
                    >Volver</Link
                >
            </div>

            <form class="space-y-6" @submit.prevent="submit">
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">
                        Cabecera
                    </h2>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Paciente *</label
                            >
                            <select
                                v-model="form.patient_id"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">Seleccionar</option>
                                <option
                                    v-for="patient in props.patients"
                                    :key="patient.id"
                                    :value="patient.id"
                                >
                                    {{ patient.last_name }},
                                    {{ patient.first_name }} ({{ patient.dni }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Obra social</label
                            >
                            <select
                                v-model="form.health_insurance_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">Sin obra social</option>
                                <option
                                    v-for="insurance in props.healthInsurances"
                                    :key="insurance.id"
                                    :value="insurance.id"
                                >
                                    {{ insurance.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Fecha *</label
                            >
                            <input
                                v-model="form.invoice_date"
                                type="date"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Items
                        </h2>
                        <button
                            type="button"
                            class="rounded-md bg-indigo-100 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-200"
                            @click="addItem"
                        >
                            Agregar item
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="grid grid-cols-1 gap-3 rounded-lg border border-gray-200 p-4 md:grid-cols-10"
                        >
                            <div class="md:col-span-5">
                                <label
                                    class="block text-xs font-medium uppercase text-gray-500"
                                    >Descripcion *</label
                                >
                                <input
                                    v-model="item.description"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>
                            <div class="md:col-span-2">
                                <label
                                    class="block text-xs font-medium uppercase text-gray-500"
                                    >Cantidad *</label
                                >
                                <input
                                    v-model.number="item.quantity"
                                    type="number"
                                    min="1"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>
                            <div class="md:col-span-2">
                                <label
                                    class="block text-xs font-medium uppercase text-gray-500"
                                    >Precio unit. *</label
                                >
                                <input
                                    v-model.number="item.unit_price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>
                            <div
                                class="flex items-end justify-end md:col-span-1"
                            >
                                <button
                                    type="button"
                                    class="rounded-md bg-red-100 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-200"
                                    @click="removeItem(index)"
                                >
                                    Quitar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">
                        Ajustes y pago
                    </h2>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Descuento</label
                            >
                            <input
                                v-model.number="form.discount"
                                type="number"
                                min="0"
                                step="0.01"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Cobertura obra social</label
                            >
                            <input
                                v-model.number="form.insurance_coverage"
                                type="number"
                                min="0"
                                step="0.01"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Metodo de pago inicial</label
                            >
                            <select
                                v-model="form.payment_method"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">Sin pago inicial</option>
                                <option value="cash">Efectivo</option>
                                <option value="card">Tarjeta</option>
                                <option value="transfer">Transferencia</option>
                                <option value="insurance">Obra social</option>
                                <option value="other">Otro</option>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Notas</label
                            >
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            ></textarea>
                        </div>
                    </div>

                    <div class="mt-6 rounded-lg bg-gray-50 p-4 text-sm">
                        <p>
                            <span class="font-semibold">Subtotal:</span>
                            {{ formatMoney(subtotal) }}
                        </p>
                        <p>
                            <span class="font-semibold">Descuento:</span>
                            {{ formatMoney(Number(form.discount || 0)) }}
                        </p>
                        <p>
                            <span class="font-semibold">Cobertura:</span>
                            {{
                                formatMoney(
                                    Number(form.insurance_coverage || 0),
                                )
                            }}
                        </p>
                        <p class="mt-2 text-lg font-bold text-gray-900">
                            Total: {{ formatMoney(total) }}
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <Link
                        :href="route('invoices.index')"
                        class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300"
                        >Cancelar</Link
                    >
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-60"
                    >
                        {{
                            form.processing
                                ? "Guardando..."
                                : isEditing
                                  ? "Actualizar factura"
                                  : "Crear factura"
                        }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
