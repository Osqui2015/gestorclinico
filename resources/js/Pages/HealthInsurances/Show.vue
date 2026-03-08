<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface Insurance {
    id: number;
    name: string;
    code: string | null;
    phone: string | null;
    email: string | null;
    copay_amount: number | null;
    copay_percentage: number | null;
    requires_authorization: boolean;
    is_active: boolean;
    notes: string | null;
    patients?: Array<{
        id: number;
        first_name: string;
        last_name: string;
        dni: string;
    }>;
    invoices?: Array<{
        id: number;
        invoice_number: string;
        total: number;
        status: string;
    }>;
}

const props = defineProps<{ insurance: Insurance }>();

const formatMoney = (value: number | null) => {
    if (value === null || value === undefined) {
        return "-";
    }

    return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
    }).format(value);
};

const destroyInsurance = () => {
    if (!confirm("Eliminar obra social?")) {
        return;
    }

    router.delete(route("health-insurances.destroy", props.insurance.id));
};
</script>

<template>
    <Head :title="insurance.name" />
    <AuthenticatedLayout>
        <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ insurance.name }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Codigo: {{ insurance.code || "-" }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="route('health-insurances.edit', insurance.id)"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                        >Editar</Link
                    >
                    <button
                        type="button"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500"
                        @click="destroyInsurance"
                    >
                        Eliminar
                    </button>
                    <Link
                        :href="route('health-insurances.index')"
                        class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300"
                        >Volver</Link
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">
                        Datos generales
                    </h2>
                    <dl class="space-y-3 text-sm text-gray-700">
                        <div>
                            <span class="font-semibold">Telefono:</span>
                            {{ insurance.phone || "-" }}
                        </div>
                        <div>
                            <span class="font-semibold">Email:</span>
                            {{ insurance.email || "-" }}
                        </div>
                        <div>
                            <span class="font-semibold">Copago monto:</span>
                            {{ formatMoney(insurance.copay_amount) }}
                        </div>
                        <div>
                            <span class="font-semibold"
                                >Copago porcentaje:</span
                            >
                            {{ insurance.copay_percentage ?? "-" }}%
                        </div>
                        <div>
                            <span class="font-semibold">Autorizacion:</span>
                            {{ insurance.requires_authorization ? "Si" : "No" }}
                        </div>
                        <div>
                            <span class="font-semibold">Estado:</span>
                            {{ insurance.is_active ? "Activa" : "Inactiva" }}
                        </div>
                        <div>
                            <span class="font-semibold">Notas:</span>
                            {{ insurance.notes || "-" }}
                        </div>
                    </dl>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">
                        Resumen de uso
                    </h2>
                    <div class="space-y-3 text-sm text-gray-700">
                        <p>
                            <span class="font-semibold"
                                >Pacientes asociados:</span
                            >
                            {{ insurance.patients?.length ?? 0 }}
                        </p>
                        <p>
                            <span class="font-semibold"
                                >Facturas asociadas:</span
                            >
                            {{ insurance.invoices?.length ?? 0 }}
                        </p>
                    </div>

                    <div
                        v-if="
                            insurance.patients && insurance.patients.length > 0
                        "
                        class="mt-4"
                    >
                        <h3 class="mb-2 text-sm font-semibold text-gray-800">
                            Pacientes
                        </h3>
                        <ul class="space-y-1 text-sm text-gray-600">
                            <li
                                v-for="patient in insurance.patients.slice(
                                    0,
                                    5,
                                )"
                                :key="patient.id"
                            >
                                {{ patient.first_name }}
                                {{ patient.last_name }} ({{ patient.dni }})
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
