<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormActions from '@/Components/Ui/FormActions.vue';
import FormSection from '@/Components/Ui/FormSection.vue';
import PageHeader from '@/Components/Ui/PageHeader.vue';

interface SelectOption {
    value: string;
    label: string;
}

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        status: string;
        role: string;
    };
    roles: SelectOption[];
    statuses: SelectOption[];
}>();

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    status: props.user.status,
    role: props.user.role,
});

const roleItems = computed(() =>
    props.roles.map((role) => ({ value: role.value, title: role.label })),
);

const statusItems = computed(() =>
    props.statuses.map((option) => ({ value: option.value, title: option.label })),
);

function submit(): void {
    form.put(route('users.update', props.user.id));
}
</script>

<template>
    <AppLayout title="Editar usuario">
        <PageHeader
            title="Editar usuario"
            :subtitle="user.name"
            :breadcrumbs="[
                { title: 'Usuarios', href: route('users.index') },
                { title: user.name, href: route('users.show', user.id) },
                { title: 'Editar', disabled: true },
            ]"
        />

        <FormSection
            title="Datos del usuario"
            description="Deja la contraseña vacía para no cambiarla."
            @submitted="submit"
        >
            <template #form>
                <v-row>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.name"
                            label="Nombre"
                            required
                            autofocus
                            :error-messages="form.errors.name"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.email"
                            type="email"
                            label="Email"
                            required
                            :error-messages="form.errors.email"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.password"
                            type="password"
                            label="Nueva contraseña"
                            autocomplete="new-password"
                            :error-messages="form.errors.password"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.password_confirmation"
                            type="password"
                            label="Confirmar contraseña"
                            autocomplete="new-password"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="form.role"
                            :items="roleItems"
                            label="Rol"
                            :error-messages="form.errors.role"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-select
                            v-model="form.status"
                            :items="statusItems"
                            label="Estatus"
                            :error-messages="form.errors.status"
                        />
                    </v-col>
                </v-row>
            </template>
            <template #actions>
                <FormActions
                    :processing="form.processing"
                    save-text="Actualizar"
                    @cancel="router.visit(route('users.show', user.id))"
                />
            </template>
        </FormSection>
    </AppLayout>
</template>
