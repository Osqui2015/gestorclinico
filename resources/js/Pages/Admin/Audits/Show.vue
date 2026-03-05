<script setup lang="ts">
const props = defineProps<{ audit: any }>();
</script>

<template>
    <div class="py-8">
        <div class="mx-auto max-w-3xl">
            <h1 class="text-xl font-semibold mb-4">Audit #{{ audit.id }}</h1>

            <div class="rounded-lg border bg-white p-4">
                <p><strong>Tipo:</strong> {{ audit.auditable_type }}</p>
                <p><strong>ID:</strong> {{ audit.auditable_id }}</p>
                <p><strong>Evento:</strong> {{ audit.event }}</p>
                <p>
                    <strong>Usuario:</strong> {{ audit.user_id || "Sistema" }}
                </p>
                <p class="mt-3 text-sm"><strong>Old:</strong></p>
                <pre class="whitespace-pre-wrap text-sm">{{
                    JSON.stringify(audit.old_values, null, 2)
                }}</pre>
                <p class="mt-3 text-sm"><strong>New:</strong></p>
                <pre class="whitespace-pre-wrap text-sm">{{
                    JSON.stringify(audit.new_values, null, 2)
                }}</pre>
            </div>
        </div>
    </div>
</template>
