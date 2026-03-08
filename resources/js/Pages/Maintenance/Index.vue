<script setup lang="ts">
import { computed, ref } from "vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Equipment {
    id: number;
    name: string;
    code: string | null;
    category: string;
    brand: string | null;
    model: string | null;
    location: string | null;
    status: string;
    next_maintenance_at: string | null;
    pending_orders_count: number;
}

interface MaintenanceOrder {
    id: number;
    title: string;
    priority: string;
    status: string;
    reported_at: string;
    equipment: {
        id: number;
        name: string;
        code: string | null;
        location: string | null;
        status: string;
    };
    reporter: {
        id: number;
        name: string;
    } | null;
    assignee: {
        id: number;
        name: string;
    } | null;
}

interface Technician {
    id: number;
    name: string;
    role: string;
}

interface Props {
    equipments: {
        data: Equipment[];
        links: PaginationLink[];
        from: number | null;
        to: number | null;
        total: number;
    };
    orders: MaintenanceOrder[];
    technicians: Technician[];
    stats: {
        total_equipments: number;
        operational: number;
        in_maintenance: number;
        maintenance_required: number;
        open_orders: number;
        critical_orders: number;
    };
    filters: {
        status?: string;
        category?: string;
        search?: string;
        order_status?: string;
    };
}

const props = defineProps<Props>();

const filters = ref({
    status: props.filters.status || "",
    category: props.filters.category || "",
    search: props.filters.search || "",
    order_status: props.filters.order_status || "",
});

const equipmentForm = useForm({
    name: "",
    code: "",
    category: "other",
    brand: "",
    model: "",
    serial_number: "",
    location: "",
    status: "operational",
    next_maintenance_at: "",
    notes: "",
});

const orderForm = useForm({
    medical_equipment_id: "",
    title: "",
    description: "",
    priority: "medium",
    assigned_to: "",
});

const equipmentStatus = ref<Record<number, string>>({});
const orderStatus = ref<Record<number, string>>({});
const orderAssignee = ref<Record<number, string>>({});

props.equipments.data.forEach((equipment) => {
    equipmentStatus.value[equipment.id] = equipment.status;
});

props.orders.forEach((order) => {
    orderStatus.value[order.id] = order.status;
    orderAssignee.value[order.id] = order.assignee?.id?.toString() || "";
});

