<script setup lang="ts">
import { computed } from "vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface InvoiceItem {
    id: number;
    description: string;
    quantity: number;
    unit_price: number;
    total: number;
}

interface Payment {
    id: number;
    amount: number;
    payment_method: string;
    reference_number: string | null;
    payment_date: string;
    notes: string | null;
    receiver?: { id: number; name: string };
}

interface Invoice {
    id: number;
    invoice_number: string;
    invoice_date: string;
    subtotal: number;
    discount: number;
    insurance_coverage: number;
    total: number;
    status: string;
    notes: string | null;
    patient?: {
        id: number;
        first_name: string;
        last_name: string;
        dni: string;
    };
    creator?: { id: number; name: string };
    health_insurance?: { id: number; name: string };
    healthInsurance?: { id: number; name: string };
    items: InvoiceItem[];
    payments: Payment[];
}

const props = defineProps<{ invoice: Invoice }>();

const paymentForm = useForm({
    amount: "",
    payment_method: "cash",
    reference_number: "",
    payment_date: new Date().toISOString().slice(0, 10),
    notes: "",
});

const totalPaid = computed(() => {
    return props.invoice.payments.reduce((sum, payment) => {
        return sum + Number(payment.amount || 0);
    }, 0);
});

const balance = computed(() => {
    return Number(props.invoice.total) - totalPaid.value;
});

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

const submitPayment = () => {
    paymentForm.post(route("invoices.payments.store", props.invoice.id), {
        preserveScroll: true,
    });
};

const cancelInvoice = () => {
    if (!confirm("Cancelar factura?")) {
        return;
    }

    router.patch(
        route("invoices.cancel", props.invoice.id),
        {},
        { preserveScroll: true },
    );
};

const deleteInvoice = () => {
    if (!confirm("Eliminar factura? Esta accion no se puede deshacer.")) {
        return;
    }

    router.delete(route("invoices.destroy", props.invoice.id));
};
</script>

<template>
    <Head :title="invoice.invoice_number" />
    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Factura {{ invoice.invoice_number }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        {{
                            new Date(invoice.invoice_date).toLocaleDateString(
                                "es-AR",
                            )
                        }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="route('invoices.edit', invoice.id)"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                        >Editar</Link
                    >
                    <button
                        type="button"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500"
                        @click="cancelInvoice"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-gray-700 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-600"
                        @click="deleteInvoice"
                    >
                        Eliminar
                    </button>
                    <Link
                        :href="route('invoices.index')"
                        class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300"
                        >Volver</Link
                    >
                </div>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">
                        Datos de factura
                    </h2>
                    <div
                        class="grid grid-cols-1 gap-3 text-sm text-gray-700 md:grid-cols-2"
                    >
                        <p>
                            <span class="font-semibold">Paciente:</span>
                            {{ invoice.patient?.first_name }}
                            {{ invoice.patient?.last_name }}
                        </p>
                        <p>
                            <span class="font-semibold">DNI:</span>
                            {{ invoice.patient?.dni || "-" }}
                        </p>
                        <p>
                            <span class="font-semibold">Obra social:</span>
                            {{
                                invoice.healthInsurance?.name ||
                                invoice.health_insurance?.name ||
                                "-"
                            }}
                        </p>
                        <p>
                            <span class="font-semibold">Creada por:</span>
                            {{ invoice.creator?.name || "-" }}
                        </p>
                        <p class="md:col-span-2">
                            <span class="font-semibold">Notas:</span>
                            {{ invoice.notes || "-" }}
                        </p>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">
                        Estado financiero
                    </h2>
                    <p class="mb-2 text-sm">
                        <span class="font-semibold">Subtotal:</span>
                        {{ formatMoney(Number(invoice.subtotal)) }}
                    </p>
                    <p class="mb-2 text-sm">
                        <span class="font-semibold">Descuento:</span>
                        {{ formatMoney(Number(invoice.discount)) }}
                    </p>
                    <p class="mb-2 text-sm">
                        <span class="font-semibold">Cobertura:</span>
                        {{ formatMoney(Number(invoice.insurance_coverage)) }}
                    </p>
                    <p class="mb-2 text-sm">
                        <span class="font-semibold">Total:</span>
                        {{ formatMoney(Number(invoice.total)) }}
                    </p>
                    <p class="mb-2 text-sm">
                        <span class="font-semibold">Pagado:</span>
                        {{ formatMoney(totalPaid) }}
                    </p>
                    <p
                        class="mb-3 text-sm font-semibold"
                        :class="balance > 0 ? 'text-red-600' : 'text-green-600'"
                    >
                        Saldo: {{ formatMoney(balance) }}
                    </p>
                    <span
                        :class="statusClass(invoice.status)"
                        class="rounded-full px-2 py-1 text-xs font-semibold"
                    >
                        {{ statusLabel(invoice.status) }}
                    </span>
                </div>
            </div>

            <div class="mb-6 overflow-hidden rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Items</h2>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Descripcion
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Cantidad
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Precio
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                            >
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="item in invoice.items" :key="item.id">
                            <td class="px-4 py-3 text-sm text-gray-800">
                                {{ item.description }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ item.quantity }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ formatMoney(Number(item.unit_price)) }}
                            </td>
                            <td
                                class="px-4 py-3 text-sm font-semibold text-gray-900"
                            >
                                {{ formatMoney(Number(item.total)) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">
                        Pagos registrados
                    </h2>
                    <div class="space-y-3" v-if="invoice.payments.length > 0">
                        <div
                            v-for="payment in invoice.payments"
                            :key="payment.id"
                            class="rounded-lg border border-gray-200 p-3 text-sm text-gray-700"
                        >
                            <p>
                                <span class="font-semibold">Monto:</span>
                                {{ formatMoney(Number(payment.amount)) }}
                            </p>
                            <p>
                                <span class="font-semibold">Metodo:</span>
                                {{ payment.payment_method }}
                            </p>
                            <p>
                                <span class="font-semibold">Fecha:</span>
                                {{
                                    new Date(
                                        payment.payment_date,
                                    ).toLocaleDateString("es-AR")
                                }}
                            </p>
                            <p>
                                <span class="font-semibold">Recibido por:</span>
                                {{ payment.receiver?.name || "-" }}
                            </p>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-500">
                        Sin pagos registrados.
                    </p>
                </div>

                <div
                    class="rounded-lg bg-white p-6 shadow"
                    v-if="invoice.status !== 'cancelled' && balance > 0"
                >
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">
                        Registrar pago
                    </h2>
                    <form class="space-y-4" @submit.prevent="submitPayment">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Monto *</label
                            >
                            <input
                                v-model="paymentForm.amount"
                                type="number"
                                min="0.01"
                                :max="balance"
                                step="0.01"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Metodo *</label
                            >
                            <select
                                v-model="paymentForm.payment_method"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="cash">Efectivo</option>
                                <option value="card">Tarjeta</option>
                                <option value="transfer">Transferencia</option>
                                <option value="check">Cheque</option>
                                <option value="other">Otro</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Referencia</label
                            >
                            <input
                                v-model="paymentForm.reference_number"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Fecha *</label
                            >
                            <input
                                v-model="paymentForm.payment_date"
                                type="date"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Notas</label
                            >
                            <textarea
                                v-model="paymentForm.notes"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            ></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="paymentForm.processing"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-60"
                            >
                                {{
                                    paymentForm.processing
                                        ? "Guardando..."
                                        : "Registrar pago"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
