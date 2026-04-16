<script setup lang="ts">
import { usePage, Link } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { echo } from '@/echo';

const page = usePage();
const user = page.props.auth?.user as any;
const isDoctor = user?.role === "doctor";
const isAdmin = user?.role === "admin";
const isSecretary = user?.role === "secretary";
const isPharmacy = user?.role === "pharmacy";
const isOperatingRoomManager = user?.role === "operating_room_manager";
const isNurse = user?.role === "nurse";
const isEmergency = user?.role === "emergency";
const isAccountant = user?.role === "accountant";
const isMaintenance = user?.role === "maintenance";
const isParamedic = user?.role === "paramedic";

const mobileMenuOpen = ref(false);
const realtimeNotifications = ref<
    Array<{ id: number; message: string; requestId: number }>
>([]);

let doctorChannel: any = null;
const notificationTimers = new Map<number, number>();

// Check if user has access to a specific module
const hasModuleAccess = (module: string): boolean => {
    // Admin always has access
    if (isAdmin) return true;

    // If no allowed_modules is set, allow all (backward compatibility)
    if (!user?.allowed_modules || user.allowed_modules.length === 0) {
        return true;
    }

    return user.allowed_modules.includes(module);
};

const menuItems = [
    {
        label: "Pacientes",
        href: "patients.index",
        icon: "👥",
        module: "patients",
    },
    {
        label: "Citas",
        href: "appointments.index",
        icon: "📅",
        module: "appointments",
    },
    {
        label: "Calendario",
        href: "appointments.calendar",
        icon: "📆",
        module: "calendar",
    },
];

const secretaryItems = [
    {
        label: "Gestión de Turnos",
        href: "secretary.turns.index",
        icon: "📋",
        module: "appointments",
    },
    {
        label: "Pre-Internación",
        href: "pre-admissions.index",
        icon: "🟢",
        module: "pre_admissions",
    },
];

const doctorItems = [
    {
        label: "Mi Agenda",
        href: "dashboard",
        icon: "📋",
        module: "appointments",
    },
    {
        label: "Reportes",
        href: "doctor.reports",
        icon: "📊",
        module: "reports",
    },
    {
        label: "Horarios",
        href: "doctor-schedules.index",
        icon: "🕐",
        module: "schedules",
    },
    {
        label: "Excepciones",
        href: "doctor-exceptions.index",
        icon: "🚫",
        module: "schedules",
    },
    {
        label: "Solicitudes Farmacia",
        href: "pharmacy-requests.my-requests",
        icon: "💊",
        module: "pharmacy_requests",
    },
    {
        label: "Quirófanos",
        href: "operations.index",
        icon: "🏥",
        module: "operations",
    },
    {
        label: "Nueva Operación",
        href: "operations.create",
        icon: "➕",
        module: "operations",
    },
    {
        label: "Internación",
        href: "hospitalizations.index",
        icon: "🛏️",
        module: "hospitalizations",
    },
];

const nurseItems = [
    {
        label: "Gestión de Camas",
        href: "hospitalizations.index",
        icon: "🛏️",
        module: "hospitalizations",
    },
    {
        label: "Nueva Internación",
        href: "hospitalizations.create",
        icon: "➕",
        module: "hospitalizations",
    },
    {
        label: "Historial",
        href: "hospitalizations.history",
        icon: "📋",
        module: "hospitalizations",
    },
];

const emergencyItems = [
    {
        label: "Guardia",
        href: "emergency.board",
        icon: "🚑",
        module: "emergency",
    },
    {
        label: "Nueva Admisión",
        href: "emergency.create",
        icon: "➕",
        module: "emergency",
    },
    {
        label: "Historial ER",
        href: "emergency.history",
        icon: "📄",
        module: "emergency",
    },
];

const accountantItems = [
    {
        label: "Cuentas",
        href: "accounting.dashboard",
        icon: "💼",
        module: "accounting",
    },
    {
        label: "Pacientes Cuenta",
        href: "accounting.index",
        icon: "📒",
        module: "accounting",
    },
    {
        label: "Deudores",
        href: "accounting.debtors",
        icon: "📉",
        module: "accounting",
    },
];

