<script setup lang="ts">
import { computed, ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
    cuil: string | null;
    birth_date: string;
    phone: string | null;
    email: string | null;
    emergency_contact_name: string | null;
    emergency_contact_phone: string | null;
}

interface HealthInsuranceOption {
    id: number;
    name: string;
    code?: string;
}

interface ObraSocial {
    rnos: string;
    nombre: string;
    sigla?: string;
    provincia?: string;
    localidad?: string;
    telefonos?: string[];
    emails?: string[];
    domicilio?: string;
}

interface Props {
    patient: Patient;
    healthInsurances: HealthInsuranceOption[];
    primaryInsuranceId: number | null;
    primaryMemberNumber: string | null;
}

const props = defineProps<Props>();

const form = useForm({
    first_name: props.patient.first_name,
    last_name: props.patient.last_name,
    dni: props.patient.dni,
    cuil: props.patient.cuil || "",
    birth_date: props.patient.birth_date,
    phone: props.patient.phone || "",
    email: props.patient.email || "",
    emergency_contact_name: props.patient.emergency_contact_name || "",
    emergency_contact_phone: props.patient.emergency_contact_phone || "",
    health_insurance_id: props.primaryInsuranceId || "",
    member_number: props.primaryMemberNumber || "",
    new_health_insurance_name: "",
    new_health_insurance_code: "",
    obra_social_data: null as ObraSocial | null,
});

const searchQuery = ref("");
const searchResults = ref<ObraSocial[]>([]);
const isSearching = ref(false);
const showSearchResults = ref(false);
const selectedObraData = ref<ObraSocial | null>(null);

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

const searchObrasSociales = async () => {
    if (searchQuery.value.length < 2) {
        showSearchResults.value = false;
        return;
    }

    isSearching.value = true;
    showSearchResults.value = true;

    try {
        const response = await fetch(
            `/api/obras-sociales/search?q=${encodeURIComponent(
                searchQuery.value,
            )}&limit=20`,
        );
        const data = await response.json();
        searchResults.value = data.data || [];
    } catch (error) {
        console.error("Error searching obras sociales:", error);
        searchResults.value = [];
    } finally {
        isSearching.value = false;
    }
};

const selectObraSocial = (obra: ObraSocial) => {
    selectedObraData.value = obra;
    form.obra_social_data = obra;
    searchQuery.value = obra.nombre;
    showSearchResults.value = false;
    form.health_insurance_id = ""; // Limpiar selección previa
};

const clearSelection = () => {
    selectedObraData.value = null;
    form.obra_social_data = null;
    searchQuery.value = "";
    searchResults.value = [];
    showSearchResults.value = false;
};

