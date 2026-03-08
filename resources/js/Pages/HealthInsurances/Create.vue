<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const form = useForm({
    name: "",
    code: "",
    phone: "",
    email: "",
    copay_amount: "",
    copay_percentage: "",
    requires_authorization: false,
    is_active: true,
    notes: "",
});

const submit = () => {
    form.post(route("health-insurances.store"));
};
</script>

<template>
    <Head title="Nueva Obra Social" />
    <AuthenticatedLayout>
        <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">
                    Nueva Obra Social
                </h1>
                <Link
                    :href="route('health-insurances.index')"
                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-800"
                >
                    Volver
                </Link>
            </div>

            <form
                class="space-y-6 rounded-lg bg-white p-6 shadow"
                @submit.prevent="submit"
            >
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700"
                            >Nombre *</label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Codigo</label
                        >
                        <input
                            v-model="form.code"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Telefono</label
                        >
                        <input
                            v-model="form.phone"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Email</label
                        >
                        <input
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Copago monto</label
                        >
                        <input
                            v-model="form.copay_amount"
                            type="number"
                            min="0"
                            step="0.01"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Copago porcentaje</label
                        >
                        <input
                            v-model="form.copay_percentage"
                            type="number"
                            min="0"
                            max="100"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            id="requires_authorization"
                            v-model="form.requires_authorization"
                            type="checkbox"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        />
                        <label
                            for="requires_authorization"
                            class="text-sm text-gray-700"
                            >Requiere autorizacion</label
                        >
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            id="is_active"
                            v-model="form.is_active"
                            type="checkbox"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        />
                        <label for="is_active" class="text-sm text-gray-700"
                            >Activa</label
                        >
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700"
                            >Notas</label
                        >
                        <textarea
                            v-model="form.notes"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        ></textarea>
                    </div>
                </div>

                <div
                    class="flex justify-end gap-2 border-t border-gray-200 pt-4"
                >
                    <Link
                        :href="route('health-insurances.index')"
                        class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-60"
                    >
                        {{ form.processing ? "Guardando..." : "Guardar" }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
