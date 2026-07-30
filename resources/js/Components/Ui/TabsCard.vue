<script setup lang="ts">
import { computed, ref, watch } from 'vue';

export interface TabItem {
    key: string;
    title: string;
    icon?: string;
    disabled?: boolean;
}

const props = withDefaults(
    defineProps<{
        tabs: TabItem[];
        modelValue?: string;
    }>(),
    {
        modelValue: undefined,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const internal = ref(props.modelValue ?? props.tabs[0]?.key ?? '');

watch(
    () => props.modelValue,
    (value) => {
        if (value !== undefined) {
            internal.value = value;
        }
    },
);

watch(internal, (value) => {
    emit('update:modelValue', value);
});

const tabKeys = computed(() => props.tabs.map((tab) => tab.key));
</script>

<template>
    <v-card>
        <v-tabs
            v-model="internal"
            color="primary"
            density="comfortable"
            show-arrows
        >
            <v-tab
                v-for="tab in tabs"
                :key="tab.key"
                :value="tab.key"
                :disabled="tab.disabled"
                :prepend-icon="tab.icon"
            >
                {{ tab.title }}
            </v-tab>
        </v-tabs>
        <v-divider />
        <v-tabs-window v-model="internal">
            <v-tabs-window-item
                v-for="key in tabKeys"
                :key="key"
                :value="key"
            >
                <div class="pa-4">
                    <slot :name="key" />
                </div>
            </v-tabs-window-item>
        </v-tabs-window>
    </v-card>
</template>
