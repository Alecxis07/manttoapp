<script setup lang="ts">
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useAppPage } from '@/Composables/useAppPage';

interface Team {
    id: number;
    name: string;
}

interface AuthUser {
    id: number;
    name: string;
    email: string;
    profile_photo_url?: string;
    current_team?: Team;
    current_team_id?: number;
    all_teams?: Team[];
}

interface JetstreamProps {
    hasTeamFeatures?: boolean;
    canCreateTeams?: boolean;
    managesProfilePhotos?: boolean;
    hasApiFeatures?: boolean;
}

const page = useAppPage();

const user = computed(() => (page.props.auth.user ?? undefined) as AuthUser | undefined);
const jetstream = computed(() => (page.props.jetstream ?? {}) as JetstreamProps);

function switchToTeam(team: Team): void {
    router.put(
        route('current-team.update'),
        { team_id: team.id },
        { preserveState: false },
    );
}

function logout(): void {
    router.post(route('logout'));
}
</script>

<template>
    <div class="d-flex align-center ga-2">
        <v-menu v-if="jetstream.hasTeamFeatures && user?.current_team" location="bottom end">
            <template #activator="{ props: menuProps }">
                <v-btn
                    v-bind="menuProps"
                    variant="outlined"
                    size="small"
                    append-icon="mdi-unfold-more-horizontal"
                    class="d-none d-sm-inline-flex"
                >
                    {{ user.current_team.name }}
                </v-btn>
            </template>
            <v-list density="compact" min-width="240">
                <v-list-subheader>Administrar equipo</v-list-subheader>
                <Link :href="route('teams.show', user.current_team)" class="text-decoration-none">
                    <v-list-item title="Configuración del equipo" prepend-icon="mdi-cog-outline" />
                </Link>
                <Link
                    v-if="jetstream.canCreateTeams"
                    :href="route('teams.create')"
                    class="text-decoration-none"
                >
                    <v-list-item title="Crear nuevo equipo" prepend-icon="mdi-plus" />
                </Link>
                <template v-if="(user.all_teams?.length ?? 0) > 1">
                    <v-divider class="my-1" />
                    <v-list-subheader>Cambiar equipo</v-list-subheader>
                    <v-list-item
                        v-for="team in user.all_teams"
                        :key="team.id"
                        :title="team.name"
                        :prepend-icon="
                            team.id === user.current_team_id
                                ? 'mdi-check-circle'
                                : 'mdi-account-group-outline'
                        "
                        @click="switchToTeam(team)"
                    />
                </template>
            </v-list>
        </v-menu>

        <v-menu location="bottom end">
            <template #activator="{ props: menuProps }">
                <v-btn
                    v-if="jetstream.managesProfilePhotos && user?.profile_photo_url"
                    v-bind="menuProps"
                    icon
                    variant="text"
                >
                    <v-avatar size="32">
                        <v-img :src="user.profile_photo_url" :alt="user.name" />
                    </v-avatar>
                </v-btn>
                <v-btn
                    v-else
                    v-bind="menuProps"
                    variant="outlined"
                    size="small"
                    append-icon="mdi-chevron-down"
                >
                    <span class="text-truncate" style="max-width: 8rem">
                        {{ user?.name }}
                    </span>
                </v-btn>
            </template>
            <v-list density="compact" min-width="200">
                <v-list-subheader>Cuenta</v-list-subheader>
                <Link :href="route('profile.show')" class="text-decoration-none">
                    <v-list-item title="Perfil" prepend-icon="mdi-account-outline" />
                </Link>
                <Link
                    v-if="jetstream.hasApiFeatures"
                    :href="route('api-tokens.index')"
                    class="text-decoration-none"
                >
                    <v-list-item title="API Tokens" prepend-icon="mdi-key-outline" />
                </Link>
                <v-divider class="my-1" />
                <v-list-item title="Cerrar sesión" prepend-icon="mdi-logout" @click="logout" />
            </v-list>
        </v-menu>
    </div>
</template>
