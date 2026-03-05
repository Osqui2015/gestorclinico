<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { format } from "date-fns";
import { es } from "date-fns/locale";

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
    patient: Patient;
}

interface QueueData {
    doctor: Doctor;
    attending: Appointment | null;
    nextCalled: Appointment | null;
    pending: Appointment[];
    called: Appointment[];
}

interface Props {
    queues: QueueData[];
}

const props = defineProps<Props>();

const formatDateTime = (date: string) => {
    return format(new Date(date), "HH:mm", { locale: es });
};

const getPendingCount = (queue: QueueData) => {
    return queue.pending.length + (queue.nextCalled ? 1 : 0);
};
</script>

<template>
    <Head title="Tablero de Colas" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 "
            >
                👨‍⚕️ Tablero de Colas de Atención
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="queue in queues"
                        :key="queue.doctor.id"
                        class="rounded-lg bg-white shadow-lg "
                    >
                        <!-- Header -->
                        <div
                            class="border-b-4 border-primary-600 bg-gradient-to-r from-primary-50 to-primary-50 px-6 py-4  "
                        >
                            <h3
                                class="text-lg font-bold text-gray-900 "
                            >
                                👨‍⚕️ {{ queue.doctor.name }}
                            </h3>
                            <p class="text-sm text-gray-600 ">
                                {{ queue.doctor.specialty }}
                            </p>
                        </div>

                        <!-- Currently Attending -->
                        <div
                            class="border-b border-gray-200 p-4 "
                        >
                            <p
                                class="mb-2 text-xs font-semibold uppercase text-gray-600 "
                            >
                                Atendiendo
                            </p>
                            <div
                                v-if="queue.attending"
                                class="rounded-lg bg-primary-50 p-3 "
                            >
                                <p
                                    class="font-semibold text-primary-900 "
                                >
                                    {{ queue.attending.patient.first_name }}
                                    {{ queue.attending.patient.last_name }}
                                </p>
                                <p
                                    class="text-xs text-gray-600 "
                                >
                                    DNI: {{ queue.attending.patient.dni }}
                                </p>
                            </div>
                            <div
                                v-else
                                class="rounded-lg bg-gray-100 p-3 text-center "
                            >
                                <p
                                    class="text-xs text-gray-600 "
                                >
                                    Ninguno
                                </p>
                            </div>
                        </div>

                        <!-- Next Called -->
                        <div
                            class="border-b border-gray-200 p-4 "
                        >
                            <p
                                class="mb-2 text-xs font-semibold uppercase text-gray-600 "
                            >
                                Siguiente (Llamado)
                            </p>
                            <div
                                v-if="queue.nextCalled"
                                class="rounded-lg bg-warning-50 p-3 "
                            >
                                <p
                                    class="font-semibold text-warning-900 "
                                >
                                    {{ queue.nextCalled.patient.first_name }}
                                    {{ queue.nextCalled.patient.last_name }}
                                </p>
                                <p
                                    class="text-xs text-gray-600 "
                                >
                                    DNI: {{ queue.nextCalled.patient.dni }}
                                </p>
                            </div>
                            <div
                                v-else
                                class="rounded-lg bg-gray-100 p-3 text-center "
                            >
                                <p
                                    class="text-xs text-gray-600 "
                                >
                                    Esperando...
                                </p>
                            </div>
                        </div>

                        <!-- Pending Count -->
                        <div class="p-4 text-center">
                            <p
                                class="text-xs font-semibold uppercase text-gray-600 "
                            >
                                En Espera
                            </p>
                            <p
                                class="text-3xl font-bold text-primary-600 "
                            >
                                {{ getPendingCount(queue) }}
                            </p>
                            <div
                                v-if="queue.pending.length > 0"
                                class="mt-3 space-y-1"
                            >
                                <p
                                    class="text-xs text-gray-600 "
                                >
                                    Próximos:
                                </p>
                                <p
                                    v-for="(ap, idx) in queue.pending.slice(
                                        0,
                                        2,
                                    )"
                                    :key="ap.id"
                                    class="text-xs text-gray-700 "
                                >
                                    {{ idx + 1 }}.
                                    {{ ap.patient.first_name }} ({{
                                        formatDateTime(ap.scheduled_at)
                                    }})
                                </p>
                                <p
                                    v-if="queue.pending.length > 2"
                                    class="text-xs text-gray-500 "
                                >
                                    +{{ queue.pending.length - 2 }} más
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="queues.length === 0"
                    class="rounded-lg bg-white p-12 text-center shadow-sm "
                >
                    <p class="text-lg text-gray-600 ">
                        📭 No hay colas disponibles
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
