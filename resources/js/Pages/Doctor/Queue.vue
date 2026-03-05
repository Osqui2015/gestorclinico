<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { format } from "date-fns";
import { es } from "date-fns/locale";
import { ref, onMounted } from "vue";

interface Doctor {
    id: number;
    name: string;
    specialty: string;
}

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
}

interface Appointment {
    id: number;
    doctor_id: number;
    patient_id: number;
    scheduled_at: string;
    status: string;
    reason: string | null;
    doctor?: Doctor;
    patient: Patient;
}

interface Props {
    attending: Appointment | null;
    nextCalled: Appointment | null;
    pending: Appointment[];
    called: Appointment[];
    appointments: Appointment[];
}

const props = defineProps<Props>();

const form = useForm({});
const loadingId = ref<number | null>(null);

const formatDateTime = (date: string) => {
    return format(new Date(date), "HH:mm", { locale: es });
};

const formatDateTimeDetailed = (date: string) => {
    return format(new Date(date), "dd MMM yyyy - HH:mm", { locale: es });
};

const callNextPatient = (appointmentId: number) => {
    loadingId.value = appointmentId;
    form.patch(route("doctor.appointments.call-next", appointmentId), {
        onFinish: () => (loadingId.value = null),
    });
};

const completePatient = (appointmentId: number) => {
    loadingId.value = appointmentId;
    form.patch(route("doctor.appointments.complete", appointmentId), {
        onFinish: () => {
            setTimeout(() => {
                window.location.reload();
            }, 500);
        },
    });
};

// Auto-refresh the page every 5 seconds
onMounted(() => {
    const interval = setInterval(() => {
        const InertiaGlobal = (window as any).Inertia;
        if (InertiaGlobal && typeof InertiaGlobal.get === "function") {
            InertiaGlobal.get(
                route("doctor.queue.board"),
                {},
                {
                    preserveState: true,
                    preserveScroll: true,
                    only: [
                        "attending",
                        "nextCalled",
                        "pending",
                        "called",
                        "appointments",
                    ],
                },
            );
        } else {
            // fallback to full reload if Inertia client not available
            window.location.reload();
        }
    }, 5000);

    return () => clearInterval(interval);
});
</script>

