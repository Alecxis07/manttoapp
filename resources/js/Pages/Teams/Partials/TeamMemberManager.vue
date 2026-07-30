<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import ActionSection from '@/Components/Ui/ActionSection.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';
import FormSection from '@/Components/Ui/FormSection.vue';
import FormActions from '@/Components/Ui/FormActions.vue';
import FormDialog from '@/Components/Ui/FormDialog.vue';
import { useAppPage } from '@/Composables/useAppPage';

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
    users: TeamMember[];
    team_invitations: TeamInvitation[];
}

const props = defineProps<{
    team: Team;
    availableRoles: TeamRole[];
    userPermissions: {
        canAddTeamMembers?: boolean;
        canUpdateTeamMembers?: boolean;
        canRemoveTeamMembers?: boolean;
    };
}>();

const page = useAppPage();
const authUser = page.props.auth.user!;

const addTeamMemberForm = useForm({
    email: '',
    role: null as string | null,
});

const updateRoleForm = useForm({
    role: null as string | null,
});

const leaveTeamForm = useForm({});
const removeTeamMemberForm = useForm({});

const currentlyManagingRole = ref(false);
const managingRoleFor = ref<TeamMember | null>(null);
const confirmingLeavingTeam = ref(false);
const teamMemberBeingRemoved = ref<TeamMember | null>(null);
const confirmingMemberRemoval = computed({
    get: () => teamMemberBeingRemoved.value !== null,
    set: (value: boolean) => {
        if (!value) {
            teamMemberBeingRemoved.value = null;
        }
    },
});

function addTeamMember(): void {
    addTeamMemberForm.post(route('team-members.store', props.team), {
        errorBag: 'addTeamMember',
        preserveScroll: true,
        onSuccess: () => addTeamMemberForm.reset(),
    });
}

function cancelTeamInvitation(invitation: TeamInvitation): void {
    router.delete(route('team-invitations.destroy', invitation), {
        preserveScroll: true,
    });
}

function manageRole(teamMember: TeamMember): void {
    managingRoleFor.value = teamMember;
    updateRoleForm.role = teamMember.membership.role;
    currentlyManagingRole.value = true;
}

function updateRole(): void {
    if (!managingRoleFor.value) {
        return;
    }

    updateRoleForm.put(route('team-members.update', [props.team, managingRoleFor.value]), {
        preserveScroll: true,
        onSuccess: () => {
            currentlyManagingRole.value = false;
        },
    });
}

function confirmLeavingTeam(): void {
    confirmingLeavingTeam.value = true;
}

function leaveTeam(): void {
    leaveTeamForm.delete(route('team-members.destroy', [props.team, authUser]));
}

function confirmTeamMemberRemoval(teamMember: TeamMember): void {
    teamMemberBeingRemoved.value = teamMember;
}

function removeTeamMember(): void {
    if (!teamMemberBeingRemoved.value) {
        return;
    }

    removeTeamMemberForm.delete(
        route('team-members.destroy', [props.team, teamMemberBeingRemoved.value]),
        {
            errorBag: 'removeTeamMember',
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                teamMemberBeingRemoved.value = null;
            },
        },
    );
}

function displayableRole(role: string): string {
    return props.availableRoles.find((r) => r.key === role)?.name ?? role;
}
</script>

