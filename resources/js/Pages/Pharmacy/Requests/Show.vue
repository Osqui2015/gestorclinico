<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref } from "vue";

interface User {
    id: number;
    name: string;
}

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
}

interface PharmacyItem {
    id: number;
    name: string;
    code: string;
    current_stock: number;
    unit_measurement: string;
}

interface RequestItem {
    id: number;
    pharmacy_item: PharmacyItem;
    quantity_requested: number;
    quantity_delivered: number;
    notes?: string;
}

interface PharmacyRequest {
    id: number;
    requested_by: User;
    processed_by?: User;
    patient?: Patient;
    priority: string;
    status: string;
    requested_at: string;
    processed_at?: string;
    completed_at?: string;
    notes?: string;
    pharmacy_notes?: string;
    items: RequestItem[];
}

const props = defineProps<{
    pharmacyRequest: PharmacyRequest;
}>();

const showDeliverModal = ref(false);
const showCancelModal = ref(false);

const deliverForm = useForm({
    items: props.pharmacyRequest.items.map((item) => ({
        id: item.id,
        quantity_delivered: item.quantity_requested - item.quantity_delivered,
    })),
    pharmacy_notes: "",
});

const cancelForm = useForm({
    cancellation_reason: "",
});

const processRequest = () => {
    router.post(
        route("pharmacy.requests.process", props.pharmacyRequest.id),
        {},
        {
            preserveScroll: true,
        },
    );
};

const openDeliverModal = () => {
    showDeliverModal.value = true;
};

const closeDeliverModal = () => {
    showDeliverModal.value = false;
    deliverForm.reset();
};

const submitDelivery = () => {
    deliverForm.post(
        route("pharmacy.requests.deliver", props.pharmacyRequest.id),
        {
            preserveScroll: true,
            onSuccess: () => closeDeliverModal(),
        },
    );
};

const openCancelModal = () => {
    showCancelModal.value = true;
};

const closeCancelModal = () => {
    showCancelModal.value = false;
    cancelForm.reset();
};

const submitCancellation = () => {
    cancelForm.post(
        route("pharmacy.requests.cancel", props.pharmacyRequest.id),
        {
            preserveScroll: true,
            onSuccess: () => closeCancelModal(),
        },
    );
};

const getPriorityLabel = (priority: string) => {
    const labels: Record<string, string> = {
        low: "Baja",
        normal: "Normal",
        high: "Alta",
        urgent: "Urgente",
    };
    return labels[priority] || priority;
};

const getPriorityClass = (priority: string) => {
    const classes: Record<string, string> = {
        low: "bg-gray-100 text-gray-800",
        normal: "bg-blue-100 text-blue-800",
        high: "bg-orange-100 text-orange-800",
        urgent: "bg-red-100 text-red-800",
    };
    return classes[priority] || "bg-gray-100 text-gray-800";
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        pending: "Pendiente",
        processing: "En Proceso",
        completed: "Completada",
        cancelled: "Cancelada",
    };
    return labels[status] || status;
};

