<script setup lang="ts">
interface Props {
    type?: "button" | "submit" | "reset";
}

withDefaults(defineProps<Props>(), {
    type: "button",
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center gap-1.5 text-sm font-semibold text-danger-600 transition-colors hover:text-danger-700 focus:outline-none focus:underline"
    >
        <slot />
    </button>
</template>
