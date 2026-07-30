<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionSection from '@/Components/Ui/ActionSection.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';

const props = defineProps<{
    team: { id: number; name?: string };
}>();

const confirmingTeamDeletion = ref(false);
const form = useForm({});

function confirmTeamDeletion(): void {
    confirmingTeamDeletion.value = true;
}

function deleteTeam(): void {
    form.delete(route('teams.destroy', props.team), {
        errorBag: 'deleteTeam',
    });
}
</script>

<template>
    <ActionSection
        title="Delete Team"
        description="Permanently delete this team."
    >
        <template #content>
            <p class="text-body-2 text-medium-emphasis mb-4">
                Once a team is deleted, all of its resources and data will be permanently deleted. Before deleting this team, please download any data or information regarding this team that you wish to retain.
            </p>

            <v-btn color="error" variant="flat" @click="confirmTeamDeletion">
                Delete Team
            </v-btn>

            <ConfirmDialog
                v-model="confirmingTeamDeletion"
                title="Delete Team"
                message="Are you sure you want to delete this team? Once a team is deleted, all of its resources and data will be permanently deleted."
                confirm-text="Delete Team"
                confirm-color="error"
                :loading="form.processing"
                @confirm="deleteTeam"
            />
        </template>
    </ActionSection>
</template>
