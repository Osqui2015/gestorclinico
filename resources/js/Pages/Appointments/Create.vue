<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AppointmentCalendar from "@/Components/AppointmentCalendar.vue";

interface Doctor {
    id: number;
    name: string;
}

interface Props {
    patients: Record<number, string>;
    doctors: Record<number, string>;
    currentDoctor: Doctor | null;
    isDoctor: boolean;
    preSelectedPatient: string | null;
    preSelectedInsurance: number | null;
}

const props = defineProps<Props>();

const patientSearch = ref("");
const obraSocialSearch = ref("");
const obrasSociales = ref<any[]>([]);
const loadingObrasSociales = ref(false);
const selectedObraSocialData = ref<any>(null);
const loadingPatientInsurance = ref(false);
const savedPatientId = ref("");

// Filter patients by DNI (digits) or name
const filteredPatients = computed(() => {
    if (!patientSearch.value) return props.patients;

    const q = patientSearch.value.toLowerCase().trim();
    const isDigits = /^\d+$/.test(q);

    return Object.fromEntries(
        Object.entries(props.patients).filter(([_, patientLabel]) => {
            const label = patientLabel.toLowerCase();
            if (isDigits) {
                return label.includes(q); // DNI is included in label as `(DNI: 1234...)`
            }
            return label.includes(q);
        }),
    );
});

const form = useForm({
    doctor_id:
        props.isDoctor && props.currentDoctor
            ? String(props.currentDoctor.id)
            : "",
    patient_id: props.preSelectedPatient || "",
    health_insurance_id: props.preSelectedInsurance || "",
    coseguro: "",
    scheduled_at: "",
    status: "pending",
    reason: "",
    notes: "",
});

// Search Obras Sociales
const searchObrasSociales = async () => {
    if (obraSocialSearch.value.length < 2) {
        obrasSociales.value = [];
        return;
    }

    loadingObrasSociales.value = true;
    try {
        const response = await fetch(
            `/api/obras-sociales/search?q=${encodeURIComponent(
                obraSocialSearch.value,
            )}&limit=20`,
        );
        const result = await response.json();
        obrasSociales.value = result.data || [];
    } catch (error) {
        console.error("Error al buscar obras sociales:", error);
        obrasSociales.value = [];
    } finally {
        loadingObrasSociales.value = false;
    }
};

// Select obra social
const selectObraSocial = (obraSocial: any) => {
    form.health_insurance_id = obraSocial.id;
    selectedObraSocialData.value = obraSocial;
    obraSocialSearch.value = obraSocial.name;
    obrasSociales.value = [];
};

// Clear obra social selection
const clearObraSocialSelection = () => {
    form.health_insurance_id = "";
    selectedObraSocialData.value = null;
    obraSocialSearch.value = "";
    obrasSociales.value = [];
};

// Load patient's primary insurance and member number
const loadPatientInsurance = async (patientId: string) => {
    if (!patientId) {
        clearObraSocialSelection();
        return;
    }

    loadingPatientInsurance.value = true;
    try {
        const response = await fetch(
            `/api/appointments/patient-insurance?patient_id=${patientId}`,
        );
        const result = await response.json();

        if (result.insurance) {
            // Set the insurance data
            form.health_insurance_id = result.insurance.id;
            selectedObraSocialData.value = result.insurance;
            obraSocialSearch.value = result.insurance.name;
            obrasSociales.value = [];

            // Set member number in coseguro if available (optional - you can store member number separately if needed)
            // For now, we'll just populate the insurance selection automatically
        } else {
            clearObraSocialSelection();
        }
    } catch (error) {
        console.error("Error al cargar el seguro del paciente:", error);
        clearObraSocialSelection();
    } finally {
        loadingPatientInsurance.value = false;
    }
};

// If pre-selected patient from query param, update form
watch(
    () => props.preSelectedPatient,
    (newValue) => {
        if (newValue) {
            form.patient_id = newValue;
        }
    },
);

