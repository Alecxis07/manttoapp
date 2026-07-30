<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';
import DeleteTeamForm from '@/Pages/Teams/Partials/DeleteTeamForm.vue';
import TeamMemberManager from '@/Pages/Teams/Partials/TeamMemberManager.vue';
import UpdateTeamNameForm from '@/Pages/Teams/Partials/UpdateTeamNameForm.vue';

interface TeamRole {
    key: string;
    name: string;
    description: string;
}

interface TeamMember {
    id: number;
    name: string;
    profile_photo_url?: string;
    membership: {
        role: string;
    };
}

interface TeamInvitation {
    id: number;
    email: string;
}

interface Team {
    id: number;
    name: string;
    personal_team?: boolean;
    owner: {
        name: string;
        email: string;
        profile_photo_url?: string;
    };
    users: TeamMember[];
    team_invitations: TeamInvitation[];
}

defineProps<{
    team: Team;
    availableRoles: TeamRole[];
    permissions: {
        canDeleteTeam?: boolean;
        canUpdateTeam?: boolean;
        canAddTeamMembers?: boolean;
        canUpdateTeamMembers?: boolean;
        canRemoveTeamMembers?: boolean;
    };
}>();
</script>

<template>
    <AppLayout title="Team Settings">
        <PageHeader
            title="Team Settings"
            :subtitle="team.name"
        />

        <div class="d-flex flex-column ga-8">
            <UpdateTeamNameForm :team="team" :permissions="permissions" />

            <TeamMemberManager
                :team="team"
                :available-roles="availableRoles"
                :user-permissions="permissions"
            />

            <DeleteTeamForm
                v-if="permissions.canDeleteTeam && !team.personal_team"
                :team="team"
            />
        </div>
    </AppLayout>
</template>
