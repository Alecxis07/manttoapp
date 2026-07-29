<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    logs: Object,
    filters: Object,
    actions: Array,
    entities: Array,
    users: Array,
});

const entity = ref(props.filters.entity ?? '');
const userId = ref(props.filters.user_id ? String(props.filters.user_id) : '');
const action = ref(props.filters.action ?? '');
const from = ref(props.filters.from ?? '');
const to = ref(props.filters.to ?? '');
const selectedLogId = ref(null);

watch([entity, userId, action, from, to], () => {
    router.get(route('audit.index'), {
        entity: entity.value || undefined,
        user_id: userId.value || undefined,
        action: action.value || undefined,
        from: from.value || undefined,
        to: to.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
});

const formatJson = (value) => {
    if (value === null || value === undefined) {
        return '—';
    }

    try {
        return JSON.stringify(value, null, 2);
    } catch {
        return String(value);
    }
};
</script>

<template>
    <AppLayout title="Auditoría">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Visor de auditoría
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white shadow-xl sm:rounded-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Entidad</label>
                            <select
                                v-model="entity"
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            >
                                <option value="">Todas</option>
                                <option
                                    v-for="option in entities"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Usuario</label>
                            <select
                                v-model="userId"
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            >
                                <option value="">Todos</option>
                                <option
                                    v-for="user in users"
                                    :key="user.id"
                                    :value="String(user.id)"
                                >
                                    {{ user.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Acción</label>
                            <select
                                v-model="action"
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            >
                                <option value="">Todas</option>
                                <option
                                    v-for="option in actions"
                                    :key="option"
                                    :value="option"
                                >
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Desde</label>
                            <TextInput v-model="from" type="date" class="w-full text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Hasta</label>
                            <TextInput v-model="to" type="date" class="w-full text-sm" />
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-xl sm:rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entidad</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Detalle</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="log in logs.data" :key="log.id">
                                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                        {{ log.created_at ? new Date(log.created_at).toLocaleString() : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ log.user?.name ?? 'Sistema' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ log.action }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ log.subject_type_label }}
                                        <span v-if="log.subject_id" class="text-gray-400">#{{ log.subject_id }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500 font-mono">{{ log.ip_address ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        <button
                                            type="button"
                                            class="text-indigo-600 hover:text-indigo-800"
                                            @click="selectedLogId = selectedLogId === log.id ? null : log.id"
                                        >
                                            {{ selectedLogId === log.id ? 'Ocultar' : 'Ver' }}
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="logs.data.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">
                                        No hay registros con los filtros seleccionados.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        v-for="log in logs.data.filter((item) => item.id === selectedLogId)"
                        :key="`detail-${log.id}`"
                        class="border-t border-gray-100 bg-gray-50 p-4"
                    >
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Cambios (before / after)</h4>
                        <pre class="text-xs bg-white border border-gray-200 rounded p-3 overflow-x-auto">{{ formatJson(log.properties) }}</pre>
                    </div>

                    <div
                        v-if="logs.links?.length > 3"
                        class="px-4 py-3 border-t border-gray-100 flex flex-wrap gap-2"
                    >
                        <Link
                            v-for="(link, index) in logs.links"
                            :key="index"
                            :href="link.url || '#'"
                            class="px-3 py-1 text-sm rounded border"
                            :class="link.active ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-white border-gray-200 text-gray-600'"
                            v-html="link.label"
                            :preserve-scroll="true"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
