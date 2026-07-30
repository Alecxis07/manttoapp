<script setup lang="ts">
withDefaults(
    defineProps<{
        title: string;
        value: string | number;
        icon?: string;
        trend?: string;
        trendDirection?: 'up' | 'down' | 'neutral';
        subtitle?: string;
    }>(),
    {
        icon: 'mdi-chart-box-outline',
        trend: undefined,
        trendDirection: 'neutral',
        subtitle: undefined,
    },
);

const trendColor = (direction: 'up' | 'down' | 'neutral'): string => {
    if (direction === 'up') {
        return 'success';
    }

    if (direction === 'down') {
        return 'error';
    }

    return 'medium-emphasis';
};

const trendIcon = (direction: 'up' | 'down' | 'neutral'): string => {
    if (direction === 'up') {
        return 'mdi-trending-up';
    }

    if (direction === 'down') {
        return 'mdi-trending-down';
    }

    return 'mdi-minus';
};
</script>

<template>
    <v-card class="h-100">
        <v-card-text class="d-flex align-start justify-space-between ga-3">
            <div class="min-w-0">
                <div class="text-caption text-medium-emphasis text-uppercase mb-1">
                    {{ title }}
                </div>
                <div class="text-h4 font-weight-bold">
                    {{ value }}
                </div>
                <div v-if="subtitle" class="text-body-2 text-medium-emphasis mt-1">
                    {{ subtitle }}
                </div>
                <div
                    v-if="trend"
                    class="d-flex align-center ga-1 mt-2 text-body-2"
                    :class="`text-${trendColor(trendDirection)}`"
                >
                    <v-icon :icon="trendIcon(trendDirection)" size="small" />
                    <span>{{ trend }}</span>
                </div>
            </div>
            <v-avatar color="primary" variant="tonal" rounded="lg" size="44">
                <v-icon :icon="icon" />
            </v-avatar>
        </v-card-text>
    </v-card>
</template>
