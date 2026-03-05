<script setup lang="ts">
import { computed, ref, watch } from "vue";

interface Props {
    selectedDate: string;
}

interface Emits {
    (e: "select-date", date: string): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const parseLocalDate = (dateString: string): Date => {
    const [year, month, day] = dateString.split("-").map(Number);
    return new Date(year, month - 1, day);
};

const currentDate = ref(parseLocalDate(props.selectedDate));

watch(
    () => props.selectedDate,
    (newValue) => {
        currentDate.value = parseLocalDate(newValue);
    },
);

const daysInMonth = computed(() => {
    return new Date(
        currentDate.value.getFullYear(),
        currentDate.value.getMonth() + 1,
        0,
    ).getDate();
});

const firstDayOfMonth = computed(() => {
    const dayIndex = new Date(
        currentDate.value.getFullYear(),
        currentDate.value.getMonth(),
        1,
    ).getDay();

    // Convert JS day index (Sunday=0) to Monday-first index (Monday=0, Sunday=6)
    return dayIndex === 0 ? 6 : dayIndex - 1;
});

const monthName = computed(() => {
    return currentDate.value.toLocaleString("es-ES", {
        month: "long",
        year: "numeric",
    });
});

const weeks = computed(() => {
    const weeks = [];
    let week = [];

    // Add empty cells for days before month starts
    for (let i = 0; i < firstDayOfMonth.value; i++) {
        week.push(null);
    }

    // Add days of month
    for (let day = 1; day <= daysInMonth.value; day++) {
        week.push(day);
        if (week.length === 7) {
            weeks.push(week);
            week = [];
        }
    }

    // Fill last week with empty cells if needed
    if (week.length > 0) {
        while (week.length < 7) {
            week.push(null);
        }
        weeks.push(week);
    }

    return weeks;
});

const isToday = (day: number | null): boolean => {
    if (!day) return false;
    const today = new Date();
    return (
        day === today.getDate() &&
        currentDate.value.getMonth() === today.getMonth() &&
        currentDate.value.getFullYear() === today.getFullYear()
    );
};

const isSelected = (day: number | null): boolean => {
    if (!day) return false;
    const year = currentDate.value.getFullYear();
    const month = String(currentDate.value.getMonth() + 1).padStart(2, "0");
    const dayStr = String(day).padStart(2, "0");
    const dayDateString = `${year}-${month}-${dayStr}`;
    return dayDateString === props.selectedDate;
};

const selectDay = (day: number | null) => {
    if (!day) return;
    const year = currentDate.value.getFullYear();
    const month = String(currentDate.value.getMonth() + 1).padStart(2, "0");
    const dayStr = String(day).padStart(2, "0");
    const dateString = `${year}-${month}-${dayStr}`;
    emit("select-date", dateString);
};

const previousMonth = () => {
    currentDate.value = new Date(
        currentDate.value.getFullYear(),
        currentDate.value.getMonth() - 1,
    );
};

const nextMonth = () => {
    currentDate.value = new Date(
        currentDate.value.getFullYear(),
        currentDate.value.getMonth() + 1,
    );
};
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <button
                type="button"
                @click="previousMonth"
                class="rounded-lg p-1.5 text-gray-600 transition-colors hover:bg-gray-100"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>
            </button>
            <h3 class="text-base font-bold capitalize text-gray-900">
                {{ monthName }}
            </h3>
            <button
                type="button"
                @click="nextMonth"
                class="rounded-lg p-1.5 text-gray-600 transition-colors hover:bg-gray-100"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5l7 7-7 7"
                    />
                </svg>
            </button>
        </div>

        <!-- Days of week header -->
        <div class="mb-2 grid grid-cols-7 gap-1">
            <div class="text-center text-xs font-semibold text-gray-500">L</div>
            <div class="text-center text-xs font-semibold text-gray-500">M</div>
            <div class="text-center text-xs font-semibold text-gray-500">M</div>
            <div class="text-center text-xs font-semibold text-gray-500">J</div>
            <div class="text-center text-xs font-semibold text-gray-500">V</div>
            <div class="text-center text-xs font-semibold text-gray-500">S</div>
            <div class="text-center text-xs font-semibold text-gray-500">D</div>
        </div>

        <!-- Calendar days -->
        <div class="space-y-1">
            <div
                v-for="(week, weekIdx) in weeks"
                :key="weekIdx"
                class="grid grid-cols-7 gap-1"
            >
                <button
                    type="button"
                    v-for="(day, dayIdx) in week"
                    :key="dayIdx"
                    @click="selectDay(day)"
                    :disabled="!day"
                    :class="[
                        'flex items-center justify-center h-8 w-full text-sm font-medium rounded-lg transition-all',
                        day ? 'cursor-pointer' : 'cursor-default',
                        isSelected(day)
                            ? 'bg-primary-600 text-white font-bold shadow-sm'
                            : isToday(day)
                              ? 'bg-primary-50 text-primary-700 font-bold border-2 border-primary-600'
                              : 'text-gray-700 hover:bg-gray-100',
                        !day && 'text-transparent',
                    ]"
                >
                    {{ day }}
                </button>
            </div>
        </div>
    </div>
</template>
