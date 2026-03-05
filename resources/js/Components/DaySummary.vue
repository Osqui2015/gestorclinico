<script setup lang="ts">
import { computed } from "vue";

interface Props {
    appointmentsCount: number;
    selectedDate: string;
}

const props = defineProps<Props>();

const parseLocalDate = (dateString: string): Date => {
    const [year, month, day] = dateString.split("-").map(Number);
    return new Date(year, month - 1, day);
};

const dayName = computed(() => {
    return parseLocalDate(props.selectedDate).toLocaleDateString("es-ES", {
        weekday: "long",
        day: "numeric",
        month: "long",
    });
});

const capitalizedDayName = computed(() => {
    const day = dayName.value;
    return day.charAt(0).toUpperCase() + day.slice(1);
});
</script>

<template>
    <div
        class="rounded-xl bg-gradient-to-br from-primary-600 to-primary-800 p-6 text-white shadow-lg"
    >
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold opacity-90">
                    Resumen del Día
                </h3>
                <p class="mt-1 text-sm opacity-75">{{ capitalizedDayName }}</p>
            </div>
            <div
                class="flex h-12 w-12 items-center justify-center rounded-full bg-white bg-opacity-20"
            >
                <svg
                    class="h-6 w-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                    />
                </svg>
            </div>
        </div>

        <div class="mt-6">
            <div class="text-5xl font-bold">
                {{ appointmentsCount }}
            </div>
            <p class="mt-2 text-sm font-medium opacity-90">Citas pendientes</p>
        </div>
    </div>
</template>
