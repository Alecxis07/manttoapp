<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const model = defineModel<string>({ default: '' });

const searching = ref(false);

function submit(): void {
    const q = model.value.trim();

    if (!q) {
        return;
    }

    searching.value = true;
    router.get(
        route('search'),
        { q },
        {
            preserveState: false,
            onFinish: () => {
                searching.value = false;
            },
        },
    );
}
</script>

<template>
    <v-text-field
        v-model="model"
        density="compact"
        hide-details
        clearable
        type="search"
        placeholder="Buscar folio, placa…"
        prepend-inner-icon="mdi-magnify"
        style="max-width: 16rem"
        :loading="searching"
        aria-label="Búsqueda global"
        @keydown.enter.prevent="submit"
        @click:clear="model = ''"
    />
</template>