const getStatusClass = (status: string) => {
    const classes: Record<string, string> = {
        pending: "bg-yellow-100 text-yellow-800",
        processing: "bg-blue-100 text-blue-800",
        completed: "bg-green-100 text-green-800",
        cancelled: "bg-gray-100 text-gray-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const formatDateTime = (date: string) => {
    return new Date(date).toLocaleString("es-AR");
};

const isFullyDelivered = (item: RequestItem) => {
    return item.quantity_delivered >= item.quantity_requested;
};

const canDeliver = (item: RequestItem) => {
    return !isFullyDelivered(item) && item.pharmacy_item.current_stock > 0;
};

const getMaxDeliverable = (item: RequestItem) => {
    const remaining = item.quantity_requested - item.quantity_delivered;
    return Math.min(remaining, item.pharmacy_item.current_stock);
};
</script>

<template>
    <Head title="Detalle de Solicitud" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        Solicitud #{{ pharmacyRequest.id }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Solicitado por
                        {{ pharmacyRequest.requested_by.name }} el
                        {{ formatDateTime(pharmacyRequest.requested_at) }}
                    </p>
                </div>
                <div class="flex space-x-3">
                    <button
                        v-if="pharmacyRequest.status === 'pending'"
                        @click="processRequest"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500"
                    >
                        🚀 Procesar
                    </button>
                    <button
                        v-if="pharmacyRequest.status === 'processing'"
                        @click="openDeliverModal"
                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500"
                    >
                        📦 Entregar Items
                    </button>
                    <button
                        v-if="
                            pharmacyRequest.status === 'pending' ||
                            pharmacyRequest.status === 'processing'
                        "
                        @click="openCancelModal"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500"
                    >
                        ❌ Cancelar
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="space-y-6">
                    <!-- Request Information -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">
                            Información de la Solicitud
                        </h3>
                        <dl
                            class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2"
                        >
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Estado
                                </dt>
                                <dd class="mt-1">
                                    <span
                                        :class="[
                                            getStatusClass(
                                                pharmacyRequest.status,
                                            ),
                                            'inline-flex rounded-full px-2 text-xs font-semibold leading-5',
                                        ]"
                                    >
                                        {{
                                            getStatusLabel(
                                                pharmacyRequest.status,
                                            )
                                        }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Prioridad
                                </dt>
                                <dd class="mt-1">
                                    <span
                                        :class="[
                                            getPriorityClass(
                                                pharmacyRequest.priority,
                                            ),
                                            'inline-flex rounded-full px-2 text-xs font-semibold leading-5',
                                        ]"
                                    >
                                        {{
                                            getPriorityLabel(
                                                pharmacyRequest.priority,
                                            )
                                        }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Solicitado por
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ pharmacyRequest.requested_by.name }}
                                </dd>
                            </div>
                            <div v-if="pharmacyRequest.processed_by">
                                <dt class="text-sm font-medium text-gray-500">
                                    Procesado por
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ pharmacyRequest.processed_by.name }}
                                </dd>
                            </div>
                            <div v-if="pharmacyRequest.patient">
                                <dt class="text-sm font-medium text-gray-500">
                                    Paciente
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ pharmacyRequest.patient.first_name }}
                                    {{ pharmacyRequest.patient.last_name }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Fecha de solicitud
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{
                                        formatDateTime(
                                            pharmacyRequest.requested_at,
                                        )
                                    }}
                                </dd>
                            </div>
                            <div v-if="pharmacyRequest.processed_at">
                                <dt class="text-sm font-medium text-gray-500">
                                    Fecha de procesamiento
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{
                                        formatDateTime(
                                            pharmacyRequest.processed_at,
                                        )
                                    }}
                                </dd>
                            </div>
                            <div v-if="pharmacyRequest.completed_at">
                                <dt class="text-sm font-medium text-gray-500">
                                    Fecha de completado
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{
                                        formatDateTime(
                                            pharmacyRequest.completed_at,
                                        )
                                    }}
                                </dd>
                            </div>
                            <div
                                v-if="pharmacyRequest.notes"
                                class="sm:col-span-2"
                            >
                                <dt class="text-sm font-medium text-gray-500">
                                    Notas del solicitante
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ pharmacyRequest.notes }}
                                </dd>
                            </div>
                            <div
                                v-if="pharmacyRequest.pharmacy_notes"
                                class="sm:col-span-2"
                            >
                                <dt class="text-sm font-medium text-gray-500">
                                    Notas de farmacia
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ pharmacyRequest.pharmacy_notes }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Request Items -->
                    <div class="rounded-lg bg-white shadow">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Items Solicitados
                            </h3>
                        </div>
                        <div class="overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Item
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Código
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Cantidad Solicitada
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Cantidad Entregada
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Stock Disponible
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Estado
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-gray-200 bg-white"
                                >
                                    <tr
                                        v-for="item in pharmacyRequest.items"
                                        :key="item.id"
                                    >
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'pharmacy.items.show',
                                                        item.pharmacy_item.id,
                                                    )
                                                "
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                {{ item.pharmacy_item.name }}
                                            </Link>
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm text-gray-500"
                                        >
                                            {{ item.pharmacy_item.code }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm text-gray-900"
                                        >
                                            {{ item.quantity_requested }}
                                            {{
                                                item.pharmacy_item
                                                    .unit_measurement
                                            }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm text-gray-900"
                                        >
                                            {{ item.quantity_delivered }}
                                            {{
                                                item.pharmacy_item
                                                    .unit_measurement
                                            }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm"
                                            :class="[
                                                item.pharmacy_item
                                                    .current_stock > 0
                                                    ? 'text-green-600'
                                                    : 'text-red-600',
                                            ]"
                                        >
                                            {{
                                                item.pharmacy_item.current_stock
                                            }}
                                            {{
                                                item.pharmacy_item
                                                    .unit_measurement
                                            }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm"
                                        >
                                            <span
                                                v-if="isFullyDelivered(item)"
                                                class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800"
                                            >
                                                ✓ Completado
                                            </span>
                                            <span
                                                v-else-if="
                                                    item.quantity_delivered > 0
                                                "
                                                class="inline-flex rounded-full bg-yellow-100 px-2 text-xs font-semibold leading-5 text-yellow-800"
                                            >
                                                ⏳ Parcial
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800"
                                            >
                                                ⭕ Pendiente
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deliver Modal -->
        <div
            v-if="showDeliverModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0"
            >
                <div
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    @click="closeDeliverModal"
                ></div>

                <span
                    class="hidden sm:inline-block sm:h-screen sm:align-middle"
                    aria-hidden="true"
                    >&#8203;</span
                >

                <div
                    class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle"
                >
                    <form @submit.prevent="submitDelivery">
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <h3
                                class="mb-4 text-lg font-semibold text-gray-900"
                                id="modal-title"
                            >
                                Entregar Items
                            </h3>

                            <div class="space-y-4">
                                <div
                                    v-for="(
                                        formItem, index
                                    ) in deliverForm.items"
                                    :key="formItem.id"
                                    class="rounded-lg border border-gray-200 p-4"
                                >
                                    <div class="mb-2 font-medium text-gray-900">
                                        {{
                                            pharmacyRequest.items[index]
                                                .pharmacy_item.name
                                        }}
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-500"
                                                >Solicitado:</span
                                            >
                                            {{
                                                pharmacyRequest.items[index]
                                                    .quantity_requested
                                            }}
                                        </div>
                                        <div>
                                            <span class="text-gray-500"
                                                >Ya entregado:</span
                                            >
                                            {{
                                                pharmacyRequest.items[index]
                                                    .quantity_delivered
                                            }}
                                        </div>
                                        <div>
                                            <span class="text-gray-500"
                                                >Stock disponible:</span
                                            >
                                            {{
                                                pharmacyRequest.items[index]
                                                    .pharmacy_item.current_stock
                                            }}
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label
                                            :for="`quantity-${formItem.id}`"
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            Cantidad a entregar ahora
                                        </label>
                                        <input
                                            :id="`quantity-${formItem.id}`"
                                            v-model.number="
                                                formItem.quantity_delivered
                                            "
                                            type="number"
                                            min="0"
                                            :max="
                                                getMaxDeliverable(
                                                    pharmacyRequest.items[
                                                        index
                                                    ],
                                                )
                                            "
                                            :disabled="
                                                !canDeliver(
                                                    pharmacyRequest.items[
                                                        index
                                                    ],
                                                )
                                            "
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:opacity-50"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="pharmacy_notes"
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Notas de farmacia (opcional)
                                    </label>
                                    <textarea
                                        id="pharmacy_notes"
                                        v-model="deliverForm.pharmacy_notes"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                            </div>
                        </div>
                        <div
                            class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6"
                        >
                            <button
                                type="submit"
                                :disabled="deliverForm.processing"
                                class="inline-flex w-full justify-center rounded-md bg-green-600 px-4 py-2 text-base font-semibold text-white shadow-sm hover:bg-green-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                            >
                                Confirmar Entrega
                            </button>
                            <button
                                type="button"
                                @click="closeDeliverModal"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2 text-base font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm"
                            >
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Cancel Modal -->
        <div
            v-if="showCancelModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="cancel-modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0"
            >
                <div
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    @click="closeCancelModal"
                ></div>

                <span
                    class="hidden sm:inline-block sm:h-screen sm:align-middle"
                    aria-hidden="true"
                    >&#8203;</span
                >

                <div
                    class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle"
                >
                    <form @submit.prevent="submitCancellation">
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <h3
                                class="mb-4 text-lg font-semibold text-gray-900"
                                id="cancel-modal-title"
                            >
                                Cancelar Solicitud
                            </h3>

                            <div>
                                <label
                                    for="cancellation_reason"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Motivo de cancelación
                                    <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    id="cancellation_reason"
                                    v-model="cancelForm.cancellation_reason"
                                    rows="3"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{
                                        'border-red-300':
                                            cancelForm.errors
                                                .cancellation_reason,
                                    }"
                                />
                                <p
                                    v-if="cancelForm.errors.cancellation_reason"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ cancelForm.errors.cancellation_reason }}
                                </p>
                            </div>
                        </div>
                        <div
                            class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6"
                        >
                            <button
                                type="submit"
                                :disabled="cancelForm.processing"
                                class="inline-flex w-full justify-center rounded-md bg-red-600 px-4 py-2 text-base font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                            >
                                Confirmar Cancelación
                            </button>
                            <button
                                type="button"
                                @click="closeCancelModal"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2 text-base font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm"
                            >
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