const applyFilters = () => {
    router.get(route("maintenance.index"), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = {
        status: "",
        category: "",
        search: "",
        order_status: "",
    };
    applyFilters();
};

const submitEquipment = () => {
    equipmentForm.post(route("maintenance.equipments.store"), {
        preserveScroll: true,
        onSuccess: () => equipmentForm.reset(),
    });
};

const submitOrder = () => {
    orderForm.post(route("maintenance.orders.store"), {
        preserveScroll: true,
        onSuccess: () => orderForm.reset("title", "description"),
    });
};

const updateEquipmentStatus = (equipmentId: number) => {
    router.patch(
        route("maintenance.equipments.status", equipmentId),
        {
            status: equipmentStatus.value[equipmentId],
        },
        {
            preserveScroll: true,
        },
    );
};

const updateOrderStatus = (orderId: number) => {
    router.patch(
        route("maintenance.orders.status", orderId),
        {
            status: orderStatus.value[orderId],
            assigned_to: orderAssignee.value[orderId] || null,
        },
        {
            preserveScroll: true,
        },
    );
};

const statusBadgeClass = (status: string) => {
    const map: Record<string, string> = {
        operational: "bg-green-100 text-green-800",
        maintenance_required: "bg-yellow-100 text-yellow-800",
        in_maintenance: "bg-blue-100 text-blue-800",
        out_of_service: "bg-red-100 text-red-800",
        open: "bg-amber-100 text-amber-800",
        in_progress: "bg-blue-100 text-blue-800",
        on_hold: "bg-slate-100 text-slate-800",
        completed: "bg-green-100 text-green-800",
        cancelled: "bg-rose-100 text-rose-800",
    };

    return map[status] || "bg-gray-100 text-gray-800";
};

const statusLabel = (status: string) => {
    const map: Record<string, string> = {
        operational: "Operativo",
        maintenance_required: "Requiere mant.",
        in_maintenance: "En mant.",
        out_of_service: "Fuera de servicio",
        open: "Abierta",
        in_progress: "En progreso",
        on_hold: "En espera",
        completed: "Completada",
        cancelled: "Cancelada",
    };

    return map[status] || status;
};

const priorityLabel = (priority: string) => {
    const map: Record<string, string> = {
        low: "Baja",
        medium: "Media",
        high: "Alta",
        critical: "Critica",
    };

    return map[priority] || priority;
};

const priorityClass = (priority: string) => {
    const map: Record<string, string> = {
        low: "text-slate-700",
        medium: "text-blue-700",
        high: "text-orange-700",
        critical: "text-red-700",
    };

    return map[priority] || "text-slate-700";
};
</script>

<template>
    <Head title="Mantenimiento" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">
                    Mantenimiento y Equipos Medicos
                </h1>
                <button
                    class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700"
                    @click="applyFilters"
                >
                    Actualizar
                </button>
            </div>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-xs text-gray-500">Equipos totales</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">
                        {{ stats.total_equipments }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-xs text-gray-500">Operativos</p>
                    <p class="mt-1 text-2xl font-bold text-green-700">
                        {{ stats.operational }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-xs text-gray-500">En mantenimiento</p>
                    <p class="mt-1 text-2xl font-bold text-blue-700">
                        {{ stats.in_maintenance }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-xs text-gray-500">Requieren mantenimiento</p>
                    <p class="mt-1 text-2xl font-bold text-yellow-700">
                        {{ stats.maintenance_required }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-xs text-gray-500">Ordenes abiertas</p>
                    <p class="mt-1 text-2xl font-bold text-amber-700">
                        {{ stats.open_orders }}
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p class="text-xs text-gray-500">Ordenes criticas</p>
                    <p class="mt-1 text-2xl font-bold text-red-700">
                        {{ stats.critical_orders }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">
                        Registrar equipo medico
                    </h2>
                    <form
                        class="grid grid-cols-1 gap-3 md:grid-cols-2"
                        @submit.prevent="submitEquipment"
                    >
                        <input
                            v-model="equipmentForm.name"
                            class="rounded-md border-gray-300 text-sm"
                            placeholder="Nombre"
                            required
                        />
                        <input
                            v-model="equipmentForm.code"
                            class="rounded-md border-gray-300 text-sm"
                            placeholder="Codigo interno"
                        />
                        <select
                            v-model="equipmentForm.category"
                            class="rounded-md border-gray-300 text-sm"
                        >
                            <option value="monitoring">Monitoreo</option>
                            <option value="imaging">Imagenes</option>
                            <option value="life_support">Soporte vital</option>
                            <option value="laboratory">Laboratorio</option>
                            <option value="surgical">Quirurgico</option>
                            <option value="other">Otro</option>
                        </select>
                        <select
                            v-model="equipmentForm.status"
                            class="rounded-md border-gray-300 text-sm"
                        >
                            <option value="operational">Operativo</option>
                            <option value="maintenance_required">
                                Requiere mantenimiento
                            </option>
                            <option value="in_maintenance">
                                En mantenimiento
                            </option>
                            <option value="out_of_service">
                                Fuera de servicio
                            </option>
                        </select>
                        <input
                            v-model="equipmentForm.brand"
                            class="rounded-md border-gray-300 text-sm"
                            placeholder="Marca"
                        />
                        <input
                            v-model="equipmentForm.model"
                            class="rounded-md border-gray-300 text-sm"
                            placeholder="Modelo"
                        />
                        <input
                            v-model="equipmentForm.serial_number"
                            class="rounded-md border-gray-300 text-sm"
                            placeholder="Serie"
                        />
                        <input
                            v-model="equipmentForm.location"
                            class="rounded-md border-gray-300 text-sm"
                            placeholder="Ubicacion"
                        />
                        <input
                            v-model="equipmentForm.next_maintenance_at"
                            type="datetime-local"
                            class="rounded-md border-gray-300 text-sm"
                        />
                        <input
                            v-model="equipmentForm.notes"
                            class="rounded-md border-gray-300 text-sm"
                            placeholder="Notas"
                        />
                        <div class="md:col-span-2">
                            <button
                                class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700"
                                :disabled="equipmentForm.processing"
                            >
                                Guardar equipo
                            </button>
                        </div>
                    </form>
                </div>

                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">
                        Nueva orden de mantenimiento
                    </h2>
                    <form
                        class="grid grid-cols-1 gap-3"
                        @submit.prevent="submitOrder"
                    >
                        <select
                            v-model="orderForm.medical_equipment_id"
                            class="rounded-md border-gray-300 text-sm"
                            required
                        >
                            <option value="">Seleccionar equipo</option>
                            <option
                                v-for="equipment in equipments.data"
                                :key="equipment.id"
                                :value="equipment.id"
                            >
                                {{ equipment.name }}
                                {{
                                    equipment.code ? `(${equipment.code})` : ""
                                }}
                            </option>
                        </select>
                        <input
                            v-model="orderForm.title"
                            class="rounded-md border-gray-300 text-sm"
                            placeholder="Titulo del incidente"
                            required
                        />
                        <textarea
                            v-model="orderForm.description"
                            rows="2"
                            class="rounded-md border-gray-300 text-sm"
                            placeholder="Descripcion"
                        ></textarea>
                        <div class="grid grid-cols-2 gap-3">
                            <select
                                v-model="orderForm.priority"
                                class="rounded-md border-gray-300 text-sm"
                            >
                                <option value="low">Baja</option>
                                <option value="medium">Media</option>
                                <option value="high">Alta</option>
                                <option value="critical">Critica</option>
                            </select>
                            <select
                                v-model="orderForm.assigned_to"
                                class="rounded-md border-gray-300 text-sm"
                            >
                                <option value="">Sin asignar</option>
                                <option
                                    v-for="tech in technicians"
                                    :key="tech.id"
                                    :value="tech.id"
                                >
                                    {{ tech.name }}
                                </option>
                            </select>
                        </div>
                        <button
                            class="rounded-md bg-amber-700 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600"
                            :disabled="orderForm.processing"
                        >
                            Crear orden
                        </button>
                    </form>
                </div>
            </div>

            <div class="rounded-lg bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">
                    Filtros
                </h2>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                    <input
                        v-model="filters.search"
                        class="rounded-md border-gray-300 text-sm"
                        placeholder="Buscar equipo"
                    />
                    <select
                        v-model="filters.status"
                        class="rounded-md border-gray-300 text-sm"
                    >
                        <option value="">Todos los estados</option>
                        <option value="operational">Operativo</option>
                        <option value="maintenance_required">
                            Requiere mantenimiento
                        </option>
                        <option value="in_maintenance">En mantenimiento</option>
                        <option value="out_of_service">
                            Fuera de servicio
                        </option>
                    </select>
                    <select
                        v-model="filters.category"
                        class="rounded-md border-gray-300 text-sm"
                    >
                        <option value="">Todas las categorias</option>
                        <option value="monitoring">Monitoreo</option>
                        <option value="imaging">Imagenes</option>
                        <option value="life_support">Soporte vital</option>
                        <option value="laboratory">Laboratorio</option>
                        <option value="surgical">Quirurgico</option>
                        <option value="other">Otro</option>
                    </select>
                    <div class="flex gap-2">
                        <button
                            class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-600"
                            @click="applyFilters"
                        >
                            Aplicar
                        </button>
                        <button
                            class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-300"
                            @click="clearFilters"
                        >
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 px-5 py-3">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Equipos medicos
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">Equipo</th>
                                <th class="px-4 py-3 text-left">Categoria</th>
                                <th class="px-4 py-3 text-left">Ubicacion</th>
                                <th class="px-4 py-3 text-left">Ordenes</th>
                                <th class="px-4 py-3 text-left">Estado</th>
                                <th class="px-4 py-3 text-left">Accion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="equipment in equipments.data"
                                :key="equipment.id"
                            >
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-900">
                                        {{ equipment.name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ equipment.code || "Sin codigo" }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    {{ equipment.category }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ equipment.location || "-" }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ equipment.pending_orders_count }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'rounded-full px-2 py-1 text-xs font-semibold',
                                            statusBadgeClass(equipment.status),
                                        ]"
                                    >
                                        {{ statusLabel(equipment.status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <select
                                            v-model="
                                                equipmentStatus[equipment.id]
                                            "
                                            class="rounded-md border-gray-300 text-xs"
                                        >
                                            <option value="operational">
                                                Operativo
                                            </option>
                                            <option
                                                value="maintenance_required"
                                            >
                                                Requiere mant.
                                            </option>
                                            <option value="in_maintenance">
                                                En mant.
                                            </option>
                                            <option value="out_of_service">
                                                Fuera de serv.
                                            </option>
                                        </select>
                                        <button
                                            class="rounded bg-slate-800 px-2 py-1 text-xs font-semibold text-white"
                                            @click="
                                                updateEquipmentStatus(
                                                    equipment.id,
                                                )
                                            "
                                        >
                                            Guardar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div
                    class="flex flex-wrap gap-2 border-t border-gray-200 px-4 py-3"
                >
                    <a
                        v-for="link in equipments.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="[
                            'rounded px-3 py-1 text-sm',
                            link.active
                                ? 'bg-slate-800 text-white'
                                : 'bg-gray-200 text-gray-800',
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>

            <div class="rounded-lg bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 px-5 py-3">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Ordenes de mantenimiento
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">Orden</th>
                                <th class="px-4 py-3 text-left">Equipo</th>
                                <th class="px-4 py-3 text-left">Prioridad</th>
                                <th class="px-4 py-3 text-left">Estado</th>
                                <th class="px-4 py-3 text-left">Asignado</th>
                                <th class="px-4 py-3 text-left">Accion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="order in orders" :key="order.id">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-900">
                                        {{ order.title }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{
                                            new Date(
                                                order.reported_at,
                                            ).toLocaleString()
                                        }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <p>{{ order.equipment.name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ order.equipment.location || "-" }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'font-semibold',
                                            priorityClass(order.priority),
                                        ]"
                                    >
                                        {{ priorityLabel(order.priority) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'rounded-full px-2 py-1 text-xs font-semibold',
                                            statusBadgeClass(order.status),
                                        ]"
                                    >
                                        {{ statusLabel(order.status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <select
                                        v-model="orderAssignee[order.id]"
                                        class="rounded-md border-gray-300 text-xs"
                                    >
                                        <option value="">Sin asignar</option>
                                        <option
                                            v-for="tech in technicians"
                                            :key="tech.id"
                                            :value="tech.id"
                                        >
                                            {{ tech.name }}
                                        </option>
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <select
                                            v-model="orderStatus[order.id]"
                                            class="rounded-md border-gray-300 text-xs"
                                        >
                                            <option value="open">
                                                Abierta
                                            </option>
                                            <option value="in_progress">
                                                En progreso
                                            </option>
                                            <option value="on_hold">
                                                En espera
                                            </option>
                                            <option value="completed">
                                                Completada
                                            </option>
                                            <option value="cancelled">
                                                Cancelada
                                            </option>
                                        </select>
                                        <button
                                            class="rounded bg-blue-700 px-2 py-1 text-xs font-semibold text-white"
                                            @click="updateOrderStatus(order.id)"
                                        >
                                            Guardar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="orders.length === 0">
                                <td
                                    class="px-4 py-6 text-center text-gray-500"
                                    colspan="6"
                                >
                                    No hay ordenes registradas.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
