<script setup lang="ts">
import { ref } from "vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface PatientAccount {
    id: number;
    patient: {
        first_name: string;
        last_name: string;
    };
    balance: number;
}

interface Props {
    account: PatientAccount;
}

const props = defineProps<Props>();

const form = useForm({
    amount: "",
    payment_method: "cash",
    concept: "",
    voucher_number: "",
    notes: "",
});

const paymentMethods = [
    { value: "cash", label: "Efectivo" },
    { value: "check", label: "Cheque" },
    { value: "transfer", label: "Transferencia Bancaria" },
    { value: "credit_card", label: "Tarjeta de Crédito" },
    { value: "debit_card", label: "Tarjeta de Débito" },
    { value: "promissory_note", label: "Pagaré" },
    { value: "credit", label: "Crédito" },
    { value: "insurance", label: "Cobertura de Obra Social" },
    { value: "other", label: "Otro" },
];

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
    }).format(value);
};

const submit = () => {
    form.post(route("accounting.record-payment", props.account.id), {
        onSuccess: () => {
            router.get(route("accounting.show", props.account.id));
        },
    });
};
</script>

<template>
    <Head :title="`Registrar Pago - ${account.patient.first_name}`" />
    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto px-4 py-8">
            <div class="bg-white rounded-lg shadow-md p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    Registrar Pago
                </h1>
                <p class="text-gray-600 mb-6">
                    {{ account.patient.first_name }}
                    {{ account.patient.last_name }}
                </p>

                <!-- Current Balance -->
                <div
                    class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6"
                >
                    <p class="text-sm text-gray-600">Balance Actual</p>
                    <p
                        class="text-2xl font-bold"
                        :class="
                            account.balance < 0
                                ? 'text-red-600'
                                : 'text-green-600'
                        "
                    >
                        {{ formatCurrency(account.balance) }}
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Amount -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Monto a Pagar *</label
                        >
                        <input
                            v-model="form.amount"
                            type="number"
                            step="0.01"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                            :class="{ 'border-red-500': form.errors.amount }"
                            placeholder="0.00"
                        />
                        <p
                            v-if="form.errors.amount"
                            class="text-red-600 text-sm mt-1"
                        >
                            {{ form.errors.amount }}
                        </p>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Método de Pago *</label
                        >
                        <select
                            v-model="form.payment_method"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                        >
                            <option
                                v-for="method in paymentMethods"
                                :key="method.value"
                                :value="method.value"
                            >
                                {{ method.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Voucher Number -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Número de Comprobante</label
                        >
                        <input
                            v-model="form.voucher_number"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                            placeholder="Ej: CHQ-12345 o TRA-98765"
                        />
                    </div>

                    <!-- Concept -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Concepto / Descripción</label
                        >
                        <input
                            v-model="form.concept"
                            type="text"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                            placeholder="Ej: Pago parcial de cuenta corriente"
                        />
                    </div>

                    <!-- Notes -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Notas / Observaciones</label
                        >
                        <textarea
                            v-model="form.notes"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                            rows="3"
                            placeholder="Notas adicionales"
                        ></textarea>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex gap-4 pt-6 border-t border-gray-200">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 transition"
                        >
                            {{
                                form.processing
                                    ? "Procesando..."
                                    : "✓ Registrar Pago"
                            }}
                        </button>
                        <button
                            type="button"
                            @click="
                                router.get(route('accounting.show', account.id))
                            "
                            class="px-6 py-2 bg-gray-300 text-gray-900 rounded-lg hover:bg-gray-400 transition"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