<template>
    <div class="d-flex flex-column ga-8">
        <FormSection
            v-if="userPermissions.canAddTeamMembers"
            title="Add Team Member"
            description="Add a new team member to your team, allowing them to collaborate with you."
            @submitted="addTeamMember"
        >
            <template #form>
                <p class="text-body-2 text-medium-emphasis mb-4">
                    Please provide the email address of the person you would like to add to this team.
                </p>

                <v-text-field
                    v-model="addTeamMemberForm.email"
                    label="Email"
                    type="email"
                    class="mb-3"
                    :error-messages="addTeamMemberForm.errors.email"
                />

                <div v-if="availableRoles.length > 0">
                    <div class="text-body-2 font-weight-medium mb-2">Role</div>
                    <div
                        v-if="addTeamMemberForm.errors.role"
                        class="text-error text-caption mb-2"
                    >
                        {{ addTeamMemberForm.errors.role }}
                    </div>
                    <v-list border rounded="lg" class="bg-transparent">
                        <v-list-item
                            v-for="role in availableRoles"
                            :key="role.key"
                            :active="addTeamMemberForm.role === role.key"
                            color="primary"
                            @click="addTeamMemberForm.role = role.key"
                        >
                            <v-list-item-title class="font-weight-medium">
                                {{ role.name }}
                            </v-list-item-title>
                            <v-list-item-subtitle>
                                {{ role.description }}
                            </v-list-item-subtitle>
                            <template #append>
                                <v-icon
                                    v-if="addTeamMemberForm.role === role.key"
                                    icon="mdi-check-circle"
                                    color="success"
                                />
                            </template>
                        </v-list-item>
                    </v-list>
                </div>
            </template>

            <template #actions>
                <FormActions
                    :processing="addTeamMemberForm.processing"
                    :show-cancel="false"
                    save-text="Add"
                >
                    <template #prepend>
                        <v-fade-transition>
                            <span
                                v-if="addTeamMemberForm.recentlySuccessful"
                                class="text-success text-body-2 me-2"
                            >
                                Added.
                            </span>
                        </v-fade-transition>
                    </template>
                </FormActions>
            </template>
        </FormSection>

        <ActionSection
            v-if="team.team_invitations.length > 0 && userPermissions.canAddTeamMembers"
            title="Pending Team Invitations"
            description="These people have been invited to your team and have been sent an invitation email. They may join the team by accepting the email invitation."
        >
            <template #content>
                <v-list class="bg-transparent">
                    <v-list-item
                        v-for="invitation in team.team_invitations"
                        :key="invitation.id"
                        :title="invitation.email"
                    >
                        <template #append>
                            <v-btn
                                v-if="userPermissions.canRemoveTeamMembers"
                                variant="text"
                                color="error"
                                size="small"
                                @click="cancelTeamInvitation(invitation)"
                            >
                                Cancel
                            </v-btn>
                        </template>
                    </v-list-item>
                </v-list>
            </template>
        </ActionSection>

        <ActionSection
            v-if="team.users.length > 0"
            title="Team Members"
            description="All of the people that are part of this team."
        >
            <template #content>
                <v-list class="bg-transparent">
                    <v-list-item
                        v-for="user in team.users"
                        :key="user.id"
                        :title="user.name"
                        :subtitle="availableRoles.length ? displayableRole(user.membership.role) : undefined"
                    >
                        <template #prepend>
                            <v-avatar size="32" class="me-3">
                                <v-img :src="user.profile_photo_url" :alt="user.name" />
                            </v-avatar>
                        </template>
                        <template #append>
                            <div class="d-flex align-center ga-2">
                                <v-btn
                                    v-if="userPermissions.canUpdateTeamMembers && availableRoles.length"
                                    variant="text"
                                    size="small"
                                    @click="manageRole(user)"
                                >
                                    {{ displayableRole(user.membership.role) }}
                                </v-btn>
                                <v-btn
                                    v-if="authUser.id === user.id"
                                    variant="text"
                                    color="error"
                                    size="small"
                                    @click="confirmLeavingTeam"
                                >
                                    Leave
                                </v-btn>
                                <v-btn
                                    v-else-if="userPermissions.canRemoveTeamMembers"
                                    variant="text"
                                    color="error"
                                    size="small"
                                    @click="confirmTeamMemberRemoval(user)"
                                >
                                    Remove
                                </v-btn>
                            </div>
                        </template>
                    </v-list-item>
                </v-list>
            </template>
        </ActionSection>

        <FormDialog
            v-model="currentlyManagingRole"
            title="Manage Role"
            save-text="Save"
            :loading="updateRoleForm.processing"
            @save="updateRole"
            @cancel="currentlyManagingRole = false"
        >
            <v-list v-if="managingRoleFor" border rounded="lg" class="bg-transparent">
                <v-list-item
                    v-for="role in availableRoles"
                    :key="role.key"
                    :active="updateRoleForm.role === role.key"
                    color="primary"
                    @click="updateRoleForm.role = role.key"
                >
                    <v-list-item-title class="font-weight-medium">
                        {{ role.name }}
                    </v-list-item-title>
                    <v-list-item-subtitle>
                        {{ role.description }}
                    </v-list-item-subtitle>
                    <template #append>
                        <v-icon
                            v-if="updateRoleForm.role === role.key"
                            icon="mdi-check-circle"
                            color="success"
                        />
                    </template>
                </v-list-item>
            </v-list>
        </FormDialog>

        <ConfirmDialog
            v-model="confirmingLeavingTeam"
            title="Leave Team"
            message="Are you sure you would like to leave this team?"
            confirm-text="Leave"
            confirm-color="error"
            :loading="leaveTeamForm.processing"
            @confirm="leaveTeam"
        />

        <ConfirmDialog
            v-model="confirmingMemberRemoval"
            title="Remove Team Member"
            message="Are you sure you would like to remove this person from the team?"
            confirm-text="Remove"
            confirm-color="error"
            :loading="removeTeamMemberForm.processing"
            @confirm="removeTeamMember"
        />
    </div>
</template>