const submit = () => {
    form.patch(route("patients.update", props.patient.id), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head :title="`Editar - ${patient.first_name} ${patient.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar paciente: {{ patient.first_name }}
                {{ patient.last_name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- First name -->
                        <div>
                            <InputLabel for="first_name" value="Nombre *" />
                            <TextInput
                                id="first_name"
                                v-model="form.first_name"
                                required
                                autofocus
                                autocomplete="given-name"
                                class="mt-1 block w-full"
                                :class="{
                                    'border-danger-500': form.errors.first_name,
                                }"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.first_name"
                            />
                        </div>

                        <!-- Last name -->
                        <div>
                            <InputLabel for="last_name" value="Apellido *" />
                            <TextInput
                                id="last_name"
                                v-model="form.last_name"
                                required
                                autocomplete="family-name"
                                class="mt-1 block w-full"
                                :class="{
                                    'border-danger-500': form.errors.last_name,
                                }"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.last_name"
                            />
                        </div>

                        <!-- DNI -->
                        <div>
                            <InputLabel
                                for="dni"
                                value="DNI (Documento Nacional de Identidad) *"
                            />
                            <TextInput
                                id="dni"
                                v-model="form.dni"
                                required
                                placeholder="Ej: 12345678"
                                class="mt-1 block w-full font-mono"
                                :class="{
                                    'border-danger-500': form.errors.dni,
                                }"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.dni"
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                Debe contener al menos 6 dígitos. Será único en
                                el sistema.
                            </p>
                        </div>

                        <!-- CUIL -->
                        <div>
                            <InputLabel for="cuil" value="CUIL *" />
                            <TextInput
                                id="cuil"
                                v-model="form.cuil"
                                required
                                placeholder="Ej: 20-12345678-3"
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

                        <!-- Birth date -->
                        <div>
                            <InputLabel
                                for="birth_date"
                                value="Fecha de nacimiento *"
                            />
                            <input
                                id="birth_date"
                                v-model="form.birth_date"
                                type="date"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                :class="{
                                    'border-danger-500': form.errors.birth_date,
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
                                type="tel"
                                placeholder="+54 9 11 1234-5678"
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
                                value="Correo electrónico (Opcional)"
                            />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="paciente@correo.com"
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

                        <!-- Emergency Contact Section -->
                        <div
                            class="rounded-lg border border-amber-200 bg-amber-50 p-4"
                        >
                            <h3 class="mb-4 font-semibold text-gray-900">
                                Contacto de Emergencia
                            </h3>

                            <!-- Emergency Contact Name -->
                            <div>
                                <InputLabel
                                    for="emergency_contact_name"
                                    value="Nombre de la Persona de Contacto (Opcional)"
                                />
                                <TextInput
                                    id="emergency_contact_name"
                                    v-model="form.emergency_contact_name"
                                    placeholder="Ej: Juan Pérez"
                                    class="mt-1 block w-full"
                                    :class="{
                                        'border-danger-500':
                                            form.errors.emergency_contact_name,
                                    }"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="
                                        form.errors.emergency_contact_name
                                    "
                                />
                            </div>

                            <!-- Emergency Contact Phone -->
                            <div class="mt-4">
                                <InputLabel
                                    for="emergency_contact_phone"
                                    value="Teléfono de Contacto de Emergencia (Opcional)"
                                />
                                <TextInput
                                    id="emergency_contact_phone"
                                    v-model="form.emergency_contact_phone"
                                    type="tel"
                                    placeholder="+54 9 11 1234-5678"
                                    class="mt-1 block w-full"
                                    :class="{
                                        'border-danger-500':
                                            form.errors.emergency_contact_phone,
                                    }"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="
                                        form.errors.emergency_contact_phone
                                    "
                                />
                            </div>
                        </div>

                        <!-- Obra social -->
                        <div>
                            <InputLabel
                                value="Obra Social (Cobertura Médica)"
                            />

                            <!-- Search field -->
                            <div class="mt-1 relative">
                                <input
                                    v-model="searchQuery"
                                    @input="searchObrasSociales"
                                    type="text"
                                    placeholder="Buscar obra social por nombre, sigla, provincia..."
                                    class="block w-full rounded-md border border-gray-300 px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />

                                <!-- Search results dropdown -->
                                <div
                                    v-if="
                                        showSearchResults &&
                                        (isSearching ||
                                            searchResults.length > 0)
                                    "
                                    class="absolute z-10 mt-1 w-full rounded-md border border-gray-300 bg-white shadow-lg"
                                >
                                    <div
                                        v-if="isSearching"
                                        class="px-4 py-3 text-center text-sm text-gray-500"
                                    >
                                        Buscando...
                                    </div>
                                    <div
                                        v-else-if="searchResults.length === 0"
                                        class="px-4 py-3 text-center text-sm text-gray-500"
                                    >
                                        No se encontraron resultados
                                    </div>
                                    <div
                                        v-else
                                        class="max-h-64 overflow-y-auto"
                                    >
                                        <button
                                            v-for="obra in searchResults"
                                            :key="obra.rnos"
                                            type="button"
                                            @click="selectObraSocial(obra)"
                                            class="block w-full border-b border-gray-200 px-4 py-3 text-left hover:bg-gray-50 transition-colors last:border-b-0"
                                        >
                                            <div
                                                class="font-semibold text-gray-900"
                                            >
                                                {{ obra.nombre }}
                                            </div>
                                            <div
                                                class="mt-1 text-xs text-gray-600"
                                            >
                                                <span
                                                    v-if="obra.sigla"
                                                    class="mr-3"
                                                >
                                                    <strong>Sigla:</strong>
                                                    {{ obra.sigla }}
                                                </span>
                                                <span
                                                    v-if="obra.provincia"
                                                    class="mr-3"
                                                >
                                                    <strong>Prov:</strong>
                                                    {{ obra.provincia }}
                                                </span>
                                                <span class="font-mono">{{
                                                    obra.rnos
                                                }}</span>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Selected obra social info -->
                            <div
                                v-if="selectedObraData"
                                class="mt-3 rounded-lg border border-primary-200 bg-primary-50 p-4"
                            >
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="font-semibold text-gray-900">
                                            {{ selectedObraData.nombre }}
                                        </p>
                                        <div
                                            class="mt-2 grid grid-cols-2 gap-2 text-sm text-gray-600"
                                        >
                                            <div>
                                                <span class="font-semibold"
                                                    >RNOS:</span
                                                >
                                                {{ selectedObraData.rnos }}
                                            </div>
                                            <div v-if="selectedObraData.sigla">
                                                <span class="font-semibold"
                                                    >Sigla:</span
                                                >
                                                {{ selectedObraData.sigla }}
                                            </div>
                                            <div
                                                v-if="
                                                    selectedObraData.provincia
                                                "
                                            >
                                                <span class="font-semibold"
                                                    >Provincia:</span
                                                >
                                                {{ selectedObraData.provincia }}
                                            </div>
                                            <div
                                                v-if="
                                                    selectedObraData.localidad
                                                "
                                            >
                                                <span class="font-semibold"
                                                    >Localidad:</span
                                                >
                                                {{ selectedObraData.localidad }}
                                            </div>
                                            <div
                                                v-if="
                                                    selectedObraData.telefonos
                                                        ?.length
                                                "
                                            >
                                                <span class="font-semibold"
                                                    >Tel:</span
                                                >
                                                {{
                                                    selectedObraData
                                                        .telefonos[0]
                                                }}
                                            </div>
                                            <div
                                                v-if="
                                                    selectedObraData.emails
                                                        ?.length
                                                "
                                            >
                                                <span class="font-semibold"
                                                    >Email:</span
                                                >
                                                {{ selectedObraData.emails[0] }}
                                            </div>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="clearSelection"
                                        class="text-primary-600 hover:text-primary-900 transition-colors"
                                        title="Limpiar selección"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.03a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Alternative: Existing health insurances -->
                            <div v-if="!selectedObraData" class="mt-3">
                                <p class="mb-2 text-xs text-gray-600">
                                    O seleccionar de obras sociales registradas:
                                </p>
                                <select
                                    v-model="form.health_insurance_id"
                                    class="block w-full rounded-md border border-gray-300 px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                    <option value="">
                                        -- Sin obra social --
                                    </option>
                                    <option
                                        v-for="insurance in props.healthInsurances"
                                        :key="insurance.id"
                                        :value="insurance.id"
                                    >
                                        {{ insurance.name }}
                                    </option>
                                </select>
                            </div>

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

                            <div class="mt-3">
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
                                        !selectedObraData &&
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

                        <!-- Submit buttons -->
                        <div class="flex gap-3 pt-4">
                            <PrimaryButton :disabled="form.processing">
                                Guardar cambios
                            </PrimaryButton>
                            <button
                                type="button"
                                @click="$inertia.visit(route('patients.index'))"
                                class="rounded-lg border border-gray-300 bg-white px-6 py-2 font-semibold text-gray-700 hover:bg-gray-50"
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
