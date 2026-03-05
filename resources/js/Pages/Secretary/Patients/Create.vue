<script setup lang="ts">
import { computed } from "vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

interface HealthInsuranceOption {
    id: number;
    name: string;
}

interface Props {
    healthInsurances: HealthInsuranceOption[];
}

const props = defineProps<Props>();

const form = useForm({
    first_name: "",
    last_name: "",
    dni: "",
    cuil: "",
    birth_date: "",
    phone: "",
    email: "",
    health_insurance_id: "",
    member_number: "",
    new_health_insurance_name: "",
    new_health_insurance_code: "",
    medical_history: "",
});

const age = computed(() => {
    if (!form.birth_date) return "";
    const today = new Date();
    const birth = new Date(form.birth_date);
    let years = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    if (
        monthDiff < 0 ||
        (monthDiff === 0 && today.getDate() < birth.getDate())
    ) {
        years -= 1;
    }
    return years >= 0 ? String(years) : "";
});

const submit = () => {
    form.post(route("secretary.patients.store"), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Crear Paciente" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                ➕ Registrar Nuevo Paciente
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- First Name -->
                            <div>
                                <InputLabel for="first_name" value="Nombre *" />
                                <TextInput
                                    id="first_name"
                                    v-model="form.first_name"
                                    required
                                    class="mt-1 block w-full"
                                    :class="{
                                        'border-danger-500':
                                            form.errors.first_name,
                                    }"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.first_name"
                                />
                            </div>

                            <!-- Last Name -->
                            <div>
                                <InputLabel
                                    for="last_name"
                                    value="Apellido *"
                                />
                                <TextInput
                                    id="last_name"
                                    v-model="form.last_name"
                                    required
                                    class="mt-1 block w-full"
                                    :class="{
                                        'border-danger-500':
                                            form.errors.last_name,
                                    }"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.last_name"
                                />
                                <details
                                    class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3"
                                >
                                    <summary
                                        class="cursor-pointer text-sm font-semibold text-primary-700 hover:text-primary-900"
                                    >
                                        + Agregar obra social que no esté en el
                                        listado
                                    </summary>

                                    <div
                                        class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2"
                                    >
                                        <div>
                                            <InputLabel
                                                for="new_health_insurance_name"
                                                value="Nombre nueva obra social"
                                            />
                                            <TextInput
                                                id="new_health_insurance_name"
                                                v-model="
                                                    form.new_health_insurance_name
                                                "
                                                placeholder="Ej: Cobertura Salud XYZ"
                                                class="mt-1 block w-full"
                                                :class="{
                                                    'border-danger-500':
                                                        form.errors
                                                            .new_health_insurance_name,
                                                }"
                                            />
                                            <InputError
                                                class="mt-2"
                                                :message="
                                                    form.errors
                                                        .new_health_insurance_name
                                                "
                                            />
                                        </div>
                                        <div>
                                            <InputLabel
                                                for="new_health_insurance_code"
                                                value="Código/RNOS (Opcional)"
                                            />
                                            <TextInput
                                                id="new_health_insurance_code"
                                                v-model="
                                                    form.new_health_insurance_code
                                                "
                                                placeholder="Ej: 400800"
                                                class="mt-1 block w-full"
                                                :class="{
                                                    'border-danger-500':
                                                        form.errors
                                                            .new_health_insurance_code,
                                                }"
                                            />
                                            <InputError
                                                class="mt-2"
                                                :message="
                                                    form.errors
                                                        .new_health_insurance_code
                                                "
                                            />
                                        </div>
                                    </div>
                                </details>
                            </div>

                            <!-- DNI -->
                            <div>
                                <InputLabel for="dni" value="DNI *" />
                                <TextInput
                                    id="dni"
                                    v-model="form.dni"
                                    placeholder="Ej: 12345678"
                                    required
                                    class="mt-1 block w-full"
                                    :class="{
                                        'border-danger-500': form.errors.dni,
                                    }"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.dni"
                                />
                            </div>

                            <!-- CUIL -->
                            <div>
                                <InputLabel for="cuil" value="CUIL *" />
                                <TextInput
                                    id="cuil"
                                    v-model="form.cuil"
                                    placeholder="Ej: 20-12345678-3"
                                    required
                                    class="mt-1 block w-full font-mono"
                                    :class="{
                                        'border-danger-500': form.errors.cuil,
                                    }"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.cuil"
                                />
                            </div>

                            <!-- Birth Date -->
                            <div>
                                <InputLabel
                                    for="birth_date"
                                    value="Fecha de Nacimiento *"
                                />
                                <input
                                    id="birth_date"
                                    v-model="form.birth_date"
                                    type="date"
                                    required
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    :class="{
                                        'border-danger-500':
                                            form.errors.birth_date,
                                    }"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.birth_date"
                                />
                            </div>

                            <!-- Age -->
                            <div>
                                <InputLabel for="age" value="Edad" />
                                <input
                                    id="age"
                                    :value="age"
                                    type="text"
                                    readonly
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-900 shadow-sm"
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    Se calcula automaticamente desde la fecha de
                                    nacimiento.
                                </p>
                            </div>

                            <!-- Phone -->
                            <div>
                                <InputLabel
                                    for="phone"
                                    value="Teléfono (Opcional)"
                                />
                                <TextInput
                                    id="phone"
                                    v-model="form.phone"
                                    placeholder="Ej: 123456789"
                                    class="mt-1 block w-full"
                                    :class="{
                                        'border-danger-500': form.errors.phone,
                                    }"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.phone"
                                />
                            </div>

                            <!-- Email -->
                            <div>
                                <InputLabel
                                    for="email"
                                    value="Email (Opcional)"
                                />
                                <TextInput
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="Ej: paciente@email.com"
                                    class="mt-1 block w-full"
                                    :class="{
                                        'border-danger-500': form.errors.email,
                                    }"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.email"
                                />
                            </div>

                            <!-- Obra social -->
                            <div>
                                <InputLabel
                                    for="health_insurance_id"
                                    value="Obra Social"
                                />
                                <select
                                    id="health_insurance_id"
                                    v-model="form.health_insurance_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
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
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.health_insurance_id"
                                />

                                <details
                                    class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3"
                                >
                                    <summary
                                        class="cursor-pointer text-sm font-semibold text-primary-700 hover:text-primary-900"
                                    >
                                        + Agregar obra social que no esté en el
                                        listado
                                    </summary>
                                    <div
                                        class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2"
                                    >
                                        <div>
                                            <InputLabel
                                                for="new_health_insurance_name"
                                                value="Nombre nueva obra social"
                                            />
                                            <TextInput
                                                id="new_health_insurance_name"
                                                v-model="
                                                    form.new_health_insurance_name
                                                "
                                                placeholder="Ej: Cobertura Salud XYZ"
                                                class="mt-1 block w-full"
                                                :class="{
                                                    'border-danger-500':
                                                        form.errors
                                                            .new_health_insurance_name,
                                                }"
                                            />
                                            <InputError
                                                class="mt-2"
                                                :message="
                                                    form.errors
                                                        .new_health_insurance_name
                                                "
                                            />
                                        </div>
                                        <div>
                                            <InputLabel
                                                for="new_health_insurance_code"
                                                value="Código/RNOS (Opcional)"
                                            />
                                            <TextInput
                                                id="new_health_insurance_code"
                                                v-model="
                                                    form.new_health_insurance_code
                                                "
                                                placeholder="Ej: 400800"
                                                class="mt-1 block w-full"
                                                :class="{
                                                    'border-danger-500':
                                                        form.errors
                                                            .new_health_insurance_code,
                                                }"
                                            />
                                            <InputError
                                                class="mt-2"
                                                :message="
                                                    form.errors
                                                        .new_health_insurance_code
                                                "
                                            />
                                        </div>
                                    </div>
                                </details>
                            </div>

                            <!-- Número de afiliado -->
                            <div>
                                <InputLabel
                                    for="member_number"
                                    value="Número de afiliado (Opcional)"
                                />
                                <TextInput
                                    id="member_number"
                                    v-model="form.member_number"
                                    placeholder="Ej: 1234567890"
                                    class="mt-1 block w-full"
                                    :disabled="
                                        !form.health_insurance_id &&
                                        !form.new_health_insurance_name
                                    "
                                    :class="{
                                        'border-danger-500':
                                            form.errors.member_number,
                                    }"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.member_number"
                                />
                            </div>
                        </div>

                        <!-- Medical History -->
                        <div>
                            <InputLabel
                                for="medical_history"
                                value="Historial Médico (Opcional)"
                            />
                            <textarea
                                id="medical_history"
                                v-model="form.medical_history"
                                rows="4"
                                placeholder="Información relevante del historial médico del paciente..."
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                :class="{
                                    'border-danger-500':
                                        form.errors.medical_history,
                                }"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.medical_history"
                            />
                        </div>

                        <!-- Submit buttons -->
                        <div class="flex gap-3 pt-4">
                            <PrimaryButton :disabled="form.processing">
                                ✅ Registrar Paciente
                            </PrimaryButton>
                            <Link
                                :href="route('secretary.turns.create')"
                                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-6 py-2 font-semibold text-gray-700 hover:bg-gray-50"
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
