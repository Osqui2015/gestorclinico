<script setup lang="ts">
import Navigation from "@/Components/Navigation.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { Link } from "@inertiajs/vue3";
import Toast from "@/Components/Toast.vue";
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-50">
            <!-- New Navigation Component -->
            <Navigation />

            <!-- Page Heading -->
            <header
                class="bg-white border-b border-gray-200"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="py-6">
                <Toast />
                <slot />
            </main>
        </div>
    </div>
</template>
