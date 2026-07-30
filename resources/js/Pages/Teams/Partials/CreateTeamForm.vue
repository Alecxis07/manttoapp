<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import FormSection from '@/Components/Ui/FormSection.vue';
import FormActions from '@/Components/Ui/FormActions.vue';
import { useAppPage } from '@/Composables/useAppPage';

const page = useAppPage();
const authUser = page.props.auth.user!;

const form = useForm({
    name: '',
});

function createTeam(): void {
    form.post(route('teams.store'), {
        errorBag: 'createTeam',
        preserveScroll: true,
    });
}
</script>

<template>
    <FormSection
        title="Team Details"
        description="Create a new team to collaborate with others on projects."
        @submitted="createTeam"
    >
        <template #form>
            <div class="mb-4">
                <div class="text-body-2 font-weight-medium mb-2">Team Owner</div>
                <div class="d-flex align-center ga-3">
                    <v-avatar size="48">
                        <v-img :src="authUser.profile_photo_url" :alt="authUser.name" />
                    </v-avatar>
                    <div>
                        <div class="text-body-1 font-weight-medium">{{ authUser.name }}</div>
                        <div class="text-body-2 text-medium-emphasis">{{ authUser.email }}</div>
                    </div>
                </div>
            </div>

            <v-text-field
                v-model="form.name"
                label="Team Name"
                autofocus
                :error-messages="form.errors.name"
            />
        </template>

        <template #actions>
            <FormActions
                :processing="form.processing"
                :show-cancel="false"
                save-text="Create"
            />
        </template>
    </FormSection>
</template>
