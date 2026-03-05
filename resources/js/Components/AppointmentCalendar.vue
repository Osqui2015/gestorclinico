<script setup lang="ts">
import { ref, computed, watch } from "vue";

interface TimeSlot {
    time: string;
    available: boolean;
    display: string;
}

interface Props {
    doctorId: string | number;
    selectedDateTime?: string;
}

interface Emits {
    (e: "update:selectedDateTime", value: string): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const currentMonth = ref(new Date());
const selectedDate = ref<string | null>(null);
const availableSlots = ref<TimeSlot[]>([]);
const selectedTime = ref<string | null>(null);
const loadingSlots = ref(false);
const errorMessage = ref<string | null>(null);

// Get number of days in month
const daysInMonth = computed(() => {
    return new Date(
        currentMonth.value.getFullYear(),
        currentMonth.value.getMonth() + 1,
        0,
    ).getDate();
});

// Get first day of month (0 = Sunday)
const firstDayOfMonth = computed(() => {
    return new Date(
        currentMonth.value.getFullYear(),
        currentMonth.value.getMonth(),
        1,
    ).getDay();
});

// Generate calendar days
const calendarDays = computed(() => {
    const days: (number | null)[] = [];
    const emptyDays = firstDayOfMonth.value;

    // Add empty cells for days before month starts
    for (let i = 0; i < emptyDays; i++) {
        days.push(null);
    }

    // Add days of month
    for (let i = 1; i <= daysInMonth.value; i++) {
        days.push(i);
    }

    return days;
});

// Format date to YYYY-MM-DD
const formatDate = (date: Date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
};

// Check if date is today or in the past
const isPastDate = (day: number) => {
    const today = new Date();
    const checkDate = new Date(
        currentMonth.value.getFullYear(),
        currentMonth.value.getMonth(),
        day,
    );
    return checkDate < today && !isSameDay(checkDate, today);
};

// Check if date is today
const isSameDay = (date1: Date, date2: Date) => {
    return (
        date1.getFullYear() === date2.getFullYear() &&
        date1.getMonth() === date2.getMonth() &&
        date1.getDate() === date2.getDate()
    );
};

// Previous month
const prevMonth = () => {
    currentMonth.value = new Date(
        currentMonth.value.getFullYear(),
        currentMonth.value.getMonth() - 1,
    );
};

// Next month
const nextMonth = () => {
    currentMonth.value = new Date(
        currentMonth.value.getFullYear(),
        currentMonth.value.getMonth() + 1,
    );
};

// Select a date
const selectDate = async (day: number) => {
    if (!day || isPastDate(day)) return;

    const dateStr = formatDate(
        new Date(
            currentMonth.value.getFullYear(),
            currentMonth.value.getMonth(),
            day,
        ),
    );
    selectedDate.value = dateStr;
    selectedTime.value = null;
    availableSlots.value = [];
    errorMessage.value = null;

    // Fetch available slots
    await fetchAvailableSlots(dateStr);
};

// Fetch available slots from backend
const fetchAvailableSlots = async (date: string) => {
    loadingSlots.value = true;
    errorMessage.value = null;
    try {
        const response = await fetch(
            `/api/appointments/available-slots?doctor_id=${props.doctorId}&date=${date}`,
        );

        if (!response.ok) {
            throw new Error("Error al obtener disponibilidad");
        }

        const data = await response.json();

        if (data.message && data.slots.length === 0) {
            errorMessage.value = data.message;
        }

        availableSlots.value = data.slots || [];
    } catch (error) {
        console.error("Error fetching slots:", error);
        errorMessage.value = "Error al cargar los horarios disponibles";
        availableSlots.value = [];
    } finally {
        loadingSlots.value = false;
    }
};

// Select time slot
const selectTimeSlot = (slotTime: string) => {
    selectedTime.value = slotTime;
    // Keep format as "YYYY-MM-DD HH:MM" (with space, not T)
    emit("update:selectedDateTime", slotTime);
};

// Available slots (only showing available times)
const availableTimes = computed(() => {
    return availableSlots.value.filter((slot) => slot.available);
});

// Busy slots (only showing busy times)
const busyTimes = computed(() => {
    return availableSlots.value.filter((slot) => !slot.available);
});

// Format month name
const monthName = computed(() => {
    return currentMonth.value.toLocaleString("es-ES", {
        month: "long",
        year: "numeric",
    });
});

// Get week days header
const weekDays = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];
</script>

