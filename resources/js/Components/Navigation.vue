<script setup lang="ts">
import { usePage, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

const page = usePage();
const user = page.props.auth?.user;
const isDoctor = user?.role === "doctor";
const isAdmin = user?.role === "admin";
const isSecretary = user?.role === "secretary";

const mobileMenuOpen = ref(false);

const menuItems = [
    { label: "Pacientes", href: "patients.index", icon: "👥" },
    { label: "Citas", href: "appointments.index", icon: "📅" },
    { label: "Calendario", href: "appointments.calendar", icon: "📆" },
];

const secretaryItems = [
    { label: "Gestión de Turnos", href: "secretary.turns.index", icon: "📋" },
];

const doctorItems = [
    { label: "Mi Agenda", href: "dashboard", icon: "📋" },
    { label: "Reportes", href: "doctor.reports", icon: "📊" },
    { label: "Horarios", href: "doctor-schedules.index", icon: "🕐" },
    { label: "Excepciones", href: "doctor-exceptions.index", icon: "🚫" },
];

const adminItems = [
    { label: "Auditoría", href: "admin.audits.index", icon: "📋" },
    { label: "Doctores", href: "admin.users.index", icon: "👨‍⚕️" },
    { label: "Tablero de Colas", href: "admin.queues.index", icon: "🏥" },
];

const visibleItems = [
    ...(isDoctor ? doctorItems : []),
    ...menuItems,
    ...(isSecretary ? secretaryItems : []),
    ...(isAdmin ? adminItems : []),
];

// Get user initials
const getUserInitials = (name: string) => {
    const parts = name.split(" ");
    if (parts.length >= 2) {
        return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
};

// Get role label
const getRoleLabel = (role: string) => {
    const labels: Record<string, string> = {
        doctor: "Doctor",
        admin: "Admin",
        secretary: "Secretaria",
    };
    return labels[role] || role;
};
</script>

<template>
    <nav class="border-b border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between items-center">
                <!-- Logo/Brand -->
                <div class="flex items-center gap-8">
                    <Link
                        href="/dashboard"
                        class="flex items-center gap-2 text-xl font-bold text-primary-600 hover:text-primary-700"
                    >
                        <span class="text-2xl">🏥</span>
                        <span>Gestor Clínico</span>
                    </Link>

                    <!-- Desktop Menu -->
                    <div class="hidden md:flex gap-2">
                        <Link
                            v-for="item in visibleItems"
                            :key="item.href"
                            :href="route(item.href)"
                            class="rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200"
                            :class="
                                route().current() === item.href
                                    ? 'bg-primary-50 text-primary-700 font-semibold'
                                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                            "
                        >
                            <span class="mr-1.5">{{ item.icon }}</span>
                            {{ item.label }}
                        </Link>
                    </div>
                </div>

                <!-- User Info & Dropdown -->
                <div class="flex items-center gap-4">
                    <div class="hidden md:block relative">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors hover:bg-gray-100"
                                >
                                    <div class="text-right hidden sm:block">
                                        <div
                                            class="font-semibold text-gray-900"
                                        >
                                            {{ user?.name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ getRoleLabel(user?.role || "") }}
                                        </div>
                                    </div>
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 text-primary-700 font-semibold"
                                    >
                                        {{ getUserInitials(user?.name || "") }}
                                    </div>
                                    <svg
                                        class="h-4 w-4 text-gray-400"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')">
                                    ⚙️ Perfil
                                </DropdownLink>
                                <DropdownLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                >
                                    🚪 Cerrar sesión
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>

                    <!-- Mobile Menu Toggle -->
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="md:hidden rounded-lg p-2 text-gray-600 hover:bg-gray-100"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                v-if="!mobileMenuOpen"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                            <path
                                v-else
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div
                v-if="mobileMenuOpen"
                class="border-t border-gray-200 py-3 md:hidden"
            >
                <div class="space-y-1">
                    <Link
                        v-for="item in visibleItems"
                        :key="item.href"
                        :href="route(item.href)"
                        class="block rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                        :class="
                            route().current() === item.href
                                ? 'bg-primary-50 text-primary-700'
                                : 'text-gray-700 hover:bg-gray-100'
                        "
                    >
                        <span class="mr-1.5">{{ item.icon }}</span>
                        {{ item.label }}
                    </Link>
                </div>

                <div class="mt-3 border-t border-gray-200 pt-3">
                    <div class="flex items-center gap-3 px-3 py-2">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 text-primary-700 font-semibold"
                        >
                            {{ getUserInitials(user?.name || "") }}
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">
                                {{ user?.name }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ getRoleLabel(user?.role || "") }}
                            </div>
                        </div>
                    </div>
                    <Link
                        :href="route('profile.edit')"
                        class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    >
                        ⚙️ Perfil
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="block w-full text-left rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    >
                        🚪 Cerrar sesión
                    </Link>
                </div>
            </div>
        </div>
    </nav>
</template>
