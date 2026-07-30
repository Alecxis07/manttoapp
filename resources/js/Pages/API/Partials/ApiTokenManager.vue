<script setup lang="ts">
import { computed, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import ActionSection from '@/Components/Ui/ActionSection.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';
import FormActions from '@/Components/Ui/FormActions.vue';
import FormDialog from '@/Components/Ui/FormDialog.vue';
import FormSection from '@/Components/Ui/FormSection.vue';

type ApiToken = {
    id: number;
    name: string;
    abilities: string[];
    last_used_ago?: string | null;
};

const props = defineProps<{
    tokens: ApiToken[];
    availablePermissions: string[];
    defaultPermissions: string[];
}>();

const page = usePage();

const createApiTokenForm = useForm({
    name: '',
    permissions: [...props.defaultPermissions],
});

const updateApiTokenForm = useForm({
    permissions: [] as string[],
});

const deleteApiTokenForm = useForm({});

const displayingToken = ref(false);
const managingPermissionsFor = ref<ApiToken | null>(null);
const apiTokenBeingDeleted = ref<ApiToken | null>(null);

const flashToken = computed(() => {
    const jetstream = page.props.jetstream as { flash?: { token?: string } } | undefined;

    return jetstream?.flash?.token;
});

const permissionsDialogOpen = computed({
    get: () => managingPermissionsFor.value !== null,
    set: (open: boolean) => {
        if (!open) {
            managingPermissionsFor.value = null;
        }
    },
});

const deleteDialogOpen = computed({
    get: () => apiTokenBeingDeleted.value !== null,
    set: (open: boolean) => {
        if (!open) {
            apiTokenBeingDeleted.value = null;
        }
    },
});

function createApiToken(): void {
    createApiTokenForm.post(route('api-tokens.store'), {
        preserveScroll: true,
        onSuccess: () => {
            displayingToken.value = true;
            createApiTokenForm.reset();
            createApiTokenForm.permissions = [...props.defaultPermissions];
        },
    });
}

function manageApiTokenPermissions(token: ApiToken): void {
    updateApiTokenForm.permissions = [...token.abilities];
    managingPermissionsFor.value = token;
}

function updateApiToken(): void {
    if (!managingPermissionsFor.value) {
        return;
    }

    updateApiTokenForm.put(route('api-tokens.update', managingPermissionsFor.value), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            managingPermissionsFor.value = null;
        },
    });
}

function confirmApiTokenDeletion(token: ApiToken): void {
    apiTokenBeingDeleted.value = token;
}

function deleteApiToken(): void {
    if (!apiTokenBeingDeleted.value) {
        return;
    }

    deleteApiTokenForm.delete(route('api-tokens.destroy', apiTokenBeingDeleted.value), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            apiTokenBeingDeleted.value = null;
        },
    });
}
</script>

<template>
    <div class="d-flex flex-column ga-8">
        <FormSection
            title="Create API Token"
            description="API tokens allow third-party services to authenticate with our application on your behalf."
            @submitted="createApiToken"
        >
            <template #form>
                <v-text-field
                    v-model="createApiTokenForm.name"
                    label="Name"
                    autofocus
                    class="mb-3"
                    :error-messages="createApiTokenForm.errors.name"
                />

                <div v-if="availablePermissions.length > 0">
                    <p class="text-body-2 font-weight-medium mb-2">
                        Permissions
                    </p>
                    <v-row dense>
                        <v-col
                            v-for="permission in availablePermissions"
                            :key="permission"
                            cols="12"
                            md="6"
                        >
                            <v-checkbox
                                v-model="createApiTokenForm.permissions"
                                :label="permission"
                                :value="permission"
                                hide-details
                                density="comfortable"
                            />
                        </v-col>
                    </v-row>
                </div>
            </template>

            <template #actions>
                <FormActions
                    :processing="createApiTokenForm.processing"
                    :show-cancel="false"
                    save-text="Create"
                >
                    <template #prepend>
                        <v-fade-transition>
                            <span
                                v-if="createApiTokenForm.recentlySuccessful"
                                class="text-success text-body-2 me-2"
                            >
                                Created.
                            </span>
                        </v-fade-transition>
                    </template>
                </FormActions>
            </template>
        </FormSection>

        <ActionSection
            v-if="tokens.length > 0"
            title="Manage API Tokens"
            description="You may delete any of your existing tokens if they are no longer needed."
        >
            <template #content>
                <div class="d-flex flex-column ga-4">
                    <div
                        v-for="token in tokens"
                        :key="token.id"
                        class="d-flex flex-wrap align-center justify-space-between ga-2"
                    >
                        <div class="text-body-1 text-break">
                            {{ token.name }}
                        </div>

                        <div class="d-flex flex-wrap align-center ga-2">
                            <span
                                v-if="token.last_used_ago"
                                class="text-body-2 text-medium-emphasis"
                            >
                                Last used {{ token.last_used_ago }}
                            </span>

                            <v-btn
                                v-if="availablePermissions.length > 0"
                                variant="text"
                                size="small"
                                @click="manageApiTokenPermissions(token)"
                            >
                                Permissions
                            </v-btn>

                            <v-btn
                                color="error"
                                variant="text"
                                size="small"
                                @click="confirmApiTokenDeletion(token)"
                            >
                                Delete
                            </v-btn>
                        </div>
                    </div>
                </div>
            </template>
        </ActionSection>

        <FormDialog
            v-model="displayingToken"
            title="API Token"
            :persistent="false"
            max-width="520"
            cancel-text="Close"
            :hide-actions="false"
            @cancel="displayingToken = false"
        >
            <p class="text-body-2 text-medium-emphasis mb-4">
                Please copy your new API token. For your security, it won't be shown again.
            </p>

            <v-sheet
                v-if="flashToken"
                border
                rounded="lg"
                class="pa-3 font-mono text-body-2 text-break"
            >
                {{ flashToken }}
            </v-sheet>

            <template #actions>
                <v-spacer />
                <v-btn variant="text" @click="displayingToken = false">
                    Close
                </v-btn>
            </template>
        </FormDialog>

        <FormDialog
            v-model="permissionsDialogOpen"
            title="API Token Permissions"
            save-text="Save"
            :loading="updateApiTokenForm.processing"
            max-width="520"
            @save="updateApiToken"
            @cancel="managingPermissionsFor = null"
        >
            <v-row dense>
                <v-col
                    v-for="permission in availablePermissions"
                    :key="permission"
                    cols="12"
                    md="6"
                >
                    <v-checkbox
                        v-model="updateApiTokenForm.permissions"
                        :label="permission"
                        :value="permission"
                        hide-details
                        density="comfortable"
                    />
                </v-col>
            </v-row>
        </FormDialog>

        <ConfirmDialog
            v-model="deleteDialogOpen"
            title="Delete API Token"
            message="Are you sure you would like to delete this API token?"
            confirm-text="Delete"
            confirm-color="error"
            :loading="deleteApiTokenForm.processing"
            @confirm="deleteApiToken"
            @cancel="apiTokenBeingDeleted = null"
        />
    </div>
</template>
