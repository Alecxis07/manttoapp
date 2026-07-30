<script setup lang="ts">
import { useDarkMode } from '@/Composables/useDarkMode';

const { isDark, toggle } = useDarkMode();
</script>

<template>
    <v-btn
        icon
        variant="outlined"
        size="small"
        :aria-label="isDark ? 'Activar modo claro' : 'Activar modo oscuro'"
        :title="isDark ? 'Modo claro' : 'Modo oscuro'"
        @click="toggle"
    >
        <v-icon :icon="isDark ? 'mdi-weather-sunny' : 'mdi-weather-night'" />
    </v-btn>
</template>