<template>
    <div class="rounded-lg border border-gray-300 bg-white p-4">
        <!-- Calendar -->
        <div class="mb-6">
            <div class="mb-4 flex items-center justify-between">
                <button
                    type="button"
                    @click="prevMonth"
                    class="rounded bg-gray-200 p-2 hover:bg-gray-300"
                >
                    ← Anterior
                </button>
                <h2 class="text-lg font-semibold capitalize text-gray-900">
                    {{ monthName }}
                </h2>
                <button
                    type="button"
                    @click="nextMonth"
                    class="rounded bg-gray-200 p-2 hover:bg-gray-300"
                >
                    Siguiente →
                </button>
            </div>

            <!-- Week days header -->
            <div
                class="mb-2 grid grid-cols-7 gap-2 text-center text-sm font-semibold text-gray-600"
            >
                <div v-for="day in weekDays" :key="day" class="py-2">
                    {{ day }}
                </div>
            </div>

            <!-- Calendar grid -->
            <div class="grid grid-cols-7 gap-2">
                <button
                    type="button"
                    v-for="(day, index) in calendarDays"
                    :key="index"
                    :disabled="!day || isPastDate(day)"
                    @click="selectDate(day!)"
                    :class="{
                        'bg-gray-100 text-gray-600   cursor-not-allowed':
                            !day || isPastDate(day),
                        'bg-primary-50  border-2 border-primary-500 text-primary-600  font-bold':
                            day &&
                            selectedDate ===
                                formatDate(
                                    new Date(
                                        currentMonth.getFullYear(),
                                        currentMonth.getMonth(),
                                        day,
                                    ),
                                ),
                        'hover:bg-primary-100  text-gray-900 ':
                            day &&
                            !isPastDate(day) &&
                            selectedDate !==
                                formatDate(
                                    new Date(
                                        currentMonth.getFullYear(),
                                        currentMonth.getMonth(),
                                        day,
                                    ),
                                ),
                    }"
                    class="rounded-lg py-2 text-sm font-semibold transition"
                >
                    {{ day }}
                </button>
            </div>
        </div>

        <!-- Time slots -->
        <div v-if="selectedDate" class="space-y-4">
            <div class="border-t border-gray-300 pt-4">
                <p class="mb-3 text-sm font-semibold text-gray-700">
                    📅 Horarios disponibles para {{ selectedDate }}:
                </p>

                <!-- Loading state -->
                <div v-if="loadingSlots" class="text-center text-gray-500">
                    ⏳ Cargando horarios disponibles...
                </div>

                <!-- Error state -->
                <div
                    v-if="errorMessage"
                    class="rounded-lg bg-danger-50 p-3 text-sm text-danger-600"
                >
                    ⚠️ {{ errorMessage }}
                </div>

                <!-- Available times -->
                <div
                    v-if="availableSlots.length > 0 && !loadingSlots"
                    class="space-y-4"
                >
                    <!-- Available slots -->
                    <div v-if="availableTimes.length > 0">
                        <p class="mb-2 text-xs font-semibold text-success-600">
                            ✓ HORARIOS DISPONIBLES ({{ availableTimes.length }})
                        </p>
                        <div class="grid grid-cols-4 gap-2 sm:grid-cols-6">
                            <button
                                type="button"
                                v-for="slot in availableTimes"
                                :key="slot.time"
                                @click="selectTimeSlot(slot.time)"
                                :class="{
                                    'bg-success-500 text-white':
                                        selectedTime === slot.time,
                                    'bg-success-100 hover:bg-success-200 text-success-800   ':
                                        selectedTime !== slot.time,
                                }"
                                class="rounded-lg px-3 py-2 text-sm font-semibold transition"
                            >
                                {{ slot.display }}
                            </button>
                        </div>
                    </div>

                    <!-- Busy slots -->
                    <div v-if="busyTimes.length > 0">
                        <p class="mb-2 text-xs font-semibold text-gray-600">
                            ✗ HORARIOS OCUPADOS ({{ busyTimes.length }})
                        </p>
                        <div class="grid grid-cols-4 gap-2 sm:grid-cols-6">
                            <button
                                v-for="slot in busyTimes"
                                :key="slot.time"
                                disabled
                                class="cursor-not-allowed rounded-lg bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-500"
                            >
                                {{ slot.display }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- No available slots -->
                <div
                    v-if="
                        availableSlots.length === 0 &&
                        !loadingSlots &&
                        selectedDate
                    "
                    class="rounded-lg bg-warning-50 p-3 text-sm text-warning-600"
                >
                    ⚠️ No hay horarios disponibles para este día. Intenta con
                    otro día.
                </div>
            </div>
        </div>

        <!-- Selected info -->
        <div
            v-if="selectedDate && selectedTime"
            class="mt-4 rounded-lg border-2 border-success-400 bg-success-50 p-3"
        >
            <p class="text-sm font-semibold text-success-800">
                ✓ Fecha y hora seleccionada:
            </p>
            <p class="text-lg font-bold text-success-600">
                {{ selectedDate }} - {{ selectedTime.split(" ")[1] }}
            </p>
        </div>
    </div>
</template>
