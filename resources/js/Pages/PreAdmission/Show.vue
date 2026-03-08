<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";

interface RequiredDoc {
    id: number;
    name: string;
    code: string;
    description?: string;
    is_mandatory: boolean;
    requires_upload: boolean;
    preAdmissionDocument?: {
        id: number;
        status: string;
        file_path?: string;
        original_filename?: string;
        verification_notes?: string;
        rejection_reason?: string;
    };
}

interface Operation {
    id: number;
    operation_type: string;
    scheduled_start: string;
    urgency: string;
    status: string;
    room: { id: number; name: string; code: string };
    doctor: { id: number; name: string };
}

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni?: string;
    phone?: string;
    email?: string;
    emergency_contact_name?: string;
    emergency_contact_phone?: string;
    allergies?: string;
}

interface PreAdm {
    id: number;
    operation_id: number;
    patient_id: number;
    secretary_id?: number;
    status: string;
    urgent_number?: string;
    contact_phone?: string;
    emergency_contact_name?: string;
    emergency_contact_phone?: string;
    medical_history_verified?: string;
    patient_observations?: string;
    data_verified_at?: string;
    documentation_verified_at?: string;
    ready_for_surgery_at?: string;
    operation: Operation;
    patient: Patient;
    secretary?: { id: number; name: string };
}

const props = defineProps<{
    preAdmission: PreAdm;
    requiredDocuments: RequiredDoc[];
    canConfirm: boolean;
    permissions: { canVerify: boolean };
}>();

const activeTab = ref<"data" | "documents" | "summary">("data");
const uploadingDocId = ref<number | null>(null);

const dataForm = useForm({
    urgent_number: props.preAdmission.urgent_number || "",
    contact_phone: props.preAdmission.contact_phone || "",
    emergency_contact_name: props.preAdmission.emergency_contact_name || "",
    emergency_contact_phone: props.preAdmission.emergency_contact_phone || "",
    medical_history_verified:
        props.preAdmission.medical_history_verified || "pending",
    patient_observations: props.preAdmission.patient_observations || "",
});

const uploadForm = useForm<{
    required_document_id: number | null;
    file: File | null;
}>({
    required_document_id: null,
    file: null,
});

const submitData = () => {
    dataForm.post(route("pre-admissions.verify-data", props.preAdmission.id), {
        preserveScroll: true,
    });
};

const handleFileUpload = (event: Event, requiredDocumentId: number) => {
    const input = event.target as HTMLInputElement;
    const selectedFile = input.files?.[0];
    if (!selectedFile) return;

    uploadForm.required_document_id = requiredDocumentId;
    uploadForm.file = selectedFile;

    uploadForm.post(
        route("pre-admissions.upload-document", props.preAdmission.id),
        {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                uploadingDocId.value = null;
                uploadForm.reset();
                input.value = "";
            },
        },
    );
};

const verifyDoc = (padDocId: number) => {
    router.post(
        route("pre-admissions.verify-document", props.preAdmission.id),
        {
            pre_admission_document_id: padDocId,
            verification_notes: "",
        },
        {
            preserveScroll: true,
        },
    );
};

const rejectDoc = (padDocId: number) => {
    const reason = prompt("Motivo del rechazo:");
    if (!reason) return;

    router.post(
        route("pre-admissions.reject-document", props.preAdmission.id),
        {
            pre_admission_document_id: padDocId,
            rejection_reason: reason,
        },
        {
            preserveScroll: true,
        },
    );
};

const confirmForSurgery = () => {
    if (
        confirm(
            "¿Confirmar pre-internación? La operación pasará a estar lista para la sala.",
        )
    ) {
        router.post(
            route("pre-admissions.confirm", props.preAdmission.id),
            {},
            {
                preserveScroll: true,
            },
        );
    }
};

const cancelPreAdm = () => {
    const reason = prompt("Motivo de cancelación:");
    if (!reason) return;

    router.post(
        route("pre-admissions.cancel", props.preAdmission.id),
        {
            cancellation_reason: reason,
        },
        {
            preserveScroll: true,
        },
    );
};

const getDocStatus = (doc: RequiredDoc) => {
    if (!doc.preAdmissionDocument) return "pending";
    return doc.preAdmissionDocument.status;
};

const getDocStatusLabel = (status: string) => {
    const map: Record<string, string> = {
        pending: "Pendiente",
        uploaded: "Subido",
        verified: "✅ Verificado",
        rejected: "❌ Rechazado",
        not_applicable: "N/A",
    };
    return map[status] || status;
};