const maintenanceItems = [
    {
        label: "Mantenimiento",
        href: "maintenance.index",
        icon: "🔧",
        module: "maintenance",
    },
];

const paramedicItems = [
    {
        label: "Traslados",
        href: "paramedic.dashboard",
        icon: "🚐",
        module: "paramedic",
    },
];

const uniqueByHref = (items: Array<{ href: string } & Record<string, any>>) => {
    const seen = new Set<string>();
    return items.filter((item) => {
        if (seen.has(item.href)) {
            return false;
        }

        seen.add(item.href);
        return true;
    });
};

const pharmacyItems = [
    {
        label: "Dashboard",
        href: "pharmacy.dashboard",
        icon: "💊",
        module: "pharmacy_requests",
    },
    {
        label: "Inventario",
        href: "pharmacy.items.index",
        icon: "📦",
        module: "pharmacy_requests",
    },
    {
        label: "Solicitudes",
        href: "pharmacy.requests.index",
        icon: "📋",
        module: "pharmacy_requests",
    },
];

const operatingRoomManagerItems = [
    {
        label: "Agenda Quirófanos",
        href: "operations.index",
        icon: "🏥",
        module: "operations",
    },
    {
        label: "Nueva Operación",
        href: "operations.create",
        icon: "➕",
        module: "operations",
    },
    {
        label: "Salas Quirófano",
        href: "operations.rooms.settings",
        icon: "⚙️",
        module: "operations",
    },
];

const adminItems = [
    {
        label: "Auditoría",
        href: "admin.audits.index",
        icon: "📋",
        module: "admin",
    },
    {
        label: "Usuarios",
        href: "admin.users.index",
        icon: "👨‍⚕️",
        module: "admin",
    },
    {
        label: "Tablero de Colas",
        href: "admin.queues.index",
        icon: "🏥",
        module: "admin",
    },
    {
        label: "Emergencias",
        href: "emergency.board",
        icon: "🚑",
        module: "emergency",
    },
    {
        label: "Cuentas",
        href: "accounting.dashboard",
        icon: "💼",
        module: "accounting",
    },
    {
        label: "Mantenimiento",
        href: "maintenance.index",
        icon: "🔧",
        module: "maintenance",
    },
    {
        label: "Paramédicos",
        href: "paramedic.dashboard",
        icon: "🚐",
        module: "paramedic",
    },
    {
        label: "Internación",
        href: "hospitalizations.index",
        icon: "🛏️",
        module: "hospitalizations",
    },
    {
        label: "Pre-Internación",
        href: "pre-admissions.index",
        icon: "🟢",
        module: "pre_admissions",
    },
    {
        label: "Quirófanos",
        href: "operations.index",
        icon: "🏥",
        module: "operations",
    },
    {
        label: "Salas Quirófano",
        href: "operations.rooms.settings",
        icon: "⚙️",
        module: "operations",
    },
    {
        label: "Habitaciones y Camas",
        href: "rooms.settings",
        icon: "🛏️",
        module: "hospitalizations",
    },
];

// Filter items based on allowed modules
const visibleItems = uniqueByHref([
    ...(isDoctor
        ? doctorItems.filter((item) => hasModuleAccess(item.module))
        : []),
    ...(isEmergency
        ? emergencyItems.filter((item) => hasModuleAccess(item.module))
        : []),
    ...(isParamedic
        ? paramedicItems.filter((item) => hasModuleAccess(item.module))
        : []),
    ...(isMaintenance
        ? maintenanceItems.filter((item) => hasModuleAccess(item.module))
        : []),
    ...(isAccountant
        ? accountantItems.filter((item) => hasModuleAccess(item.module))
        : []),
    ...(isPharmacy
        ? pharmacyItems.filter((item) => hasModuleAccess(item.module))
        : []),
    ...(isNurse
        ? nurseItems.filter((item) => hasModuleAccess(item.module))
        : []),
    ...(isOperatingRoomManager
        ? operatingRoomManagerItems.filter((item) =>
              hasModuleAccess(item.module),
          )
        : []),
    ...(!isPharmacy
        ? menuItems.filter((item) => hasModuleAccess(item.module))
        : []),
    ...(isSecretary
        ? secretaryItems.filter((item) => hasModuleAccess(item.module))
        : []),
    ...(isAdmin ? adminItems : []),
]);

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
        nurse: "Enfermero/a",
        pharmacy: "Farmacéutico",
        operating_room_manager: "Encargado Quirófano",
        emergency: "Guardia",
        accountant: "Contabilidad",
        maintenance: "Mantenimiento",
        paramedic: "Paramédico",
    };
    return labels[role] || role;
};

