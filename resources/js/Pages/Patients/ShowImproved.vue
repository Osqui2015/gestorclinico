<script setup lang="ts">
import { format } from "date-fns";
import { es } from "date-fns/locale";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { ref, computed } from "vue";

interface User {
    id: number;
    name: string;
    specialty?: string;
}

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
    birth_date: string;
    phone: string | null;
    email: string | null;
    created_at: string;
}

interface MedicalRecord {
    id: number;
    reason: string | null;
    diagnosis: string | null;
    treatment: string | null;
    private_notes: string | null;
    is_first_consultation?: boolean;
    health_background?: string | null;
    doctor: User;
    created_at: string;
    updated_at: string;
}

interface PrimaryInsurance {
    id: number;
    name: string;
    member_number: string | null;
}

interface PrescriptionDraft {
    diagnosis: string;
    medications: Array<{
        name: string;
        dosage: string;
        frequency: string;
        duration: string;
    }>;
    instructions: Array<{
        description: string;
    }>;
    notes: string;
}

interface Props {
    patient: Patient;
    medicalRecords: MedicalRecord[];
    primaryInsurance: PrimaryInsurance | null;
}

const props = defineProps<Props>();
const page = usePage();

const showForm = ref(false);
const showAllRecords = ref(false);
const showPrescriptionModal = ref(false);
const editingPrescriptionIndex = ref<number | null>(null);
const prescriptionErrors = ref<string[]>([]);

const form = useForm({
    reason: "",
    diagnosis: "",
    treatment: "",
    private_notes: "",
    is_first_consultation: false,
    health_background: "",
    prescriptions: [] as PrescriptionDraft[],
});

const prescriptions = ref<PrescriptionDraft[]>([]);

const blankPrescription = (): PrescriptionDraft => ({
    diagnosis: "",
    medications: [
        {
            name: "",
            dosage: "",
            frequency: "",
            duration: "",
        },
    ],
    instructions: [
        {
            description: "",
        },
    ],
    notes: "",
});

const prescriptionForm = ref<PrescriptionDraft>(blankPrescription());

const currentUser = computed(() => page.props.auth?.user);

// Filter records: by default only current doctor's records
const displayedRecords = computed(() => {
    if (showAllRecords.value) {
        return props.medicalRecords;
    }
    // Show only current doctor's records
    return props.medicalRecords.filter(
        (r) => r.doctor.id === currentUser.value?.id,
    );
});

const isFirstConsultationWithDoctor = computed(() => {
    if (!currentUser.value?.id) {
        return false;
    }

    return !props.medicalRecords.some(
        (r) => r.doctor.id === currentUser.value?.id,
    );
});

const formatDate = (date: string) => {
    return format(new Date(date), "dd MMM yyyy", { locale: es });
};

const formatDateTime = (date: string) => {
    return format(new Date(date), "dd MMM yyyy - HH:mm", { locale: es });
};

const getAge = (birthDate: string) => {
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();

    if (
        monthDiff < 0 ||
        (monthDiff === 0 && today.getDate() < birth.getDate())
    ) {
        age--;
    }

    return age;
};

const submitRecord = () => {
    if (isFirstConsultationWithDoctor.value) {
        form.is_first_consultation = true;
    }

    form.prescriptions = prescriptions.value;
    form.post(route("patients.medical-records.store", props.patient.id), {
        onSuccess: () => {
            form.reset();
            form.is_first_consultation = false;
            showForm.value = false;
            prescriptions.value = [];
        },
    });
};

const openPrescriptionModal = (index: number | null = null) => {
    prescriptionErrors.value = [];
    editingPrescriptionIndex.value = index;
    if (index === null) {
        prescriptionForm.value = blankPrescription();
    } else {
        prescriptionForm.value = JSON.parse(
            JSON.stringify(prescriptions.value[index]),
        );
    }
    showPrescriptionModal.value = true;
};

const addMedication = () => {
    prescriptionForm.value.medications.push({
        name: "",
        dosage: "",
        frequency: "",
        duration: "",
    });
};