const getDocStatusClass = (status: string) => {
    const map: Record<string, string> = {
        pending: "bg-gray-100 text-gray-800",
        uploaded: "bg-blue-100 text-blue-800",
        verified: "bg-green-100 text-green-800",
        rejected: "bg-red-100 text-red-800",
        not_applicable: "bg-gray-50 text-gray-600",
    };
    return map[status] || "bg-gray-100 text-gray-800";
};

const progressPercentage = computed(() => {
    const totalDocs = props.requiredDocuments.length;
    if (totalDocs === 0) {
        return 0;
    }

    const verifiedDocs = props.requiredDocuments.filter(
        (d) => getDocStatus(d) === "verified",
    ).length;
    return Math.round((verifiedDocs / totalDocs) * 100);
});

const dataIsComplete = computed(() => !!props.preAdmission.data_verified_at);

const allDocsVerified = computed(() =>
    props.requiredDocuments.every(
        (d) =>
            getDocStatus(d) === "verified" ||
            getDocStatus(d) === "not_applicable",
    ),
);
</script>

<template>
    <Head title="Detalle Pre-Internación" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        Pre-Internación #{{ preAdmission.id }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ preAdmission.patient.first_name }}
                        {{ preAdmission.patient.last_name }}
                    </p>
                </div>
                <div v-if="permissions.canVerify" class="flex gap-2">
                    <button
                        v-if="preAdmission.status !== 'ready_for_surgery'"
                        @click="confirmForSurgery"
                        :disabled="!canConfirm"
                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                    >
                        ✅ Confirmar para cirugía
                    </button>
                    <button
                        v-if="preAdmission.status !== 'cancelled'"
                        @click="cancelPreAdm"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white"
                    >
                        ❌ Cancelar
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Progress Bar -->
                <div
                    v-if="preAdmission.status !== 'ready_for_surgery'"
                    class="mb-6"
                >
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-700">
                            Progreso de verificación
                        </p>
                        <span class="text-sm font-semibold text-gray-900">
                            {{ progressPercentage }}%
                        </span>
                    </div>
                    <div
                        class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-200"
                    >
                        <div
                            class="h-full transition-all"
                            :class="
                                progressPercentage === 100
                                    ? 'bg-green-600'
                                    : 'bg-blue-600'
                            "
                            :style="{ width: `${progressPercentage}%` }"
                        ></div>
                    </div>
                </div>

                <!-- Status Alert -->
                <div
                    v-if="preAdmission.status === 'ready_for_surgery'"
                    class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4"
                >
                    <p class="text-sm font-semibold text-green-800">
                        ✅ Pre-internación confirmada. Operación lista para la
                        sala.
                    </p>
                </div>

                <!-- Tabs -->
                <div class="mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8">
                            <button
                                @click="activeTab = 'data'"
                                :class="[
                                    activeTab === 'data'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500',
                                    'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
                                ]"
                            >
                                📋 Datos del paciente
                                {{ dataIsComplete ? "✅" : "" }}
                            </button>
                            <button
                                @click="activeTab = 'documents'"
                                :class="[
                                    activeTab === 'documents'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500',
                                    'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
                                ]"
                            >
                                📄 Documentación
                                {{ allDocsVerified ? "✅" : "" }}
                            </button>
                            <button
                                @click="activeTab = 'summary'"
                                :class="[
                                    activeTab === 'summary'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500',
                                    'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
                                ]"
                            >
                                📝 Resumen
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Tab: Datos -->
                <div v-if="activeTab === 'data'" class="space-y-6">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">
                            Información del Paciente
                        </h3>
                        <dl
                            class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2"
                        >
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Nombre
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ preAdmission.patient.first_name }}
                                    {{ preAdmission.patient.last_name }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    DNI
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ preAdmission.patient.dni || "N/A" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Teléfono
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ preAdmission.patient.phone || "N/A" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Email
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ preAdmission.patient.email || "N/A" }}
                                </dd>
                            </div>
                            <div v-if="preAdmission.patient.allergies">
                                <dt class="text-sm font-medium text-red-600">
                                    ⚠️ Alergias
                                </dt>
                                <dd
                                    class="mt-1 text-sm text-red-700 font-semibold"
                                >
                                    {{ preAdmission.patient.allergies }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <form @submit.prevent="submitData" class="space-y-6">
                        <div class="rounded-lg bg-white p-6 shadow">
                            <h3
                                class="mb-4 text-lg font-semibold text-gray-900"
                            >
                                Verificación de Datos
                            </h3>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Número de urgencia
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="dataForm.urgent_number"
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Teléfono de contacto
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="dataForm.contact_phone"
                                        type="tel"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Contacto de emergencia
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="
                                            dataForm.emergency_contact_name
                                        "
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Teléfono emergencia
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="
                                            dataForm.emergency_contact_phone
                                        "
                                        type="tel"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Historia médica verificada
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="
                                            dataForm.medical_history_verified
                                        "
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option value="pending">
                                            Pendiente
                                        </option>
                                        <option value="yes">Sí</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>

                                <div class="sm:col-span-2">
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Observaciones
                                    </label>
                                    <textarea
                                        v-model="dataForm.patient_observations"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="dataForm.processing"
                                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50"
                                >
                                    {{
                                        dataForm.processing
                                            ? "Guardando..."
                                            : "✅ Verificar datos"
                                    }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tab: Documentos -->
                <div v-if="activeTab === 'documents'" class="space-y-4">
                    <div
                        v-for="doc in requiredDocuments"
                        :key="doc.id"
                        class="rounded-lg border border-gray-200 p-4"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">
                                    {{ doc.name }}
                                    <span
                                        v-if="doc.is_mandatory"
                                        class="text-red-500"
                                    >
                                        *
                                    </span>
                                </h4>
                                <p class="text-sm text-gray-600">
                                    {{ doc.description }}
                                </p>
                                <div class="mt-2">
                                    <span
                                        :class="[
                                            getDocStatusClass(
                                                getDocStatus(doc),
                                            ),
                                            'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                        ]"
                                    >
                                        {{
                                            getDocStatusLabel(getDocStatus(doc))
                                        }}
                                    </span>
                                </div>
                            </div>

                            <div class="ml-4 flex flex-col gap-2">
                                <button
                                    v-if="
                                        doc.requires_upload &&
                                        getDocStatus(doc) === 'pending'
                                    "
                                    @click="uploadingDocId = doc.id"
                                    class="text-blue-600 hover:text-blue-900 text-sm font-semibold"
                                >
                                    📤 Subir
                                </button>

                                <button
                                    v-if="getDocStatus(doc) === 'uploaded'"
                                    @click="
                                        verifyDoc(doc.preAdmissionDocument!.id)
                                    "
                                    class="text-green-600 hover:text-green-900 text-sm font-semibold"
                                >
                                    ✅ Verificar
                                </button>

                                <button
                                    v-if="
                                        getDocStatus(doc) === 'uploaded' ||
                                        getDocStatus(doc) === 'verified'
                                    "
                                    @click="
                                        rejectDoc(doc.preAdmissionDocument!.id)
                                    "
                                    class="text-red-600 hover:text-red-900 text-sm font-semibold"
                                >
                                    ❌ Rechazar
                                </button>

                                <a
                                    v-if="doc.preAdmissionDocument?.file_path"
                                    :href="`/storage/${doc.preAdmissionDocument.file_path}`"
                                    target="_blank"
                                    class="text-indigo-600 hover:text-indigo-900 text-sm font-semibold"
                                >
                                    📥 Ver archivo
                                </a>
                            </div>
                        </div>

                        <input
                            v-if="uploadingDocId === doc.id"
                            type="file"
                            class="mt-3"
                            @change="(event) => handleFileUpload(event, doc.id)"
                        />
                    </div>
                </div>

                <!-- Tab: Resumen -->
                <div v-if="activeTab === 'summary'" class="space-y-6">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">
                            Resumen de Operación
                        </h3>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Tipo de operación
                                </dt>
                                <dd
                                    class="mt-1 text-sm text-gray-900 font-semibold"
                                >
                                    {{ preAdmission.operation.operation_type }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Sala asignada
                                </dt>
                                <dd
                                    class="mt-1 text-sm text-gray-900 font-semibold"
                                >
                                    {{ preAdmission.operation.room.name }}
                                    ({{ preAdmission.operation.room.code }})
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Médico
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ preAdmission.operation.doctor.name }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Fecha y hora
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{
                                        new Date(
                                            preAdmission.operation
                                                .scheduled_start,
                                        ).toLocaleString("es-AR")
                                    }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
