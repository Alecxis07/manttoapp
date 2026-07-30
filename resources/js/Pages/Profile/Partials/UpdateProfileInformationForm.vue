<script setup lang="ts">
import { ref } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import FormSection from '@/Components/Ui/FormSection.vue';
import FormActions from '@/Components/Ui/FormActions.vue';

interface ProfileUser {
    name: string;
    email: string;
    email_verified_at: string | null;
    profile_photo_url?: string;
    profile_photo_path?: string | null;
}

const props = defineProps<{
    user: ProfileUser;
}>();

const page = usePage();
const jetstream = page.props.jetstream as {
    managesProfilePhotos?: boolean;
    hasEmailVerification?: boolean;
};

const form = useForm({
    _method: 'PUT',
    name: props.user.name,
    email: props.user.email,
    photo: null as File | null,
});

const verificationLinkSent = ref(false);
const photoPreview = ref<string | null>(null);
const photoInput = ref<HTMLInputElement | null>(null);

function updateProfileInformation(): void {
    if (photoInput.value?.files?.[0]) {
        form.photo = photoInput.value.files[0];
    }

    form.post(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => clearPhotoFileInput(),
    });
}

function sendEmailVerification(): void {
    verificationLinkSent.value = true;
}

function selectNewPhoto(): void {
    photoInput.value?.click();
}

function updatePhotoPreview(): void {
    const photo = photoInput.value?.files?.[0];
    if (!photo) {
        return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = (e.target?.result as string) ?? null;
    };
    reader.readAsDataURL(photo);
}

function deletePhoto(): void {
    router.delete(route('current-user-photo.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
}

function clearPhotoFileInput(): void {
    if (photoInput.value) {
        photoInput.value.value = '';
    }
}
</script>

<template>
    <FormSection
        title="Profile Information"
        description="Update your account's profile information and email address."
        @submitted="updateProfileInformation"
    >
        <template #form>
            <div v-if="jetstream.managesProfilePhotos" class="mb-4">
                <input
                    id="photo"
                    ref="photoInput"
                    type="file"
                    class="d-none"
                    accept="image/*"
                    @change="updatePhotoPreview"
                >

                <div class="text-body-2 font-weight-medium mb-2">Photo</div>

                <v-avatar
                    v-show="!photoPreview"
                    size="80"
                    class="mb-3"
                >
                    <v-img :src="user.profile_photo_url" :alt="user.name" />
                </v-avatar>

                <v-avatar
                    v-show="photoPreview"
                    size="80"
                    class="mb-3"
                    :image="photoPreview ?? undefined"
                />

                <div class="d-flex flex-wrap ga-2 mb-1">
                    <v-btn variant="outlined" type="button" @click.prevent="selectNewPhoto">
                        Select A New Photo
                    </v-btn>
                    <v-btn
                        v-if="user.profile_photo_path"
                        variant="text"
                        type="button"
                        @click.prevent="deletePhoto"
                    >
                        Remove Photo
                    </v-btn>
                </div>
                <div
                    v-if="form.errors.photo"
                    class="text-error text-caption mt-1"
                >
                    {{ form.errors.photo }}
                </div>
            </div>

            <v-text-field
                v-model="form.name"
                label="Name"
                autocomplete="name"
                required
                class="mb-3"
                :error-messages="form.errors.name"
            />

            <v-text-field
                v-model="form.email"
                label="Email"
                type="email"
                autocomplete="username"
                required
                class="mb-2"
                :error-messages="form.errors.email"
            />

            <div
                v-if="jetstream.hasEmailVerification && user.email_verified_at === null"
                class="mb-2"
            >
                <p class="text-body-2 text-medium-emphasis mb-1">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="text-decoration-underline bg-transparent border-0 cursor-pointer pa-0"
                        @click.prevent="sendEmailVerification"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>
                <v-alert
                    v-show="verificationLinkSent"
                    type="success"
                    variant="tonal"
                    density="compact"
                >
                    A new verification link has been sent to your email address.
                </v-alert>
            </div>
        </template>

        <template #actions>
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
