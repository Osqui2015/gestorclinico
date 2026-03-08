<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
}

interface Doctor {
    id: number;
    name: string;
    specialty: string | null;
}

interface Bed {
    id: number;
    bed_number: string;
    bed_type: string;
    full_name: string;
    room: {
        id: number;
        name: string;
        floor: number | null;
    };
}

interface Props {
    availableBeds: Bed[];
    patients: Patient[];
    doctors: Doctor[];
}

const props = defineProps<Props>();

const form = useForm({
    patient_id: "" as number | "",
    bed_id: "" as number | "",
    doctor_id: "" as number | "",
    operation_id: "" as number | "",
    admission_date: new Date().toISOString().slice(0, 16),
    expected_discharge_date: "",
    admission_reason: "",
    admission_type: "scheduled",
    diagnosis: "",
    treatment: "",
});

const submit = () => {
    form.post(route("hospitalizations.store"));
};

const getBedTypeLabel = (type: string): string => {
    const labels: Record<string, string> = {
        standard: "Estándar",
        intensive_care: "Cuidados Intensivos",
        isolation: "Aislamiento",
        pediatric: "Pediátrica",
        psychiatric: "Psiquiátrica",
    };
    return labels[type] || type;
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Nueva Internación" />

        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <a
                        :href="route('hospitalizations.index')"
                        class="text-sm text-indigo-600 hover:text-indigo-900 mb-2 inline-block"
                    >
                        ← Volver al listado
                    </a>
                    <h2 class="text-2xl font-bold text-gray-900">
                        ➕ Nueva Internación
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Registre una nueva internación asignando un paciente a
                        una cama disponible
                    </p>
                </div>

                <!-- Form -->
                <form
                    @submit.prevent="submit"
                    class="bg-white shadow rounded-lg p-6 space-y-6"
                >
                    <!-- Patient Selection -->
                    <div>
                        <label
                            for="patient_id"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Paciente <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="patient_id"
                            v-model="form.patient_id"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            :class="{
                                'border-red-500': form.errors.patient_id,
                            }"
                        >
                            <option value="">Seleccione un paciente...</option>
                            <option
                                v-for="patient in patients"
                                :key="patient.id"
                                :value="patient.id"
                            >
                                {{ patient.last_name }},
                                {{ patient.first_name }} ({{ patient.dni }})
                            </option>
                        </select>
                        <p
                            v-if="form.errors.patient_id"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.patient_id }}
                        </p>
                    </div>

                    <!-- Bed Selection -->
                    <div>
                        <label
                            for="bed_id"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Cama <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="bed_id"
                            v-model="form.bed_id"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': form.errors.bed_id }"
                        >
                            <option value="">Seleccione una cama...</option>
                            <option
                                v-for="bed in availableBeds"
                                :key="bed.id"
                                :value="bed.id"
                            >
                                {{ bed.full_name }} -
                                {{ getBedTypeLabel(bed.bed_type) }} (Piso
                                {{ bed.room.floor }})
                            </option>
                        </select>
                        <p
                            v-if="form.errors.bed_id"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.bed_id }}
                        </p>
                        <p
                            v-if="availableBeds.length === 0"
                            class="mt-1 text-sm text-yellow-600"
                        >
                            ⚠️ No hay camas disponibles en este momento.
                        </p>
                    </div>

                    <!-- Doctor Selection -->
                    <div>
                        <label
                            for="doctor_id"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Médico Responsable
                            <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="doctor_id"
                            v-model="form.doctor_id"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': form.errors.doctor_id }"
                        >
                            <option value="">Seleccione un médico...</option>
                            <option
                                v-for="doctor in doctors"
                                :key="doctor.id"
                                :value="doctor.id"
                            >
                                {{ doctor.name }}
                                <span v-if="doctor.specialty"
                                    >({{ doctor.specialty }})</span
                                >
                            </option>
                        </select>
                        <p
                            v-if="form.errors.doctor_id"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.doctor_id }}
                        </p>
                    </div>

                    <!-- Admission Type -->
                    <div>
                        <label
                            for="admission_type"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Tipo de Admisión <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="admission_type"
                            v-model="form.admission_type"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="emergency">🚨 Emergencia</option>
                            <option value="scheduled">📅 Programada</option>
                            <option value="post_surgical">
                                🏥 Post-quirúrgica
                            </option>
                            <option value="transfer">🔄 Transferencia</option>
                        </select>
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label
                                for="admission_date"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Fecha y Hora de Ingreso
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="admission_date"
                                v-model="form.admission_date"
                                type="datetime-local"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                :class="{
                                    'border-red-500':
                                        form.errors.admission_date,
                                }"
                            />
                            <p
                                v-if="form.errors.admission_date"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.admission_date }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="expected_discharge_date"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Fecha Estimada de Alta
                            </label>
                            <input
                                id="expected_discharge_date"
                                v-model="form.expected_discharge_date"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                :class="{
                                    'border-red-500':
                                        form.errors.expected_discharge_date,
                                }"
                            />
                            <p
                                v-if="form.errors.expected_discharge_date"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.expected_discharge_date }}
                            </p>
                        </div>
                    </div>

                    <!-- Admission Reason -->
                    <div>
                        <label
                            for="admission_reason"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Motivo de Internación
                            <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="admission_reason"
                            v-model="form.admission_reason"
                            required
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            :class="{
                                'border-red-500': form.errors.admission_reason,
                            }"
                            placeholder="Describa el motivo de la internación..."
                        ></textarea>
                        <p
                            v-if="form.errors.admission_reason"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.admission_reason }}
                        </p>
                    </div>

                    <!-- Diagnosis -->
                    <div>
                        <label
                            for="diagnosis"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Diagnóstico
                        </label>
                        <textarea
                            id="diagnosis"
                            v-model="form.diagnosis"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="Diagnóstico médico (opcional)..."
                        ></textarea>
                    </div>

                    <!-- Treatment -->
                    <div>
                        <label
                            for="treatment"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Tratamiento
                        </label>
                        <textarea
                            id="treatment"
                            v-model="form.treatment"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="Plan de tratamiento (opcional)..."
                        ></textarea>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end gap-3 pt-6 border-t">
                        <a
                            :href="route('hospitalizations.index')"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Cancelar
                        </a>
                        <button
                            type="submit"
                            :disabled="
                                form.processing || availableBeds.length === 0
                            "
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing">Registrando...</span>
                            <span v-else>Registrar Internación</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