// If pre-selected insurance from patient, update form
watch(
    () => props.preSelectedInsurance,
    (newValue) => {
        if (newValue) {
            form.health_insurance_id = String(newValue);
        }
    },
);

// Watch for patient_id changes and load their insurance
watch(
    () => form.patient_id,
    (newValue) => {
        if (newValue) {
            loadPatientInsurance(newValue);
            savedPatientId.value = newValue;
        }
    },
);

// Watch for scheduled_at changes and preserve patient_id if it got cleared
watch(
    () => form.scheduled_at,
    () => {
        // If patient_id was cleared but we have a saved value, restore it
        if (!form.patient_id && savedPatientId.value) {
            form.patient_id = savedPatientId.value;
        }
    },
);

const submit = () => {
    console.log("Submitting form with data:", form.data());
    form.post(route("appointments.store"), {
        onSuccess: () => {
            console.log("Appointment created successfully");
        },
        onError: (errors) => {
            console.error("Validation errors:", errors);
        },
        onFinish: () => {
            console.log("Form submission finished");
        },
    });
};
</script>

<template>
    <Head title="Crear Cita" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                📅 Agendar nueva cita
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Doctor -->
                        <div>
                            <InputLabel for="doctor_id" value="Médico *" />
                            <div
                                v-if="isDoctor"
                                class="mt-1 flex items-center rounded-lg border border-gray-300 bg-gray-50 px-4 py-2"
                            >
                                <span class="text-gray-900">
                                    👨‍⚕️ {{ currentDoctor?.name }}
                                </span>
                                <p class="ml-auto text-xs text-gray-500">
                                    (Tu perfil)
                                </p>
                            </div>
                            <select
                                v-else
                                id="doctor_id"
                                v-model="form.doctor_id"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                :class="{
                                    'border-danger-500': form.errors.doctor_id,
                                }"
                            >
                                <option value="">Selecciona un médico</option>
                                <option
                                    v-for="(name, id) in doctors"
                                    :key="id"
                                    :value="id"
                                >
                                    {{ name }}
                                </option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="form.errors.doctor_id"
                            />
                        </div>

                        <!-- Patient Search & Selection -->
                        <div>
                            <InputLabel
                                for="patient_search"
                                value="Buscar Paciente por DNI"
                            />
                            <TextInput
                                id="patient_search"
                                v-model="patientSearch"
                                placeholder="Ej: 12345678 o Buscar por nombre"
                                class="mt-1 block w-full"
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                💡 Escribe el DNI o nombre del paciente para
                                filtrar
                            </p>
                        </div>

                        <!-- Patient Selection -->
                        <div>
                            <InputLabel for="patient_id" value="Paciente *" />
                            <select
                                id="patient_id"
                                v-model="form.patient_id"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                :class="{
                                    'border-danger-500': form.errors.patient_id,
                                }"
                            >
                                <option value="">Selecciona un paciente</option>
                                <option
                                    v-for="(name, id) in filteredPatients"
                                    :key="id"
                                    :value="id"
                                >
                                    {{ name }}
                                </option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="form.errors.patient_id"
                            />
                            <p
                                v-if="
                                    Object.keys(filteredPatients).length === 0
                                "
                                class="mt-2 text-sm text-warning-600"
                            >
                                ⚠️ No se encontraron pacientes con esa búsqueda
                            </p>
                        </div>
                        <!-- Obra Social Search & Selection -->
                        <div
                            class="rounded-lg border border-primary-200 bg-primary-50 p-4"
                        >
                            <h3
                                class="mb-3 text-sm font-semibold text-primary-900"
                            >
                                💳 Información de Cobertura
                                <span
                                    v-if="loadingPatientInsurance"
                                    class="ml-2 inline-block animate-spin"
                                    >⚙️</span
                                >
                            </h3>

                            <!-- Obra Social Search -->
                            <div class="mb-3">
                                <InputLabel
                                    for="obra_social_search"
                                    value="Buscar Obra Social"
                                />
                                <div class="relative mt-1">
                                    <TextInput
                                        id="obra_social_search"
                                        v-model="obraSocialSearch"
                                        @input="searchObrasSociales"
                                        placeholder="Ej: OSDE, Swiss Medical, IOMA..."
                                        class="block w-full"
                                        :disabled="!!selectedObraSocialData"
                                    />
                                    <button
                                        v-if="selectedObraSocialData"
                                        type="button"
                                        @click="clearObraSocialSelection"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-danger-500 hover:text-danger-700"
                                    >
                                        ✕
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-primary-700">
                                    💡 Escribe el nombre de la obra social
                                </p>

                                <!-- Search Results -->
                                <div
                                    v-if="obrasSociales.length > 0"
                                    class="mt-2 max-h-48 overflow-y-auto rounded-lg border border-primary-300 bg-white shadow-lg"
                                >
                                    <button
                                        v-for="os in obrasSociales"
                                        :key="os.id"
                                        type="button"
                                        @click="selectObraSocial(os)"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-primary-50"
                                    >
                                        <div class="font-medium text-gray-900">
                                            {{ os.name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            RNOS: {{ os.code }}
                                        </div>
                                    </button>
                                </div>

                                <!-- Selected Obra Social -->
                                <div
                                    v-if="selectedObraSocialData"
                                    class="mt-2 rounded-lg border border-success-300 bg-success-50 p-3"
                                >
                                    <div
                                        class="flex items-start justify-between"
                                    >
                                        <div>
                                            <p
                                                class="font-semibold text-success-900"
                                            >
                                                ✓
                                                {{
                                                    selectedObraSocialData.name
                                                }}
                                            </p>
                                            <p class="text-xs text-success-700">
                                                RNOS:
                                                {{
                                                    selectedObraSocialData.code
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Coseguro Amount -->
                            <div>
                                <InputLabel
                                    for="coseguro"
                                    value="Coseguro (Monto a cobrar) $"
                                />
                                <TextInput
                                    id="coseguro"
                                    v-model="form.coseguro"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="Ej: 5000.00"
                                    class="mt-1 block w-full"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.coseguro"
                                />
                                <p class="mt-1 text-xs text-primary-700">
                                    💰 Monto que el paciente debe abonar por la
                                    consulta
                                </p>
                            </div>
                        </div>
                        <!-- Date and Time -->
                        <div>
                            <InputLabel
                                for="scheduled_at"
                                value="Fecha y Hora *"
                            />
                            <AppointmentCalendar
                                v-if="form.doctor_id"
                                :doctor-id="form.doctor_id"
                                :selected-date-time="form.scheduled_at"
                                @update:selectedDateTime="
                                    (value: string) =>
                                        (form.scheduled_at = value)
                                "
                            />
                            <div
                                v-else
                                class="mt-2 rounded-lg border border-warning-400 bg-warning-50 p-4 text-sm text-warning-700"
                            >
                                ⚠️ Por favor, selecciona un médico primero para
                                ver la disponibilidad.
                            </div>
                            <InputError
                                class="mt-2"
                                :message="form.errors.scheduled_at"
                            />
                        </div>

                        <!-- Reason -->
                        <div>
                            <InputLabel
                                for="reason"
                                value="Motivo de la consulta (Opcional)"
                            />
                            <TextInput
                                id="reason"
                                v-model="form.reason"
                                placeholder="Ej: Dolor de cabeza, revisión general, etc."
                                class="mt-1 block w-full"
                                :class="{
                                    'border-danger-500': form.errors.reason,
                                }"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.reason"
                            />
                        </div>

                        <!-- Notes -->
                        <div>
                            <InputLabel for="notes" value="Notas (Opcional)" />
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="4"
                                placeholder="Notas adicionales sobre la cita..."
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                :class="{
                                    'border-danger-500': form.errors.notes,
                                }"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.notes"
                            />
                        </div>

                        <!-- Submit buttons -->
                        <div class="flex gap-3 pt-4">
                            <PrimaryButton :disabled="form.processing">
                                ✅ Agendar cita
                            </PrimaryButton>
                            <button
                                type="button"
                                @click="
                                    $inertia.visit(route('appointments.index'))
                                "
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