const removeMedication = (index: number) => {
    prescriptionForm.value.medications.splice(index, 1);
};

const addInstruction = () => {
    prescriptionForm.value.instructions.push({
        description: "",
    });
};

const removeInstruction = (index: number) => {
    prescriptionForm.value.instructions.splice(index, 1);
};

const savePrescription = () => {
    const errors: string[] = [];
    if (!prescriptionForm.value.diagnosis.trim()) {
        errors.push("El diagnostico es obligatorio.");
    }
    if (prescriptionForm.value.medications.length === 0) {
        errors.push("Agrega al menos un medicamento.");
    }
    if (prescriptionForm.value.instructions.length === 0) {
        errors.push("Agrega al menos una indicacion.");
    }
    if (
        prescriptionForm.value.medications.some(
            (med) =>
                !med.name.trim() ||
                !med.dosage.trim() ||
                !med.frequency.trim() ||
                !med.duration.trim(),
        )
    ) {
        errors.push("Completa todos los campos de medicamentos.");
    }
    if (
        prescriptionForm.value.instructions.some(
            (ins) => !ins.description.trim(),
        )
    ) {
        errors.push("Completa todas las indicaciones.");
    }

    if (errors.length > 0) {
        prescriptionErrors.value = errors;
        return;
    }

    if (editingPrescriptionIndex.value === null) {
        prescriptions.value.push(prescriptionForm.value);
    } else {
        prescriptions.value.splice(
            editingPrescriptionIndex.value,
            1,
            prescriptionForm.value,
        );
    }

    showPrescriptionModal.value = false;
};

const removePrescription = (index: number) => {
    prescriptions.value.splice(index, 1);
};

