<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

defineProps({
    user: Object,
});
</script>

<template>
    <AppLayout title="Detalle de usuario">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ user.name }}
                </h2>
                <div class="flex gap-2">
                    <Link :href="route('users.index')">
                        <SecondaryButton>Volver</SecondaryButton>
                    </Link>
                    <Link :href="route('users.edit', user.id)">
                        <PrimaryButton>Editar</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-xl sm:rounded-lg p-6 space-y-4">
                    <div>
                        <div class="text-sm text-gray-500">Email</div>
                        <div class="text-gray-900">{{ user.email }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Rol</div>
                        <div class="text-gray-900">{{ user.role }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Estatus</div>
                        <div class="text-gray-900">{{ user.status_label }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Creado</div>
                        <div class="text-gray-900">{{ user.created_at }}</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
