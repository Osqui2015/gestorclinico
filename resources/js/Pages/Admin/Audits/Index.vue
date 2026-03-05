<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps<{
    audits: any;
    types: string[];
    users: any[];
    filters: any;
}>();

const page = usePage();

const form = ref({
    type: props.filters.type || "",
    user_id: props.filters.user_id || "",
    event: props.filters.event || "",
    from: props.filters.from || "",
    to: props.filters.to || "",
    perPage: props.filters.perPage || 15,
});

const applyFilters = () => {
    const params = new URLSearchParams();
    Object.entries(form.value).forEach(([k, v]) => {
        if (v !== "" && v != null) params.set(k, String(v));
    });

    window.location.href = `${route("admin.audits.index")}?${params.toString()}`;
};
</script>

<template>
    <Head title="Auditoría" />
    <div class="py-8">
        <div class="mx-auto max-w-5xl">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Registro de Auditoría</h1>
            </div>

            <!-- Filters -->
            <div class="mb-4 rounded-lg border bg-white p-4">
                <div class="grid gap-3 md:grid-cols-6">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600"
                            >Tipo</label
                        >
                        <select
                            v-model="form.type"
                            class="mt-1 w-full rounded border p-2"
                        >
                            <option value="">Todos</option>
                            <option
                                v-for="t in props.types"
                                :key="t"
                                :value="t"
                            >
                                {{ t.split("\\").pop() }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600"
                            >Usuario</label
                        >
                        <select
                            v-model="form.user_id"
                            class="mt-1 w-full rounded border p-2"
                        >
                            <option value="">Todos</option>
                            <option
                                v-for="u in props.users"
                                :key="u.id"
                                :value="u.id"
                            >
                                {{ u.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600"
                            >Evento</label
                        >
                        <select
                            v-model="form.event"
                            class="mt-1 w-full rounded border p-2"
                        >
                            <option value="">Todos</option>
                            <option value="created">created</option>
                            <option value="updated">updated</option>
                            <option value="deleted">deleted</option>
                            <option value="restored">restored</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600"
                            >Desde</label
                        >
                        <input
                            v-model="form.from"
                            type="date"
                            class="mt-1 w-full rounded border p-2"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600"
                            >Hasta</label
                        >
                        <input
                            v-model="form.to"
                            type="date"
                            class="mt-1 w-full rounded border p-2"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600"
                            >Por página</label
                        >
                        <select
                            v-model="form.perPage"
                            class="mt-1 w-full rounded border p-2"
                        >
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3 text-right">
                    <button
                        @click="applyFilters"
                        class="rounded bg-primary-600 px-4 py-2 text-white"
                    >
                        Aplicar
                    </button>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg border">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-3 text-left text-sm font-medium">
                                ID
                            </th>
                            <th class="p-3 text-left text-sm font-medium">
                                Tipo
                            </th>
                            <th class="p-3 text-left text-sm font-medium">
                                Evento
                            </th>
                            <th class="p-3 text-left text-sm font-medium">
                                Usuario
                            </th>
                            <th class="p-3 text-left text-sm font-medium">
                                Fecha
                            </th>
                            <th class="p-3 text-left text-sm font-medium">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="audit in audits.data" :key="audit.id">
                            <td class="p-3 text-sm">{{ audit.id }}</td>
                            <td class="p-3 text-sm">
                                {{ audit.auditable_type.split("\\").pop() }}
                            </td>
                            <td class="p-3 text-sm">{{ audit.event }}</td>
                            <td class="p-3 text-sm">
                                {{ audit.user_id || "Sistema" }}
                            </td>
                            <td class="p-3 text-sm">{{ audit.created_at }}</td>
                            <td class="p-3 text-sm">
                                <Link
                                    :href="route('admin.audits.show', audit.id)"
                                    class="text-primary-600"
                                    >Ver</Link
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <nav class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">
                            Mostrando {{ audits.from }} - {{ audits.to }} de
                            {{ audits.total }}
                        </p>
                    </div>
                    <div>
                        <Link
                            v-if="audits.prev_page_url"
                            :href="audits.prev_page_url"
                            class="mr-2"
                            >Anterior</Link
                        >
                        <Link
                            v-if="audits.next_page_url"
                            :href="audits.next_page_url"
                            >Siguiente</Link
                        >
                    </div>
                </nav>
            </div>
        </div>
    </div>
</template>
