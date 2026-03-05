<script setup lang="ts">
interface Props {
    href?: string;
    type?: "button" | "submit" | "reset";
}

withDefaults(defineProps<Props>(), {
    type: "button",
});
</script>

<template>
    <component
        :is="href ? 'a' : 'button'"
        :href="href"
        :type="!href ? type : undefined"
        class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary-600 transition-colors hover:text-primary-700 focus:outline-none focus:underline"
    >
        <slot />
    </component>
</template>
