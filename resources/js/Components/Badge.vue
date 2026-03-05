<script setup lang="ts">
interface Props {
    variant?: "success" | "warning" | "danger" | "info" | "gray";
}

const props = withDefaults(defineProps<Props>(), {
    variant: "gray",
});

const variantClasses = {
    success: "bg-success-100 text-success-700",
    warning: "bg-warning-100 text-warning-700",
    danger: "bg-danger-100 text-danger-700",
    info: "bg-primary-100 text-primary-700",
    gray: "bg-gray-100 text-gray-700",
};
</script>

<template>
    <span
        :class="[
            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
            variantClasses[variant],
        ]"
    >
        <slot />
    </span>
</template>
