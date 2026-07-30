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
    roles: SelectOption[];
    statuses: SelectOption[];
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    status: 'active',
    role: props.roles[0]?.value ?? 'consulta',
});

const roleItems = computed(() =>
    props.roles.map((role) => ({ value: role.value, title: role.label })),
);

const statusItems = computed(() =>
    props.statuses.map((option) => ({ value: option.value, title: option.label })),
);

function submit(): void {
    form.post(route('users.store'));
}
</script>

<template>
    <AppLayout title="Nuevo usuario">
        <PageHeader
            title="Nuevo usuario"
            subtitle="Crea un usuario y asígnale un rol del sistema."
            :breadcrumbs="[
                { title: 'Usuarios', href: route('users.index') },
                { title: 'Nuevo', disabled: true },
            ]"
        />

        <FormSection
            title="Datos del usuario"
            description="Credenciales y rol de acceso."
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
                            label="Contraseña"
                            required
                            autocomplete="new-password"
                            :error-messages="form.errors.password"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            v-model="form.password_confirmation"
                            type="password"
                            label="Confirmar contraseña"
                            required
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
                    save-text="Guardar"
                    @cancel="router.visit(route('users.index'))"
                />
            </template>
        </FormSection>
    </AppLayout>
</template>