const removeNotification = (id: number): void => {
    const timer = notificationTimers.get(id);
    if (timer) {
        window.clearTimeout(timer);
        notificationTimers.delete(id);
    }

    realtimeNotifications.value = realtimeNotifications.value.filter(
        (notification) => notification.id !== id,
    );
};

const pushNotification = (message: string, requestId: number): void => {
    const id = Date.now() + Math.floor(Math.random() * 1000);

    realtimeNotifications.value = [
        ...realtimeNotifications.value,
        { id, message, requestId },
    ];

    const timer = window.setTimeout(() => {
        removeNotification(id);
    }, 6000);

    notificationTimers.set(id, timer);
};

onMounted(() => {
    const echoInstance = echo;

    if (!echoInstance || !user?.id || user?.role !== 'doctor') {
        return;
    }

    doctorChannel = echoInstance.private(`doctor.${user.id}`);
    doctorChannel.listen('.PharmacyRequestCompleted', (event: {
        message?: string;
        pharmacy_request_id?: number;
    }) => {
        pushNotification(
            event.message ?? 'Farmacia completó una solicitud.',
            event.pharmacy_request_id ?? 0,
        );
    });
});

onBeforeUnmount(() => {
    const echoInstance = echo;

    if (!echoInstance || !doctorChannel || !user?.id) {
        return;
    }

    doctorChannel.stopListening('.PharmacyRequestCompleted');
    echoInstance.leaveChannel(`doctor.${user.id}`);
    doctorChannel = null;

    notificationTimers.forEach((timer) => window.clearTimeout(timer));
    notificationTimers.clear();
});
</script>

<template>
    <nav class="overflow-x-hidden border-b border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex min-h-16 items-center justify-between gap-3 py-2">
                <!-- Logo/Brand -->
                <div class="min-w-0">
                    <Link
                        href="/dashboard"
                        class="flex min-w-0 items-center gap-2 text-lg font-bold text-primary-600 hover:text-primary-700 sm:text-xl"
                    >
                        <span class="text-2xl">🏥</span>
                        <span class="truncate">Gestor Clínico</span>
                    </Link>
                </div>

                <!-- User Info & Dropdown -->
                <div class="flex shrink-0 items-center gap-2 sm:gap-4">
                    <div class="relative hidden lg:block">
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
                        class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 lg:hidden"
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

            <!-- Desktop Menu -->
            <div class="hidden w-full flex-wrap gap-2 pb-3 lg:flex">
                <Link
                    v-for="item in visibleItems"
                    :key="item.href"
                    :href="route(item.href)"
                    class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200"
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

            <!-- Mobile Menu -->
            <div
                v-if="mobileMenuOpen"
                class="border-t border-gray-200 py-3 lg:hidden"
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

    <div class="pointer-events-none fixed right-4 top-4 z-50 flex w-full max-w-sm flex-col gap-3">
        <div
            v-for="notification in realtimeNotifications"
            :key="notification.id"
            class="pointer-events-auto rounded-xl border border-emerald-200 bg-white/95 p-4 shadow-xl backdrop-blur"
        >
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-emerald-700">
                        Farmacia completó una solicitud
                    </p>
                    <p class="mt-1 text-sm text-gray-700">
                        {{ notification.message }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500">
                        Solicitud #{{ notification.requestId }}
                    </p>
                </div>
                <button
                    type="button"
                    class="text-gray-400 transition hover:text-gray-700"
                    @click="removeNotification(notification.id)"
                >
                    ✕
                </button>
            </div>
        </div>
    </div>
</template>