<template>
    <Head title="Turnero" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 "
            >
                🏥 Turnero del Consultorio
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Currently Attending -->
                <div
                    class="mb-8 rounded-lg bg-white shadow-lg "
                >
                    <div class="border-b-4 border-primary-600 px-6 py-4">
                        <h2
                            class="text-2xl font-bold text-gray-900 "
                        >
                            👤 Atendiendo Ahora
                        </h2>
                    </div>

                    <div class="p-8">
                        <div v-if="attending" class="space-y-4">
                            <div
                                class="rounded-lg bg-primary-50 p-6 "
                            >
                                <p
                                    class="text-sm text-gray-600 "
                                >
                                    Paciente
                                </p>
                                <h3
                                    class="text-4xl font-bold text-primary-600 "
                                >
                                    {{ attending.patient.first_name }}
                                    {{ attending.patient.last_name }}
                                </h3>
                                <p
                                    class="mt-2 text-sm text-gray-600 "
                                >
                                    DNI: {{ attending.patient.dni }}
                                </p>
                                <p
                                    v-if="attending.reason"
                                    class="mt-3 text-base italic text-gray-700 "
                                >
                                    "{{ attending.reason }}"
                                </p>
                            </div>

                            <div class="flex gap-4">
                                <button
                                    @click="
                                        $inertia.visit(
                                            route(
                                                'prescriptions.create-from-appointment',
                                                attending.id,
                                            ),
                                        )
                                    "
                                    class="flex-1 rounded-lg bg-primary-600 px-6 py-3 font-semibold text-white hover:bg-primary-700 transition"
                                >
                                    💊 Generar Receta
                                </button>
                                <button
                                    @click="completePatient(attending.id)"
                                    :disabled="loadingId === attending.id"
                                    class="flex-1 rounded-lg bg-success-600 px-6 py-3 font-semibold text-white hover:bg-success-700 disabled:opacity-50"
                                >
                                    {{
                                        loadingId === attending.id
                                            ? "Procesando..."
                                            : "✅ Completar y Siguiente"
                                    }}
                                </button>
                            </div>
                        </div>

                        <div
                            v-else
                            class="rounded-lg bg-gray-50 p-8 text-center "
                        >
                            <p
                                class="text-2xl font-semibold text-gray-600 "
                            >
                                📭 Ningún paciente atendiendo
                            </p>
                            <p class="mt-2 text-gray-500 ">
                                Selecciona el primer paciente de la cola
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Next in Queue -->
                <div
                    class="mb-8 rounded-lg bg-white shadow-lg "
                >
                    <div class="border-b-4 border-warning-500 px-6 py-4">
                        <h2
                            class="text-xl font-bold text-gray-900 "
                        >
                            📢 Siguiente (Llamado)
                        </h2>
                    </div>

                    <div class="p-6">
                        <div v-if="nextCalled" class="space-y-4">
                            <div
                                class="rounded-lg bg-warning-50 p-4 "
                            >
                                <p
                                    class="text-sm text-gray-600 "
                                >
                                    {{ nextCalled.patient.first_name }}
                                    {{ nextCalled.patient.last_name }}
                                </p>
                                <p
                                    class="text-sm text-warning-600 "
                                >
                                    DNI: {{ nextCalled.patient.dni }}
                                </p>
                                <p
                                    v-if="nextCalled.reason"
                                    class="mt-2 text-sm italic text-gray-700 "
                                >
                                    {{ nextCalled.reason }}
                                </p>
                            </div>

                            <button
                                @click="callNextPatient(nextCalled.id)"
                                :disabled="loadingId === nextCalled.id"
                                class="w-full rounded-lg bg-warning-600 px-4 py-2 font-semibold text-white hover:bg-warning-700 disabled:opacity-50"
                            >
                                {{
                                    loadingId === nextCalled.id
                                        ? "Procesando..."
                                        : "🎯 Atender Este"
                                }}
                            </button>
                        </div>

                        <div
                            v-else
                            class="rounded-lg bg-gray-50 p-6 text-center "
                        >
                            <p class="text-gray-600 ">
                                ⏳ Esperando al próximo paciente...
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pending Queue -->
                <div class="rounded-lg bg-white shadow-lg ">
                    <div class="border-b-4 border-gray-400 px-6 py-4">
                        <h2
                            class="text-lg font-bold text-gray-900 "
                        >
                            📋 Cola de Espera ({{ pending.length }} pacientes)
                        </h2>
                    </div>

                    <div class="divide-y divide-gray-200 ">
                        <div
                            v-if="pending.length === 0"
                            class="p-6 text-center text-gray-500 "
                        >
                            📭 No hay pacientes en la cola
                        </div>

                        <div
                            v-for="(appointment, index) in pending"
                            :key="appointment.id"
                            class="flex items-center justify-between p-4 hover:bg-gray-50 "
                        >
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 "
                                    >
                                        {{ index + 1 }}
                                    </span>
                                    <div>
                                        <p
                                            class="font-semibold text-gray-900 "
                                        >
                                            {{ appointment.patient.first_name }}
                                            {{ appointment.patient.last_name }}
                                        </p>
                                        <p
                                            class="text-sm text-gray-600 "
                                        >
                                            DNI: {{ appointment.patient.dni }} |
                                            Hora:
                                            {{
                                                formatDateTime(
                                                    appointment.scheduled_at,
                                                )
                                            }}
                                        </p>
                                        <p
                                            v-if="appointment.reason"
                                            class="text-xs italic text-gray-500 "
                                        >
                                            {{ appointment.reason }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <button
                                @click="callNextPatient(appointment.id)"
                                :disabled="loadingId === appointment.id"
                                class="ml-4 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-50"
                            >
                                {{
                                    loadingId === appointment.id ? "..." : "📞"
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
