<script setup lang="ts">
import { ref, watch, onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const visible = ref(false);
const message = ref("");
const type = ref<"success" | "error" | "info">("success");

const show = (msg: string, t: typeof type.value = "success") => {
    message.value = msg;
    type.value = t;
    visible.value = true;
    setTimeout(() => (visible.value = false), 4000);
};

watch(
    () => (page.props as any).flash,
    (fl) => {
        if (!fl) return;
        if (fl.success) show(fl.success, "success");
        if (fl.error) show(fl.error, "error");
    },
    { immediate: true },
);

onMounted(() => {
    // in case server set flash before mount
    const fl = (page.props as any).flash;
    if (fl?.success) show(fl.success, "success");
    if (fl?.error) show(fl.error, "error");
});
</script>

<template>
    <transition name="fade">
        <div
            v-if="visible"
            :class="[
                'fixed right-4 top-6 z-50 max-w-sm rounded-md px-4 py-3 shadow-lg',
                type === 'success'
                    ? 'bg-success-600 text-white'
                    : 'bg-danger-600 text-white',
            ]"
        >
            <div class="flex items-center justify-between">
                <div class="text-sm">{{ message }}</div>
                <button
                    @click="visible = false"
                    class="ml-3 text-white/80 hover:text-white"
                >
                    ✕
                </button>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
