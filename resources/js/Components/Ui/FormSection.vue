<script setup lang="ts">
withDefaults(
    defineProps<{
        title: string;
        description?: string;
    }>(),
    {
        description: undefined,
    },
);

const emit = defineEmits<{
    submitted: [];
}>();
</script>

<template>
    <v-row>
        <v-col cols="12" md="4">
            <h3 class="text-subtitle-1 font-weight-bold mb-1">
                {{ title }}
            </h3>
            <p v-if="description" class="text-body-2 text-medium-emphasis mb-0">
                {{ description }}
            </p>
            <slot name="aside" />
        </v-col>
        <v-col cols="12" md="8">
            <v-card>
                <form @submit.prevent="emit('submitted')">
                    <v-card-text>
                        <slot name="form" />
                        <slot />
                    </v-card-text>
                    <v-card-actions v-if="$slots.actions" class="px-4 pb-4">
                        <v-spacer />
                        <slot name="actions" />
                    </v-card-actions>
                </form>
            </v-card>
        </v-col>
    </v-row>
</template>
