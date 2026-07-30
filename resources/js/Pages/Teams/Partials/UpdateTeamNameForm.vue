<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import FormSection from '@/Components/Ui/FormSection.vue';
import FormActions from '@/Components/Ui/FormActions.vue';

interface TeamOwner {
    name: string;
    email: string;
    profile_photo_url?: string;
}

interface Team {
    id: number;
    name: string;
    owner: TeamOwner;
}

const props = defineProps<{
    team: Team;
    permissions: {
        canUpdateTeam?: boolean;
    };
}>();

const form = useForm({
    name: props.team.name,
});

function updateTeamName(): void {
    form.put(route('teams.update', props.team), {
        errorBag: 'updateTeamName',
        preserveScroll: true,
    });
}
</script>

<template>
    <FormSection
        title="Team Name"
        description="The team's name and owner information."
        @submitted="updateTeamName"
    >
        <template #form>
            <div class="mb-4">
                <div class="text-body-2 font-weight-medium mb-2">Team Owner</div>
                <div class="d-flex align-center ga-3">
                    <v-avatar size="48">
                        <v-img :src="team.owner.profile_photo_url" :alt="team.owner.name" />
                    </v-avatar>
                    <div>
                        <div class="text-body-1 font-weight-medium">{{ team.owner.name }}</div>
                        <div class="text-body-2 text-medium-emphasis">{{ team.owner.email }}</div>
                    </div>
                </div>
            </div>

            <v-text-field
                v-model="form.name"
                label="Team Name"
                :disabled="!permissions.canUpdateTeam"
                :error-messages="form.errors.name"
            />
        </template>

        <template v-if="permissions.canUpdateTeam" #actions>
            <FormActions
                :processing="form.processing"
                :show-cancel="false"
                save-text="Save"
            >
                <template #prepend>
                    <v-fade-transition>
                        <span
                            v-if="form.recentlySuccessful"
                            class="text-success text-body-2 me-2"
                        >
                            Saved.
                        </span>
                    </v-fade-transition>
                </template>
            </FormActions>
        </template>
    </FormSection>
</template>
