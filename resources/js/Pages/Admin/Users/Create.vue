<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { computed } from "vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

interface Props {
    specialties: string[];
    availableRoles: Array<{
        value: string;
        label: string;
    }>;
    availableModules: Array<{
        id: string;
        label: string;
        icon: string;
        description: string;
    }>;
}

const props = withDefaults(defineProps<Props>(), {});

const form = useForm({
    name: "",
    dni: "",
    email: "",
    password: "",
    password_confirmation: "",
    specialty: "",
    license_number: "",
    professional_id: "",
    phone: "",
    address: "",
    role: "doctor",
    allowed_modules: [] as string[],
});

const isDoctorRole = computed(() => form.role === "doctor");

const submit = () => {
    form.post(route("admin.users.store"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Crear Usuario" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Crear Nuevo Usuario
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
                            Registro de Nuevo Usuario
                        </h1>
                        <p class="mt-2 text-sm text-gray-600">
                            Completa los datos para crear un nuevo perfil del
                            sistema
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
                                        placeholder="Dr. Juan García López"
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
                                        placeholder="XX.XXX.XXX"
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
                                        placeholder="doctor@clinica.com"
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
                                        placeholder="+54-11-XXXX-XXXX"
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
                                    placeholder="Av. Santa Fe 1234, CABA"
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
                                        <option value="">
                                            -- Seleccionar especialidad --
                                        </option>
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
                                        placeholder="123456"
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
                                        placeholder="654321"
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
                                        <option
                                            v-for="roleOption in availableRoles"
                                            :key="roleOption.value"
                                            :value="roleOption.value"
                                        >
                                            {{ roleOption.label }}
                                        </option>
                                    </select>
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.role"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Sección: Módulos Permitidos -->
                        <div v-if="form.role !== 'admin'" class="border-b pb-6">
                            <h3
                                class="mb-4 text-lg font-semibold text-gray-900"
                            >
                                🔑 Permisos de Módulos
                            </h3>
                            <p class="mb-4 text-sm text-gray-600">
                                Selecciona los módulos a los que este usuario
                                tendrá acceso. Deja vacío para permitir acceso a
                                todos los módulos por defecto.
                            </p>

                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <label
                                    v-for="module in availableModules"
                                    :key="module.id"
                                    class="flex items-start gap-3 rounded-lg border border-gray-200 p-4 hover:border-primary-300 hover:bg-primary-50 cursor-pointer transition-all"
                                >
                                    <input
                                        type="checkbox"
                                        :value="module.id"
                                        v-model="form.allowed_modules"
                                        class="mt-1 h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                    />
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">
                                            <span class="mr-2">{{
                                                module.icon
                                            }}</span>
                                            {{ module.label }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ module.description }}
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <InputError
                                class="mt-2"
                                :message="form.errors.allowed_modules"
                            />
                        </div>

                        <!-- Sección: Credenciales de Acceso -->
                        <div class="border-b pb-6">
                            <h3
                                class="mb-4 text-lg font-semibold text-gray-900"
                            >
                                🔒 Credenciales de Acceso
                            </h3>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <!-- Password -->
                                <div>
                                    <InputLabel
                                        for="password"
                                        value="Contraseña *"
                                    />
                                    <TextInput
                                        id="password"
                                        v-model="form.password"
                                        type="password"
                                        class="mt-1 block w-full"
                                        placeholder="••••••••"
                                        required
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.password"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">
                                        Mínimo 8 caracteres
                                    </p>
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <InputLabel
                                        for="password_confirmation"
                                        value="Confirmar Contraseña *"
                                    />
                                    <TextInput
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        type="password"
                                        class="mt-1 block w-full"
                                        placeholder="••••••••"
                                        required
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="
                                            form.errors.password_confirmation
                                        "
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="flex gap-3 pt-4">
                            <PrimaryButton :disabled="form.processing">
                                ✅ Crear Usuario
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
