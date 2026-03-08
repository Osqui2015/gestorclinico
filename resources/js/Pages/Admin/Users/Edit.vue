<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { computed } from "vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

interface User {
    id: number;
    name: string;
    email: string;
    specialty: string;
    role: string;
    dni?: string;
    license_number?: string;
    professional_id?: string;
    phone?: string;
    address?: string;
}

interface Props {
    user: User;
    specialties: string[];
}

const props = defineProps<Props>();

const form = useForm({
    name: props.user.name,
    dni: props.user.dni || "",
    email: props.user.email,
    specialty: props.user.specialty,
    license_number: props.user.license_number || "",
    professional_id: props.user.professional_id || "",
    phone: props.user.phone || "",
    address: props.user.address || "",
    role: props.user.role,
});

const isDoctorRole = computed(() => form.role === "doctor");

const submit = () => {
    form.patch(route("admin.users.update", props.user.id));
};
</script>

<template>
    <Head title="Editar Usuario" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Editar Usuario
                </h2>
                <Link
                    :href="route('admin.users.index')"
                    class="inline-flex items-center rounded-md bg-gray-800 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-700"
                >
                    ← Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white shadow-sm">
                    <div class="border-b border-gray-200 p-6">
                        <h1 class="text-2xl font-bold text-gray-900">
                            ✏️ Editar: {{ user.name }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-600">
                            Actualiza la información del usuario
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6 p-6">
                        <!-- Sección: Información Personal -->
                        <div class="border-b pb-6">
                            <h3
                                class="mb-4 text-lg font-semibold text-gray-900"
                            >
                                📋 Información Personal
                            </h3>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <!-- Nombre -->
                                <div>
                                    <InputLabel
                                        for="name"
                                        value="Nombre Completo *"
                                    />
                                    <TextInput
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.name"
                                    />
                                </div>

                                <!-- DNI -->
                                <div>
                                    <InputLabel
                                        for="dni"
                                        value="DNI / Cédula *"
                                    />
                                    <TextInput
                                        id="dni"
                                        v-model="form.dni"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.dni"
                                    />
                                </div>
                            </div>

                            <div
                                class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2"
                            >
                                <!-- Email -->
                                <div>
                                    <InputLabel
                                        for="email"
                                        value="Correo Electrónico *"
                                    />
                                    <TextInput
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.email"
                                    />
                                </div>

                                <!-- Teléfono -->
                                <div>
                                    <InputLabel
                                        for="phone"
                                        value="Teléfono Consultorio"
                                    />
                                    <TextInput
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.phone"
                                    />
                                </div>
                            </div>

                            <!-- Dirección -->
                            <div class="mt-6">
                                <InputLabel
                                    for="address"
                                    value="Dirección del Consultorio"
                                />
                                <textarea
                                    id="address"
                                    v-model="form.address"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    rows="2"
                                ></textarea>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.address"
                                />
                            </div>
                        </div>

                        <!-- Sección: Información Profesional -->
                        <div class="border-b pb-6">
                            <h3
                                class="mb-4 text-lg font-semibold text-gray-900"
                            >
                                🏥 Información Profesional
                            </h3>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <!-- Especialidad -->
                                <div>
                                    <InputLabel
                                        for="specialty"
                                        :value="`Especialidad${isDoctorRole ? ' *' : ''}`"
                                    />
                                    <select
                                        id="specialty"
                                        v-model="form.specialty"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        :required="isDoctorRole"
                                    >
                                        <option
                                            v-for="spec in specialties"
                                            :key="spec"
                                            :value="spec"
                                        >
                                            {{ spec }}
                                        </option>
                                    </select>
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.specialty"
                                    />
                                </div>

                                <!-- Matrícula (M.N.) -->
                                <div>
                                    <InputLabel
                                        for="license_number"
                                        :value="`Matrícula Nacional (M.N.)${isDoctorRole ? ' *' : ''}`"
                                    />
                                    <TextInput
                                        id="license_number"
                                        v-model="form.license_number"
                                        type="text"
                                        class="mt-1 block w-full"
                                        :required="isDoctorRole"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.license_number"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">
                                        Ejemplo: Matrícula Provincial
                                    </p>
                                </div>

                                <!-- Matrícula Profesional (M.P.) -->
                                <div>
                                    <InputLabel
                                        for="professional_id"
                                        value="Matrícula Profesional (M.P.)"
                                    />
                                    <TextInput
                                        id="professional_id"
                                        v-model="form.professional_id"
                                        type="text"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.professional_id"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">
                                        Número de colegio profesional
                                    </p>
                                </div>

                                <!-- Rol -->
                                <div>
                                    <InputLabel
                                        for="role"
                                        value="Rol en el Sistema *"
                                    />
                                    <select
                                        id="role"
                                        v-model="form.role"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        required
                                    >
                                        <option value="doctor">
                                            👨‍⚕️ Doctor
                                        </option>
                                        <option value="admin">
                                            🔐 Administrador
                                        </option>
                                        <option value="operating_room_manager">
                                            🏥 Encargado de Quirófano
                                        </option>
                                        <option value="pharmacy">
                                            💊 Farmacia
                                        </option>
                                        <option value="secretary">
                                            🗂️ Secretaría
                                        </option>
                                        <option value="nurse">
                                            🩺 Enfermería
                                        </option>
                                        <option value="emergency">
                                            🚑 Guardia / Emergencias
                                        </option>
                                        <option value="accountant">
                                            💼 Contabilidad
                                        </option>
                                        <option value="maintenance">
                                            🔧 Mantenimiento
                                        </option>
                                        <option value="paramedic">
                                            🚐 Paramédico / Ambulancia
                                        </option>
                                    </select>
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.role"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="flex gap-3 pt-4">
                            <PrimaryButton :disabled="form.processing">
                                ✅ Guardar Cambios
                            </PrimaryButton>
                            <Link
                                :href="route('admin.users.index')"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Cancelar
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