const downloadPrescriptionDraft = async (prescription: PrescriptionDraft) => {
    try {
        // Enviar datos al backend para generar el PDF
        const response = await fetch(route("prescriptions.draft-pdf"), {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN":
                    (
                        document.querySelector(
                            'meta[name="csrf-token"]',
                        ) as HTMLMetaElement
                    )?.content || "",
            },
            body: JSON.stringify({
                patient_id: props.patient.id,
                diagnosis: prescription.diagnosis,
                medications: prescription.medications,
                instructions: prescription.instructions,
                notes: prescription.notes,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            console.error("Error del servidor:", data);
            alert(
                "Error al generar el PDF: " +
                    (data.error || response.statusText),
            );
            return;
        }

        // Abrir el PDF en una nueva pestaña
        window.open(data.url, "_blank");
    } catch (error) {
        console.error("Error:", error);
        alert("Error al generar el PDF de la receta: " + error);
    }
};
</script>

<template>
    <Head :title="`${patient.first_name} ${patient.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Paciente: {{ patient.first_name }} {{ patient.last_name }}
                </h2>
                <Link
                    :href="route('patients.index')"
                    class="inline-flex items-center rounded-md bg-gray-800 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-700"
                >
                    ← Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Patient Information Card -->
                <div class="rounded-lg bg-white shadow-sm">
                    <div
                        class="border-b border-gray-200 bg-gradient-to-r from-primary-50 to-primary-50 p-6"
                    >
                        <h1 class="text-3xl font-bold text-gray-900">
                            👤 {{ patient.first_name }} {{ patient.last_name }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-600">
                            DNI:
                            <span class="font-mono font-semibold">{{
                                patient.dni
                            }}</span>
                        </p>
                    </div>

                    <div class="p-6">
                        <div
                            class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4"
                        >
                            <div class="rounded-lg bg-gray-50 p-4">
                                <label
                                    class="block text-sm font-semibold text-gray-700"
                                >
                                    Edad
                                </label>
                                <p
                                    class="mt-1 text-2xl font-bold text-primary-600"
                                >
                                    {{ getAge(patient.birth_date) }} años
                                </p>
                            </div>

                            <div class="rounded-lg bg-gray-50 p-4">
                                <label
                                    class="block text-sm font-semibold text-gray-700"
                                >
                                    Nacimiento
                                </label>
                                <p class="mt-1 text-gray-900">
                                    {{ formatDate(patient.birth_date) }}
                                </p>
                            </div>

                            <div class="rounded-lg bg-gray-50 p-4">
                                <label
                                    class="block text-sm font-semibold text-gray-700"
                                >
                                    Teléfono
                                </label>
                                <p class="mt-1 text-gray-900">
                                    {{ patient.phone || "—" }}
                                </p>
                            </div>

                            <div class="rounded-lg bg-gray-50 p-4">
                                <label
                                    class="block text-sm font-semibold text-gray-700"
                                >
                                    Email
                                </label>
                                <p class="mt-1 truncate text-gray-900">
                                    {{ patient.email || "—" }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="rounded-lg bg-gray-50 p-4">
                                <label
                                    class="block text-sm font-semibold text-gray-700"
                                >
                                    Obra social
                                </label>
                                <p class="mt-1 text-gray-900">
                                    {{
                                        props.primaryInsurance?.name ||
                                        "Sin obra social"
                                    }}
                                </p>
                            </div>

                            <div class="rounded-lg bg-gray-50 p-4">
                                <label
                                    class="block text-sm font-semibold text-gray-700"
                                >
                                    Número de afiliado
                                </label>
                                <p class="mt-1 font-mono text-gray-900">
                                    {{
                                        props.primaryInsurance?.member_number ||
                                        "—"
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medical History Section -->
                <div class="rounded-lg bg-white shadow-sm">
                    <div
                        class="flex flex-col gap-4 border-b border-gray-200 p-6 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">
                                📋 Historial Médico
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                <span
                                    v-if="!showAllRecords"
                                    class="font-semibold"
                                >
                                    Mostrando: Solo mis registros ({{
                                        displayedRecords.length
                                    }}
                                    de {{ medicalRecords.length }})
                                </span>
                                <span v-else class="font-semibold">
                                    Mostrando: Todo el historial médico ({{
                                        displayedRecords.length
                                    }}
                                    registros)
                                </span>
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Link
                                :href="
                                    route('appointments.create', {
                                        patient_id: patient.id,
                                    })
                                "
                                class="inline-flex items-center rounded-md bg-success-600 px-4 py-2 text-sm font-semibold text-white hover:bg-success-700"
                            >
                                📅 Nueva Cita
                            </Link>
                            <button
                                v-if="!showForm"
                                @click="showForm = true"
                                class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700"
                            >
                                + Agregar Registro
                            </button>
                            <button
                                @click="showAllRecords = !showAllRecords"
                                :class="[
                                    'inline-flex items-center rounded-md px-4 py-2 text-sm font-semibold',
                                    showAllRecords
                                        ? 'bg-primary-600 text-white hover:bg-primary-700'
                                        : 'bg-gray-200 text-gray-800 hover:bg-gray-300   ',
                                ]"
                            >
                                {{
                                    showAllRecords
                                        ? "👁️ Mis registros"
                                        : "👁️‍🗨️ Ver todos"
                                }}
                            </button>
                        </div>
                    </div>

                    <!-- Add Medical Record Form -->
                    <div
                        v-if="showForm"
                        class="border-b border-gray-200 bg-gray-50 p-6"
                    >
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">
                            Agregar Registro Médico - Hoy ({{
                                formatDate(new Date().toISOString())
                            }})
                        </h3>

                        <form @submit.prevent="submitRecord" class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <!-- Reason -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Motivo de Consulta
                                    </label>
                                    <textarea
                                        v-model="form.reason"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        rows="2"
                                        placeholder="Ej: Dolor de cabeza persistente"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.reason"
                                    />
                                </div>

                                <!-- Diagnosis -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Diagnóstico
                                    </label>
                                    <textarea
                                        v-model="form.diagnosis"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        rows="2"
                                        placeholder="Ej: Migraña tensional"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.diagnosis"
                                    />
                                </div>

                                <!-- Treatment -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Tratamiento
                                    </label>
                                    <textarea
                                        v-model="form.treatment"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        rows="2"
                                        placeholder="Ej: Reposo, analgésicos cada 8 horas"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.treatment"
                                    />
                                </div>

                                <!-- Private Notes -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Notas Privadas (Cifradas)
                                    </label>
                                    <textarea
                                        v-model="form.private_notes"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        rows="2"
                                        placeholder="Notas confidenciales del doctor"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.private_notes"
                                    />
                                </div>
                            </div>

                            <div
                                class="rounded-lg border border-primary-200 bg-primary-50 p-4"
                            >
                                <div
                                    class="mb-3 flex items-center justify-between"
                                >
                                    <h4
                                        class="text-sm font-semibold text-primary-900"
                                    >
                                        Antecedentes de Salud
                                    </h4>
                                    <label
                                        class="inline-flex items-center gap-2 text-sm text-primary-900"
                                    >
                                        <input
                                            v-model="form.is_first_consultation"
                                            :disabled="
                                                isFirstConsultationWithDoctor
                                            "
                                            type="checkbox"
                                            class="rounded border-primary-300 text-primary-600 focus:ring-primary-500"
                                        />
                                        Primera consulta con este médico
                                    </label>
                                </div>

                                <p
                                    v-if="isFirstConsultationWithDoctor"
                                    class="mb-3 text-xs text-primary-800"
                                >
                                    Primera atención detectada automáticamente
                                    para este médico. Completa antecedentes.
                                </p>

                                <div
                                    v-if="
                                        form.is_first_consultation ||
                                        isFirstConsultationWithDoctor
                                    "
                                >
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Formulario de antecedentes
                                    </label>
                                    <textarea
                                        v-model="form.health_background"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        rows="3"
                                        placeholder="Antecedentes clínicos relevantes: enfermedades previas, alergias, cirugías, medicación habitual, etc."
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.health_background"
                                    />
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    @click="openPrescriptionModal()"
                                    class="inline-flex items-center rounded-md bg-success-600 px-4 py-2 text-sm font-semibold text-white hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-success-500 focus:ring-offset-2"
                                >
                                    📝 Receta
                                </button>
                                <PrimaryButton :disabled="form.processing">
                                    💾 Guardar Registro
                                </PrimaryButton>
                                <button
                                    type="button"
                                    @click="showForm = false"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                                >
                                    Cancelar
                                </button>
                            </div>
                            <div
                                v-if="prescriptions.length > 0"
                                class="mt-4 rounded-md bg-gray-50 p-4"
                            >
                                <div class="flex items-center justify-between">
                                    <h4
                                        class="text-sm font-semibold text-gray-700"
                                    >
                                        Recetas agregadas
                                    </h4>
                                    <button
                                        type="button"
                                        @click="openPrescriptionModal()"
                                        class="text-sm font-semibold text-success-700 hover:text-success-800"
                                    >
                                        + Nueva receta
                                    </button>
                                </div>
                                <div class="mt-3 space-y-3">
                                    <div
                                        v-for="(
                                            prescription, index
                                        ) in prescriptions"
                                        :key="index"
                                        class="rounded-md border border-success-200 bg-white p-3"
                                    >
                                        <div
                                            class="flex items-start justify-between"
                                        >
                                            <div>
                                                <p
                                                    class="text-sm font-semibold text-gray-800"
                                                >
                                                    Diagnostico:
                                                    {{ prescription.diagnosis }}
                                                </p>
                                                <p
                                                    class="mt-1 text-xs text-gray-600"
                                                >
                                                    Medicamentos:
                                                    {{
                                                        prescription.medications
                                                            .map(
                                                                (med) =>
                                                                    med.name,
                                                            )
                                                            .join(", ")
                                                    }}
                                                </p>
                                            </div>
                                            <div class="flex gap-2">
                                                <button
                                                    type="button"
                                                    @click="
                                                        downloadPrescriptionDraft(
                                                            prescription,
                                                        )
                                                    "
                                                    class="text-xs font-semibold text-success-600 hover:text-success-700"
                                                >
                                                    Descargar
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="
                                                        openPrescriptionModal(
                                                            index,
                                                        )
                                                    "
                                                    class="text-xs font-semibold text-primary-600 hover:text-primary-700"
                                                >
                                                    Editar
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="
                                                        removePrescription(
                                                            index,
                                                        )
                                                    "
                                                    class="text-xs font-semibold text-danger-600 hover:text-danger-700"
                                                >
                                                    Quitar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Prescription Modal -->
                    <div
                        v-if="showPrescriptionModal"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                    >
                        <div
                            class="w-full max-w-3xl rounded-lg bg-white p-6 shadow-lg"
                        >
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{
                                        editingPrescriptionIndex === null
                                            ? "Nueva receta"
                                            : "Editar receta"
                                    }}
                                </h3>
                                <button
                                    type="button"
                                    @click="showPrescriptionModal = false"
                                    class="text-gray-500 hover:text-gray-700"
                                >
                                    ✕
                                </button>
                            </div>

                            <div
                                v-if="prescriptionErrors.length"
                                class="mt-4 rounded-md bg-danger-50 p-3 text-sm text-danger-700"
                            >
                                <ul class="list-disc space-y-1 pl-5">
                                    <li
                                        v-for="(err, idx) in prescriptionErrors"
                                        :key="idx"
                                    >
                                        {{ err }}
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-4 space-y-4">
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700"
                                    >
                                        Diagnostico
                                    </label>
                                    <textarea
                                        v-model="prescriptionForm.diagnosis"
                                        rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    />
                                </div>

                                <div>
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <label
                                            class="text-sm font-semibold text-gray-700"
                                        >
                                            Medicamentos
                                        </label>
                                        <button
                                            type="button"
                                            @click="addMedication"
                                            class="text-sm font-semibold text-success-700 hover:text-success-800"
                                        >
                                            + Agregar
                                        </button>
                                    </div>
                                    <div class="mt-2 space-y-2">
                                        <div
                                            v-for="(
                                                med, medIndex
                                            ) in prescriptionForm.medications"
                                            :key="medIndex"
                                            class="grid grid-cols-1 gap-2 rounded-md border border-gray-200 p-3 md:grid-cols-4"
                                        >
                                            <input
                                                v-model="med.name"
                                                type="text"
                                                placeholder="Medicamento"
                                                class="rounded-md border-gray-300 text-sm"
                                            />
                                            <input
                                                v-model="med.dosage"
                                                type="text"
                                                placeholder="Dosis"
                                                class="rounded-md border-gray-300 text-sm"
                                            />
                                            <input
                                                v-model="med.frequency"
                                                type="text"
                                                placeholder="Frecuencia"
                                                class="rounded-md border-gray-300 text-sm"
                                            />
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <input
                                                    v-model="med.duration"
                                                    type="text"
                                                    placeholder="Duracion"
                                                    class="w-full rounded-md border-gray-300 text-sm"
                                                />
                                                <button
                                                    v-if="
                                                        prescriptionForm
                                                            .medications
                                                            .length > 1
                                                    "
                                                    type="button"
                                                    @click="
                                                        removeMedication(
                                                            medIndex,
                                                        )
                                                    "
                                                    class="text-xs font-semibold text-danger-600 hover:text-danger-700"
                                                >
                                                    Quitar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <label
                                            class="text-sm font-semibold text-gray-700"
                                        >
                                            Indicaciones
                                        </label>
                                        <button
                                            type="button"
                                            @click="addInstruction"
                                            class="text-sm font-semibold text-success-700 hover:text-success-800"
                                        >
                                            + Agregar
                                        </button>
                                    </div>
                                    <div class="mt-2 space-y-2">
                                        <div
                                            v-for="(
                                                ins, insIndex
                                            ) in prescriptionForm.instructions"
                                            :key="insIndex"
                                            class="flex items-start gap-2"
                                        >
                                            <textarea
                                                v-model="ins.description"
                                                rows="2"
                                                placeholder="Indicacion"
                                                class="w-full rounded-md border-gray-300 text-sm"
                                            />
                                            <button
                                                v-if="
                                                    prescriptionForm
                                                        .instructions.length > 1
                                                "
                                                type="button"
                                                @click="
                                                    removeInstruction(insIndex)
                                                "
                                                class="mt-2 text-xs font-semibold text-danger-600 hover:text-danger-700"
                                            >
                                                Quitar
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700"
                                    >
                                        Notas
                                    </label>
                                    <textarea
                                        v-model="prescriptionForm.notes"
                                        rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    />
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <button
                                    type="button"
                                    @click="showPrescriptionModal = false"
                                    class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="button"
                                    @click="savePrescription"
                                    class="rounded-md bg-success-600 px-4 py-2 text-sm font-semibold text-white hover:bg-success-700"
                                >
                                    Aceptar receta
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Medical Records List -->
                    <div class="divide-y divide-gray-200">
                        <div
                            v-if="displayedRecords.length === 0"
                            class="p-6 text-center text-gray-500"
                        >
                            <p class="mb-2 text-lg">
                                📭 No hay registros para mostrar
                            </p>
                            <p class="text-sm">
                                {{
                                    showAllRecords
                                        ? "No hay registros médicos en el historial"
                                        : "No tienes registros en este paciente"
                                }}
                            </p>
                        </div>

                        <div
                            v-for="record in displayedRecords"
                            :key="record.id"
                            class="border-b border-gray-200 p-6 last:border-b-0"
                        >
                            <div class="mb-4 flex items-start justify-between">
                                <div>
                                    <p
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        🏥 Consulta -
                                        {{ formatDateTime(record.created_at) }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        👨‍⚕️ Dr. {{ record.doctor.name }}
                                    </p>
                                </div>
                                <Link
                                    :href="
                                        route('patients.medical-records.show', [
                                            patient.id,
                                            record.id,
                                        ])
                                    "
                                    class="inline-flex items-center rounded-md bg-primary-600 px-3 py-1 text-sm font-semibold text-white hover:bg-primary-700"
                                >
                                    Ver
                                </Link>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <!-- Reason -->
                                <div
                                    v-if="record.reason"
                                    class="rounded-lg bg-primary-50 p-4"
                                >
                                    <p
                                        class="text-sm font-semibold text-primary-900"
                                    >
                                        Motivo
                                    </p>
                                    <p class="mt-1 text-gray-700">
                                        {{ record.reason }}
                                    </p>
                                </div>

                                <!-- Diagnosis -->
                                <div
                                    v-if="record.diagnosis"
                                    class="rounded-lg bg-success-50 p-4"
                                >
                                    <p
                                        class="text-sm font-semibold text-success-900"
                                    >
                                        Diagnóstico
                                    </p>
                                    <p class="mt-1 text-gray-700">
                                        {{ record.diagnosis }}
                                    </p>
                                </div>

                                <!-- Treatment -->
                                <div
                                    v-if="record.treatment"
                                    class="rounded-lg bg-primary-50 p-4"
                                >
                                    <p
                                        class="text-sm font-semibold text-primary-900"
                                    >
                                        Tratamiento
                                    </p>
                                    <p class="mt-1 text-gray-700">
                                        {{ record.treatment }}
                                    </p>
                                </div>

                                <!-- Private Notes - Only visible to doctor who created them -->
                                <div
                                    v-if="
                                        record.private_notes &&
                                        record.doctor.id === currentUser?.id
                                    "
                                    class="rounded-lg bg-gray-100 p-4"
                                >
                                    <p
                                        class="text-sm font-semibold text-gray-900"
                                    >
                                        🔒 Notas Privadas (Cifradas)
                                    </p>
                                    <p class="mt-1 italic text-gray-700">
                                        {{ record.private_notes }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
